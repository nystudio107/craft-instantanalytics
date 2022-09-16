<?php
/**
 * Instant Analytics plugin for Craft CMS
 *
 * Instant Analytics brings full Google Analytics support to your Twig templates
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\instantanalytics\variables;

use craft\helpers\Template;
use nystudio107\instantanalytics\helpers\IAnalytics;
use nystudio107\instantanalytics\InstantAnalytics;
use nystudio107\pluginvite\variables\ViteVariableInterface;
use nystudio107\pluginvite\variables\ViteVariableTrait;
use Twig\Markup;
use yii\base\Exception;

/**
 * Instant Analytics Variable
 *
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 */
class InstantAnalyticsVariable implements ViteVariableInterface
{
    use ViteVariableTrait;

    // Public Methods
    // =========================================================================

    /**
     * Get a PageView analytics object
     *
     * @param string $url
     * @param string $title
     *
     * @return null|IAnalytics
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
