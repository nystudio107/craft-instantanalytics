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

use nystudio107\instantanalytics\InstantAnalytics;

use Craft;
use craft\web\Controller;

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
    protected $allowAnonymous = [
        'track-page-view-url',
        'track-event-url'
    ];

    // Public Methods
    // =========================================================================

    /**
     *
     */
    public function actionTrackPageViewUrl()
    {
        $request = Craft::$app->getRequest();
        $url = $request->getParam('url');
        $title = $request->getParam('title');
        $analytics = InstantAnalytics::$plugin->ia->pageViewAnalytics($url, $title);
        $analytics->sendPageView();
        $response = Craft::$app->getResponse();
        $response->redirect($url, 200);
    }

    /**
     *
     */
    public function actionTrackEventUrl()
    {
        $request = Craft::$app->getRequest();
        $url = $request->getParam('url');
        $eventCategory = $request->getParam('eventCategory');
        $eventAction = $request->getParam('eventAction');
        $eventLabel = $request->getParam('eventLabel');
        $eventValue = $request->getParam('eventValue');
        $analytics = InstantAnalytics::$plugin->ia->eventAnalytics(
            $eventCategory,
            $eventAction,
            $eventLabel,
            $eventValue
        );
        $analytics->sendEvent();
        $response = Craft::$app->getResponse();
        $response->redirect($url, 200);
    }
}
