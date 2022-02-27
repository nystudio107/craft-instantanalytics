<?php
/**
 * Instant Analytics plugin for Craft CMS 3.x
 *
 * Instant Analytics brings full Google Analytics support to your Twig templates
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\instantanalytics\twigextensions;

use Craft;
use craft\helpers\Template;
use nystudio107\instantanalytics\helpers\IAnalytics;
use nystudio107\instantanalytics\InstantAnalytics;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\Markup;
use Twig\TwigFilter;
use Twig\TwigFunction;
use yii\base\Exception;

/**
 * Twig can be extended in many ways; you can add extra tags, filters, tests,
 * operators, global variables, and functions. You can even extend the parser
 * itself with node visitors.
 *
 * http://twig.sensiolabs.org/doc/advanced.html
 *
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 */
class InstantAnalyticsTwigExtension extends AbstractExtension implements GlobalsInterface
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'InstantAnalytics';
    }

    /**
     * @inheritdoc
     */
    public function getGlobals(): array
    {
        $globals = [];
        $view = Craft::$app->getView();
        if ($view->getIsRenderingPageTemplate()) {
            $request = Craft::$app->getRequest();
            if ($request->getIsSiteRequest() && !$request->getIsConsoleRequest()) {
                // Return our Analytics object as a Twig global
                $globals = [
                    'instantAnalytics' => InstantAnalytics::$plugin->ia->getGlobals(InstantAnalytics::$currentTemplate),
                ];
            }
        }

        return $globals;
    }

    /**
     * @inheritdoc
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('pageViewAnalytics', [$this, 'pageViewAnalytics']),
            new TwigFilter('eventAnalytics', [$this, 'eventAnalytics']),
            new TwigFilter('pageViewTrackingUrl', [$this, 'pageViewTrackingUrl']),
            new TwigFilter('eventTrackingUrl', [$this, 'eventTrackingUrl']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('pageViewAnalytics', [$this, 'pageViewAnalytics']),
            new TwigFunction('eventAnalytics', [$this, 'eventAnalytics']),
            new TwigFunction('pageViewTrackingUrl', [$this, 'pageViewTrackingUrl']),
            new TwigFunction('eventTrackingUrl', [$this, 'eventTrackingUrl']),
        ];
    }

    /**
     * Get a PageView analytics object
     *
     * @param string $url
     * @param string $title
     *
     * @return null|IAnalytics object
     */
    public function pageViewAnalytics(string $url = '', string $title = ''): ?IAnalytics
    {
        return InstantAnalytics::$plugin->ia->pageViewAnalytics($url, $title);
    }

    /**
     * Get an Event analytics object
     *
     * @param string $eventCategory
     * @param string $eventAction
     * @param string $eventLabel
     * @param int $eventValue
     *
     * @return null|IAnalytics
     */
    public function eventAnalytics(string $eventCategory = '', string $eventAction = '', string $eventLabel = '', int $eventValue = 0): ?IAnalytics
    {
        return InstantAnalytics::$plugin->ia->eventAnalytics($eventCategory, $eventAction, $eventLabel, $eventValue);
    }

    /**
     * Return an Analytics object
     *
     * @return null|IAnalytics
     */
    public function analytics(): ?IAnalytics
    {
        return InstantAnalytics::$plugin->ia->analytics();
    }

    /**
     * Get a PageView tracking URL
     *
     * @param $url
     * @param $title
     *
     * @return Markup
     * @throws Exception
     */
    public function pageViewTrackingUrl($url, $title): Markup
    {
        return Template::raw(InstantAnalytics::$plugin->ia->pageViewTrackingUrl($url, $title));
    }

    /**
     * Get an Event tracking URL
     *
     * @param string $url
     * @param string $eventCategory
     * @param string $eventAction
     * @param string $eventLabel
     * @param int $eventValue
     *
     * @return Markup
     * @throws Exception
     */
    public function eventTrackingUrl(
        string $url,
        string $eventCategory = '',
        string $eventAction = '',
        string $eventLabel = '',
        int    $eventValue = 0
    ): Markup
    {
        return Template::raw(InstantAnalytics::$plugin->ia->eventTrackingUrl(
            $url,
            $eventCategory,
            $eventAction,
            $eventLabel,
            $eventValue
        ));
    }
}
