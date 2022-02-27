<?php
/**
 * Instant Analytics plugin for Craft CMS 3.x
 *
 * Instant Analytics brings full Google Analytics support to your Twig templates
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\instantanalytics;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\commerce\elements\Order;
use craft\commerce\events\LineItemEvent;
use craft\commerce\Plugin as Commerce;
use craft\events\PluginEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\TemplateEvent;
use craft\helpers\UrlHelper;
use craft\services\Plugins;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use Exception;
use nystudio107\instantanalytics\assetbundles\instantanalytics\InstantAnalyticsAsset;
use nystudio107\instantanalytics\helpers\Field as FieldHelper;
use nystudio107\instantanalytics\helpers\IAnalytics;
use nystudio107\instantanalytics\models\Settings;
use nystudio107\instantanalytics\services\Commerce as CommerceService;
use nystudio107\instantanalytics\services\IA as IAService;
use nystudio107\instantanalytics\twigextensions\InstantAnalyticsTwigExtension;
use nystudio107\instantanalytics\variables\InstantAnalyticsVariable;
use nystudio107\pluginvite\services\VitePluginService;
use nystudio107\seomatic\Seomatic;
use yii\base\Event;
use function array_merge;

/** @noinspection MissingPropertyAnnotationsInspection */

/**
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 *
 * @property IAService $ia
 * @property CommerceService $commerce
 * @property VitePluginService $vite
 */
class InstantAnalytics extends Plugin
{
    // Constants
    // =========================================================================

    protected const COMMERCE_PLUGIN_HANDLE = 'commerce';
    protected const SEOMATIC_PLUGIN_HANDLE = 'seomatic';

    // Static Properties
    // =========================================================================

    /**
     * @var null|InstantAnalytics
     */
    public static ?InstantAnalytics $plugin;

    /**
     * @var null|Settings
     */
    public static ?Settings $settings;

    /**
     * @var null|Commerce
     */
    public static ?Commerce $commercePlugin;

    /**
     * @var null|Seomatic
     */
    public static ?Seomatic $seomaticPlugin;

    /**
     * @var string
     */
    public static string $currentTemplate = '';

    /**
     * @var bool
     */
    public static bool $pageViewSent = false;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public bool $hasCpSection = false;
    /**
     * @var bool
     */
    public bool $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        $config['components'] = [
            'ia' => IAService::class,
            'commerce' => CommerceService::class,
            // Register the vite service
            'vite' => [
                'class' => VitePluginService::class,
                'assetClass' => InstantAnalyticsAsset::class,
                'useDevServer' => true,
                'devServerPublic' => 'http://localhost:3001',
                'serverPublic' => 'http://localhost:8000',
                'errorEntry' => 'src/js/app.ts',
                'devServerInternal' => 'http://craft-instantanalytics-buildchain:3001',
                'checkDevServer' => true,
            ],
        ];

        parent::__construct($id, $parent, $config);
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;
        self::$settings = $this->getSettings();

        // Determine if Craft Commerce is installed & enabled
        self::$commercePlugin = Craft::$app->getPlugins()->getPlugin(self::COMMERCE_PLUGIN_HANDLE);
        // Determine if SEOmatic is installed & enabled
        self::$seomaticPlugin = Craft::$app->getPlugins()->getPlugin(self::SEOMATIC_PLUGIN_HANDLE);
        // Add in our Craft components
        $this->addComponents();
        // Install our global event handlers
        $this->installEventListeners();

        Craft::info(
            Craft::t(
                'instant-analytics',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    /**
     * @inheritdoc
     */
    public function settingsHtml(): ?string
    {
        $commerceFields = [];

        if (self::$commercePlugin) {
            $productTypes = self::$commercePlugin->getProductTypes()->getAllProductTypes();

            foreach ($productTypes as $productType) {
                $productFields = $this->getPullFieldsFromLayoutId($productType->fieldLayoutId);
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $commerceFields = array_merge($commerceFields, $productFields);
                if ($productType->hasVariants) {
                    $variantFields = $this->getPullFieldsFromLayoutId($productType->variantFieldLayoutId);
                    /** @noinspection SlowArrayOperationsInLoopInspection */
                    $commerceFields = array_merge($commerceFields, $variantFields);
                }
            }
        }

        // Rend the settings template
        try {
            return Craft::$app->getView()->renderTemplate(
                'instant-analytics/settings',
                [
                    'settings' => $this->getSettings(),
                    'commerceFields' => $commerceFields,
                ]
            );
        } catch (Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }

        return '';
    }

    /**
     * Handle the `{% hook iaSendPageView %}`
     *
     * @param array &$context
     *
     * @return string
     */
    public function iaSendPageView(/** @noinspection PhpUnusedParameterInspection */ array &$context): string
    {
        $this->sendPageView();

        return '';
    }

    // Protected Methods
    // =========================================================================

    /**
     * Add in our Craft components
     */
    protected function addComponents(): void
    {
        $view = Craft::$app->getView();
        // Add in our Twig extensions
        $view->registerTwigExtension(new InstantAnalyticsTwigExtension());
        // Install our template hook
        $view->hook('iaSendPageView', [$this, 'iaSendPageView']);
        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('instantAnalytics', [
                    'class' => InstantAnalyticsVariable::class,
                    'viteService' => $this->vite,
                ]);
            }
        );
    }

    /**
     * Install our event listeners
     */
    protected function installEventListeners(): void
    {
        // Handler: Plugins::EVENT_AFTER_INSTALL_PLUGIN
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    $request = Craft::$app->getRequest();
                    if ($request->isCpRequest) {
                        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('instant-analytics/welcome'))->send();
                    }
                }
            }
        );
        $request = Craft::$app->getRequest();
        // Install only for non-console site requests
        if ($request->getIsSiteRequest() && !$request->getIsConsoleRequest()) {
            $this->installSiteEventListeners();
        }
        // Install only for non-console Control Panel requests
        if ($request->getIsCpRequest() && !$request->getIsConsoleRequest()) {
            $this->installCpEventListeners();
        }
    }

    /**
     * Install site event listeners for site requests only
     */
    protected function installSiteEventListeners(): void
    {
        // Handler: UrlManager::EVENT_REGISTER_SITE_URL_RULES
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                Craft::debug(
                    'UrlManager::EVENT_REGISTER_SITE_URL_RULES',
                    __METHOD__
                );
                // Register our Control Panel routes
                $event->rules = array_merge(
                    $event->rules,
                    $this->customFrontendRoutes()
                );
            }
        );
        // Remember the name of the currently rendering template
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
            static function (TemplateEvent $event) {
                self::$currentTemplate = $event->template;
            }
        );
        // Remember the name of the currently rendering template
        Event::on(
            View::class,
            View::EVENT_AFTER_RENDER_PAGE_TEMPLATE,
            function (TemplateEvent $event) {
                if (self::$settings->autoSendPageView) {
                    $this->sendPageView();
                }
            }
        );
        // Commerce-specific hooks
        if (self::$commercePlugin) {
            Event::on(Order::class, Order::EVENT_AFTER_COMPLETE_ORDER, function (Event $e) {
                $order = $e->sender;
                if (self::$settings->autoSendPurchaseComplete) {
                    $this->commerce->orderComplete($order);
                }
            });

            Event::on(Order::class, Order::EVENT_AFTER_ADD_LINE_ITEM, function (LineItemEvent $e) {
                $lineItem = $e->lineItem;
                if (self::$settings->autoSendAddToCart) {
                    $this->commerce->addToCart($lineItem->order, $lineItem);
                }
            });

            // Check to make sure Order::EVENT_AFTER_REMOVE_LINE_ITEM is defined
            if (defined(Order::class . '::EVENT_AFTER_REMOVE_LINE_ITEM')) {
                Event::on(Order::class, Order::EVENT_AFTER_REMOVE_LINE_ITEM, function (LineItemEvent $e) {
                    $lineItem = $e->lineItem;
                    if (self::$settings->autoSendRemoveFromCart) {
                        $this->commerce->removeFromCart($lineItem->order, $lineItem);
                    }
                });
            }
        }
    }

    /**
     * Install site event listeners for Control Panel requests only
     */
    protected function installCpEventListeners(): void
    {
    }

    /**
     * Return the custom frontend routes
     *
     * @return array
     */
    protected function customFrontendRoutes(): array
    {
        return [
            'instantanalytics/pageViewTrack/<filename:[-\w\.*]+>?' =>
                'instant-analytics/track/track-page-view-url',
            'instantanalytics/eventTrack/<filename:[-\w\.*]+>?' =>
                'instant-analytics/track/track-event-url',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    // Private Methods
    // =========================================================================

    /**
     * Send a page view with the pre-loaded IAnalytics object
     */
    private function sendPageView(): void
    {
        $request = Craft::$app->getRequest();
        if ($request->getIsSiteRequest() && !$request->getIsConsoleRequest() && !self::$pageViewSent) {
            self::$pageViewSent = true;
            /** @var IAnalytics $analytics */
            $analytics = self::$plugin->ia->getGlobals(self::$currentTemplate);
            // Bail if we have no analytics object
            if ($analytics === null) {
                return;
            }
            // If SEOmatic is installed, set the page title from it
            $this->setTitleFromSeomatic($analytics);
            // Send the page view
            if ($analytics) {
                $response = $analytics->sendPageview();
                Craft::info(
                    Craft::t(
                        'instant-analytics',
                        'pageView sent, response:: {response}',
                        [
                            'response' => print_r($response, true),
                        ]
                    ),
                    __METHOD__
                );
            } else {
                Craft::error(
                    Craft::t(
                        'instant-analytics',
                        'Analytics not sent because googleAnalyticsTracking is not set'
                    ),
                    __METHOD__
                );
            }
        }
    }

    /**
     * If SEOmatic is installed, set the page title from it
     *
     * @param IAnalytics $analytics
     */
    private function setTitleFromSeomatic(IAnalytics $analytics): void
    {
        if (self::$seomaticPlugin && Seomatic::$settings->renderEnabled) {
            $titleTag = Seomatic::$plugin->title->get('title');
            if ($titleTag) {
                $titleArray = $titleTag->renderAttributes();
                if (!empty($titleArray['title'])) {
                    $analytics->setDocumentTitle($titleArray['title']);
                }
            }
        }
    }

    /**
     * @param $layoutId
     *
     * @return array
     */
    private function getPullFieldsFromLayoutId($layoutId): array
    {
        $result = ['' => 'none'];
        if ($layoutId === null) {
            return $result;
        }
        $fieldLayout = Craft::$app->getFields()->getLayoutById($layoutId);
        if ($fieldLayout) {
            $result = FieldHelper::fieldsOfTypeFromLayout(FieldHelper::TEXT_FIELD_CLASS_KEY, $fieldLayout, false);
        }

        return $result;
    }
}
