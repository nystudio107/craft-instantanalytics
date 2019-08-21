<?php

namespace nystudio107\instantanalytics\variables;

use nystudio107\instantanalytics\helpers\Manifest as ManifestHelper;
use nystudio107\instantanalytics\assetbundles\instantanalytics\InstantAnalyticsAsset;

use Craft;
use craft\helpers\Template;

use Twig\Markup;

use yii\web\NotFoundHttpException;

class ManifestVariable
{
    // Protected Static Properties
    // =========================================================================

    protected static $config = [
        // If `devMode` is on, use webpack-dev-server to all for HMR (hot module reloading)
        'useDevServer' => false,
        // Manifest names
        'manifest' => [
            'legacy' => 'manifest.json',
            'modern' => 'manifest.json',
        ],
        // Public server config
        'server' => [
            'manifestPath' => '/',
            'publicPath' => '/',
        ],
        // webpack-dev-server config
        'devServer' => [
            'manifestPath' => 'http://127.0.0.1:8080',
            'publicPath' => '/',
        ],
    ];

    // Public Methods
    // =========================================================================

    /**
     * ManifestVariable constructor.
     */
    public function __construct()
    {
        ManifestHelper::invalidateCaches();
        $bundle = new InstantAnalyticsAsset();
        $baseAssetsUrl = Craft::$app->assetManager->getPublishedUrl(
            $bundle->sourcePath,
            true
        );
        self::$config['server']['manifestPath'] = Craft::getAlias($bundle->sourcePath);
        self::$config['server']['publicPath'] = $baseAssetsUrl;
        $useDevServer = getenv('NYS_PLUGIN_DEVSERVER');
        if ($useDevServer !== false) {
            self::$config['useDevServer'] = (bool)$useDevServer;
        }
    }

    /**
     * @param string     $moduleName
     * @param bool       $async
     * @param null|array $config
     *
     * @return Markup
     * @throws NotFoundHttpException
     */
    public function includeCssModule(string $moduleName, bool $async = false, $config = null): Markup
    {
        return Template::raw(
            ManifestHelper::getCssModuleTags(self::$config, $moduleName, $async)
        );
    }

    /**
     * Returns the CSS file in $path wrapped in <style></style> tags
     *
     * @param string $path
     *
     * @return Markup
     */
    public function includeInlineCssTags(string $path): Markup
    {
        return Template::raw(
            ManifestHelper::getCssInlineTags($path)
        );
    }

    /**
     * Returns the uglified loadCSS rel=preload Polyfill as per:
     * https://github.com/filamentgroup/loadCSS#how-to-use-loadcss-recommended-example
     *
     * @return Markup
     */
    public static function includeCssRelPreloadPolyfill(): Markup
    {
        return Template::raw(
            ManifestHelper::getCssRelPreloadPolyfill()
        );
    }

    /**
     * @param string     $moduleName
     * @param bool       $async
     * @param null|array $config
     *
     * @return null|Markup
     * @throws NotFoundHttpException
     */
    public function includeJsModule(string $moduleName, bool $async = false, $config = null)
    {
        return Template::raw(
            ManifestHelper::getJsModuleTags(self::$config, $moduleName, $async)
        );
    }

    /**
     * Return the URI to a module
     *
     * @param string $moduleName
     * @param string $type
     * @param null   $config
     *
     * @return null|Markup
     * @throws NotFoundHttpException
     */
    public function getModuleUri(string $moduleName, string $type = 'modern', $config = null)
    {
        return Template::raw(
            ManifestHelper::getModule(self::$config, $moduleName, $type)
        );
    }

    /**
     * Include the Safari 10.1 nomodule fix JavaScript
     *
     * @return Markup
     */
    public function includeSafariNomoduleFix(): Markup
    {
        return Template::raw(
            ManifestHelper::getSafariNomoduleFix()
        );
    }

    /**
     * Returns the contents of a file from a URI path
     *
     * @param string $path
     *
     * @return Markup
     */
    public function includeFile(string $path): Markup
    {
        return Template::raw(
            ManifestHelper::getFile($path)
        );
    }

    /**
     * Returns the contents of a file from the $fileName in the manifest
     *
     * @param string $fileName
     * @param string $type
     * @param null   $config
     *
     * @return Markup
     */
    public function includeFileFromManifest(string $fileName, string $type = 'legacy', $config = null): Markup
    {
        return Template::raw(
            ManifestHelper::getFileFromManifest($config, $fileName, $type)
        );
    }
}
