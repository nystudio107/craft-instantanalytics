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

use craft\helpers\ArrayHelper;
use nystudio107\instantanalytics\assetbundles\instantanalytics\InstantAnalyticsAsset;
use nystudio107\instantanalytics\services\Commerce as CommerceService;
use nystudio107\instantanalytics\services\IA as IAService;
use nystudio107\pluginvite\services\VitePluginService;
use yii\base\InvalidConfigException;

/**
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.1.16
 *
 * @property IAService $ia
 * @property CommerceService $commerce
 * @property VitePluginService $vite
 */
trait ServicesTrait
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        // Constants aren't allowed in traits until PHP >= 8.2
        $majorVersion = '1';
        // Dev server container name & port are based on the major version of this plugin
        $devPort = 3000 + (int)$majorVersion;
        $versionName = 'v' . $majorVersion;
        // Merge in the passed config, so it our config can be overridden by Plugins::pluginConfigs['vite']
        // ref: https://github.com/craftcms/cms/issues/1989
        $config = ArrayHelper::merge([
            'components' => [
                'ia' => IAService::class,
                'commerce' => CommerceService::class,
                // Register the vite service
                'vite' => [
                    'assetClass' => InstantAnalyticsAsset::class,
                    'checkDevServer' => true,
                    'class' => VitePluginService::class,
                    'devServerInternal' => 'http://craft-instantanalytics-' . $versionName . '-buildchain-dev:' . $devPort,
                    'devServerPublic' => 'http://localhost:' . $devPort,
                    'errorEntry' => 'src/js/app.ts',
                    'useDevServer' => true,
                ],
            ]
        ], $config);

        parent::__construct($id, $parent, $config);
    }

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
