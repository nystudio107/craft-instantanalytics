<?php
/**
 * Instant Analytics plugin for Craft CMS 3.x
 *
 * Instant Analytics brings full Google Analytics support to your Twig templates
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\instantanalytics\assetbundles\instantanalytics;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class InstantAnalyticsAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@nystudio107/instantanalytics/assetbundles/instantanalytics/dist';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        parent::init();
    }
}
