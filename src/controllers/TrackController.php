<?php
/**
 * Instant Analytics plugin for Craft CMS 3.x
 *
 * Instant Analytics brings full Google Analytics support to your Twig templates
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\instantanalytics\controllers;

use craft\web\Controller;
use nystudio107\instantanalytics\InstantAnalytics;

/**
 * TrackController
 *
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 */
class TrackController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected array|bool|int $allowAnonymous = [
        'track-page-view-url',
        'track-event-url'
    ];

    // Public Methods
    // =========================================================================

    /**
     * @param string $url
     * @param string $title
     */
    public function actionTrackPageViewUrl(string $url, string $title): void
    {
        $analytics = InstantAnalytics::$plugin->ia->pageViewAnalytics($url, $title);
        $analytics?->sendPageview();
        $this->redirect($url, 200);
    }

    /**
     * @param string $url
     * @param string $eventCategory
     * @param string $eventAction
     * @param string $eventLabel
     * @param int $eventValue
     */
    public function actionTrackEventUrl(
        string $url,
        string $eventCategory,
        string $eventAction,
        string $eventLabel,
        int    $eventValue
    ): void
    {
        $analytics = InstantAnalytics::$plugin->ia->eventAnalytics(
            $eventCategory,
            $eventAction,
            $eventLabel,
            $eventValue
        );
        // Get the file name
        $path = parse_url($url, PHP_URL_PATH);
        $analytics?->setDocumentPath($path)
            ->sendEvent();
        $this->redirect($url, 200);
    }
}
