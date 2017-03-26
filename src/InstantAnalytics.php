<?php
/**
 * Instant Analytics plugin for Craft CMS 3.x
 *
 * Instant Analytics brings full Google Analytics support to your Twig templates
 * and automatic Craft Commerce integration with Google Enhanced Ecommerce.
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\instantanalytics;

use nystudio107\instantanalytics\services\IA as IAService;
use nystudio107\instantanalytics\variables\InstantAnalyticsVariable;
use nystudio107\instantanalytics\twigextensions\InstantAnalyticsTwigExtension;
use nystudio107\instantanalytics\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;

use yii\base\Event;

/**
 * Class InstantAnalytics
 *
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 *
 * @property  IAService $ia
 */
class InstantAnalytics extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var InstantAnalytics
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Install our Twig extension
        Craft::$app->view->twig->addExtension(new InstantAnalyticsTwigExtension());

        // Register our template hook
        Craft::$app->getView()->hook('iaSendPageView', [$this, 'iaSendPageViewHook']);

        Event::on(
            Plugins::className(),
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'instantanalytics',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    /**
     * @inheritdoc
     */
    public function defineTemplateComponent()
    {
        return InstantAnalyticsVariable::class;
    }

    /**
     * @param $context
     */
    public function iaSendPageViewHook(&$context)
    {
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
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'instantanalytics/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
