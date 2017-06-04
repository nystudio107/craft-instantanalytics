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

namespace nystudio107\instantanalytics\twigextensions;

use nystudio107\instantanalytics\InstantAnalytics;
use nystudio107\instantanalytics\helpers\IAnalytics;

use Craft;

/**
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 */
class InstantAnalyticsTwigExtension extends \Twig_Extension
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

    /**
     * @inheritdoc
     */
    public function getGlobals()
    {
        $result = [];
        if (Craft::$app->getRequest()->getIsSiteRequest()
            && !Craft::$app->getRequest()->getIsConsoleRequest()
        ) {
            // Return our Analytics object as a Twig global
            $currentTemplate = $this->getCurrentTemplatePath();
            $result = InstantAnalytics::$plugin->ia->getGlobals($currentTemplate);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('pageViewAnalytics', [$this, 'pageViewAnalytics']),
            new \Twig_SimpleFilter('eventAnalytics', [$this, 'eventAnalytics']),
            new \Twig_SimpleFilter('pageViewTrackingUrl', [$this, 'pageViewTrackingUrl']),
            new \Twig_SimpleFilter('eventTrackingUrl', [$this, 'eventTrackingUrl']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('pageViewAnalytics', [$this, 'pageViewAnalytics']),
            new \Twig_SimpleFunction('eventAnalytics', [$this, 'eventAnalytics']),
            new \Twig_SimpleFunction('pageViewTrackingUrl', [$this, 'pageViewTrackingUrl']),
            new \Twig_SimpleFunction('eventTrackingUrl', [$this, 'eventTrackingUrl']),
        ];
    }

    /**
     * Get a PageView analytics object
     *
     * @param string  $url   the URL to track
     * @param string  $title the page title
     *
     * @return IAnalytics
     */
    public function pageViewAnalytics($url = "", $title = "")
    {
        return InstantAnalytics::$plugin->ia->pageViewAnalytics($url, $title);
    }

    /**
     * Get an Event analytics object
     *
     * @param  string $eventCategory the event category
     * @param  string $eventAction   the event action
     * @param  string $eventLabel    the event label
     * @param  int    $eventValue    the event value
     *
     * @return IAnalytics
     */
    public function eventAnalytics($eventCategory = "", $eventAction = "", $eventLabel = "", $eventValue = 0)
    {
        return InstantAnalytics::$plugin->ia->eventAnalytics($eventCategory, $eventAction, $eventLabel, $eventValue);
    }

    /**
     * Return an Analytics object
     *
     * @return IAnalytics
     */
    public function analytics()
    {
        return InstantAnalytics::$plugin->ia->analytics();
    }

    /**
     * Get a PageView tracking URL
     *
     * @param string  $url   the URL to track
     * @param string  $title the page title
     *
     * @return string the tracking URL
     */
    public function pageViewTrackingUrl($url, $title)
    {
        return InstantAnalytics::$plugin->ia->pageViewTrackingUrl($url, $title);
    }

    /**
     * Get an Event tracking URL
     *
     * @param  string $url           the URL to track
     * @param  string $eventCategory the event category
     * @param  string $eventAction   the event action
     * @param  string $eventLabel    the event label
     * @param  int    $eventValue    the event value
     *
     * @return string the tracking URL
     */
    public function eventTrackingUrl($url, $eventCategory = "", $eventAction = "", $eventLabel = "", $eventValue = 0)
    {
        return InstantAnalytics::$plugin->ia->eventTrackingUrl($url, $eventCategory, $eventAction, $eventLabel, $eventValue);
    }

    /**
     * Get the current template path
     *
     * @return string the template path
     */
    private function getCurrentTemplatePath()
    {
        $result = "";
        $currentTemplate = Craft::$app->getView()->getRenderingTemplate();
        $templatesPath = Craft::$app->getPath()->getSiteTemplatesPath();

        $path_parts = pathinfo($currentTemplate);

        if ($path_parts && isset($path_parts['dirname']) && isset($path_parts['filename'])) {
            $result = $path_parts['dirname'] . "/" . $path_parts['filename'];

            if (substr($result, 0, strlen($templatesPath)) == $templatesPath) {
                $result = substr($result, strlen($templatesPath));
            }
        }

        return $result;
    }
}
