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

namespace nystudio107\instantanalytics\assetbundles\InstantAnalytics;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 */
class InstantAnalyticsAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@nystudio107/instantanalytics/assetbundles/instantanalytics/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/InstantAnalytics.js',
        ];

        $this->css = [
            'css/InstantAnalytics.css',
        ];

        parent::init();
    }
}
