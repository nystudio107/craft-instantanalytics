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

use nystudio107\instantanalytics\helpers\IAnalytics;
use nystudio107\instantanalytics\services\Commerce as CommerceService;
use nystudio107\instantanalytics\services\IA as IAService;
use nystudio107\instantanalytics\variables\InstantAnalyticsVariable;
use nystudio107\instantanalytics\twigextensions\InstantAnalyticsTwigExtension;
use nystudio107\instantanalytics\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\TemplateEvent;
use craft\helpers\UrlHelper;
use craft\services\Plugins;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;

use yii\base\Event;

/**
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 *
 * @property  IAService $ia
 * @property  CommerceService $commerce
 */
class InstantAnalytics extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var InstantAnalytics
     */
    public static $plugin;

    /**
     * @var Plugin|null
     */
    public static $commercePlugin;

    /**
     * @var Plugin|null
     */
    public static $seomaticPlugin;

    /**
     * @var string
     */
    public static $currentTemplate = '';

    /**
     * @var bool
     */
    public static $pageViewSent = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;
        $view = Craft::$app->getView();
        $request = Craft::$app->getRequest();
        // Add in our Twig extensions
        $view->registerTwigExtension(new InstantAnalyticsTwigExtension());
        // Install our template hook
        $view->hook('iaSendPageView', [$this, 'iaSendPageView']);
        // Determine if Craft Commerce is installed & enabled
        self::$commercePlugin = Craft::$app->getPlugins()->getPlugin('commerce');
        // Determine if SEOmatic is installed & enabled
        self::$seomaticPlugin = Craft::$app->getPlugins()->getPlugin('seomatic');
        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('instantAnalytics', InstantAnalyticsVariable::class);
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    $request = Craft::$app->getRequest();
                    if (($request->isCpRequest) && (!$request->isConsoleRequest)) {
                        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('instant-analytics/welcome'))->send();
                    }
                }
            }
        );

        // We're only interested in site requests that are not console requests
        if (($request->isSiteRequest) && (!$request->isConsoleRequest)) {
            // Remember the name of the currently rendering template
            Event::on(
                View::class,
                View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
                function (TemplateEvent $event) {
                    self::$currentTemplate = $event->template;
                }
            );
            // Remember the name of the currently rendering template
            Event::on(
                View::class,
                View::EVENT_AFTER_RENDER_PAGE_TEMPLATE,
                function (TemplateEvent $event) {
                    $settings = InstantAnalytics::$plugin->getSettings();
                    if ($settings->autoSendPageView) {
                        $this->sendPageView();
                    }
                }
            );
            // Register our site routes
            Event::on(
                UrlManager::class,
                UrlManager::EVENT_REGISTER_SITE_URL_RULES,
                function (RegisterUrlRulesEvent $event) {
                    $event->rules['instantanalytics/pageViewTrack/<filename:[-\w\.*]+>?'] =
                        'instant-analytics/track/track-page-view-url';
                    $event->rules['instantanalytics/eventTrack/<filename:[-\w\.*]+>?'] =
                        'instant-analytics/track/track-event-url';
                }
            );
            // Commerce-specific hooks
            if (self::$commercePlugin) {
                // TODO: pending Commerce for Craft 3
            }
        }

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
    public function settingsHtml()
    {
        $commerceFields = [];

        if (self::$commercePlugin) {
            // TODO: pending Commerce for Craft 3
            /*
            $productTypes = craft()->commerce_productTypes->getAllProductTypes();
            foreach ($productTypes as $productType) {
                $productFields = $this->_getPullFieldsFromLayoutId($productType->fieldLayoutId);
                $commerceFields = array_merge($commerceFields, $productFields);
                if ($productType->hasVariants) {
                    $variantFields = $this->_getPullFieldsFromLayoutId($productType->variantFieldLayoutId);
                    $commerceFields = array_merge($commerceFields, $variantFields);
                }
            }
            */
        }


        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'instant-analytics/settings',
            [
                'settings'       => $this->getSettings(),
                'commerceFields' => $commerceFields,
            ]
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @param int $layoutId
     *
     * @return array
     */
    private function getPullFieldsFromLayoutId(int $layoutId)
    {
        $result = ['' => "none"];
        $fieldLayout = Craft::$app->getFields()->getLayoutById($layoutId);
        $fieldLayoutFields = $fieldLayout->getFields();
        foreach ($fieldLayoutFields as $fieldLayoutField) {
            $field = $fieldLayoutField->field;
            switch ($field->type) {
                case "PlainText":
                case "RichText":
                case "RedactorI":
                case "PreparseField_Preparse":
                case "Categories":
                    $result[$field->handle] = $field->name;
                    break;

                case "Tags":
                    break;
            }
        }

        return $result;
    }

    // Private Methods
    // =========================================================================

    private function sendPageView()
    {
        if (!self::$pageViewSent) {
            self::$pageViewSent = true;
            /** @var IAnalytics $analytics */
            $analytics = InstantAnalytics::$plugin->ia->getGlobals(self::$currentTemplate);
            // Send the page view
            if ($analytics) {
                $response = $analytics->sendPageView();
                Craft::info(
                    "pageView sent, response: " . print_r($response, true),
                    __METHOD__
                );
            } else {
                Craft::error(
                    "Analytics not sent because googleAnalyticsTracking is not set",
                    __METHOD__
                );
            }
        }
    }

    /**
     * Send a page view with the pre-loaded IAnalytics object
     *
     * @param array &$context
     *
     * @return string|null
     */
    private function iaSendPageView(array &$context)
    {
        $request = Craft::$app->getRequest();
        if ($request->getIsSiteRequest() && !$request->getIsConsoleRequest()) {
            // If SEOmatic is installed, set the page title from it
            if (self::$seomaticPlugin && isset($context['seomaticMeta'])) {
                // TODO: fix for SEOmatic
                /*
                $seomaticMeta = $context['seomaticMeta'];
                $analytics->setDocumentTitle($seomaticMeta['seoTitle']);
                */
            }
            $this->sendPageView();
        }

        return '';
    }
}
