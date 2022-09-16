<?php
/**
 * Instant Analytics plugin for Craft CMS
 *
 * Instant Analytics brings full Google Analytics support to your Twig templates
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2022 nystudio107
 */

namespace nystudio107\instantanalytics\services;

use nystudio107\instantanalytics\assetbundles\instantanalytics\InstantAnalyticsAsset;
use nystudio107\instantanalytics\services\Commerce as CommerceService;
use nystudio107\instantanalytics\services\IA as IAService;
use nystudio107\pluginvite\services\VitePluginService;
use yii\base\InvalidConfigException;

/**
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     4.0.0
 *
 * @property IAService $ia
 * @property CommerceService $commerce
 * @property VitePluginService $vite
 */
trait ServicesTrait
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function config(): array
    {
        return [
            'components' => [
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
            ]
        ];
    }

    // Public Methods
    // =========================================================================

    /**
     * Returns the ia service
     *
     * @return IAService The ia service
     * @throws InvalidConfigException
     */
    public function getIa(): IAService
    {
        return $this->get('ia');
    }

    /**
     * Returns the commerce service
     *
     * @return CommerceService The commerce service
     * @throws InvalidConfigException
     */
    public function getCommerce(): CommerceService
    {
        return $this->get('commerce');
    }

    /**
     * Returns the vite service
     *
     * @return VitePluginService The vite service
     * @throws InvalidConfigException
     */
    public function getVite(): VitePluginService
    {
        return $this->get('vite');
    }
}
