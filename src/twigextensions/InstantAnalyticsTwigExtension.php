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

use nystudio107\instantanalytics\InstantAnalytics;
use nystudio107\instantanalytics\helpers\IAnalytics;

use Craft;
use craft\helpers\Template;

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
class InstantAnalyticsTwigExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'InstantAnalytics';
    }

    /** @noinspection ReturnTypeCanBeDeclaredInspection */
    /**
     * @@inheritdoc
     */
    public function getGlobals()
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
    public function getFilters()
    {
        return [
            new \Twig\TwigFilter('pageViewAnalytics', [$this, 'pageViewAnalytics']),
            new \Twig\TwigFilter('eventAnalytics', [$this, 'eventAnalytics']),
            new \Twig\TwigFilter('pageViewTrackingUrl', [$this, 'pageViewTrackingUrl']),
            new \Twig\TwigFilter('eventTrackingUrl', [$this, 'eventTrackingUrl']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('pageViewAnalytics', [$this, 'pageViewAnalytics']),
            new \Twig\TwigFunction('eventAnalytics', [$this, 'eventAnalytics']),
            new \Twig\TwigFunction('pageViewTrackingUrl', [$this, 'pageViewTrackingUrl']),
            new \Twig\TwigFunction('eventTrackingUrl', [$this, 'eventTrackingUrl']),
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
    public function pageViewAnalytics($url = '', $title = '')
    {
        return InstantAnalytics::$plugin->ia->pageViewAnalytics($url, $title);
    }

    /**
     * Get an Event analytics object
     *
     * @param string $eventCategory
     * @param string $eventAction
     * @param string $eventLabel
     * @param int    $eventValue
     *
     * @return null|IAnalytics
     */
    public function eventAnalytics($eventCategory = '', $eventAction = '', $eventLabel = '', $eventValue = 0)
    {
        return InstantAnalytics::$plugin->ia->eventAnalytics($eventCategory, $eventAction, $eventLabel, $eventValue);
    }

    /**
     * Return an Analytics object
     *
     * @return null|IAnalytics
     */
    public function analytics()
    {
        return InstantAnalytics::$plugin->ia->analytics();
    }

    /**
     * Get a PageView tracking URL
     *
     * @param $url
     * @param $title
     *
     * @return \Twig\Markup
     * @throws \yii\base\Exception
     */
    public function pageViewTrackingUrl($url, $title): \Twig\Markup
    {
        return Template::raw(InstantAnalytics::$plugin->ia->pageViewTrackingUrl($url, $title));
    }

    /**
     * Get an Event tracking URL
     *
     * @param        $url
     * @param string $eventCategory
     * @param string $eventAction
     * @param string $eventLabel
     * @param int    $eventValue
     *
     * @return \Twig\Markup
     * @throws \yii\base\Exception
     */
    public function eventTrackingUrl(
        $url,
        $eventCategory = '',
        $eventAction = '',
        $eventLabel = '',
        $eventValue = 0
    ): \Twig\Markup {
        return Template::raw(InstantAnalytics::$plugin->ia->eventTrackingUrl(
            $url,
            $eventCategory,
            $eventAction,
            $eventLabel,
            $eventValue
        ));
    }
}
