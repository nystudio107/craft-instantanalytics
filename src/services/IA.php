<?php
/**
 * Instant Analytics plugin for Craft CMS 3.x
 *
 * Instant Analytics brings full Google Analytics support to your Twig templates
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\instantanalytics\services;

use nystudio107\instantanalytics\InstantAnalytics;
use nystudio107\instantanalytics\helpers\IAnalytics;

use Jaybizzle\CrawlerDetect\CrawlerDetect;

use Craft;
use craft\base\Component;
use craft\elements\User as UserElement;
use craft\helpers\UrlHelper;

/**
 * IA Service
 *
 * All of your plugin’s business logic should go in services, including saving
 * data, retrieving data, etc. They provide APIs that your controllers,
 * template variables, and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 */
class IA extends Component
{
    // Public Methods
    // =========================================================================

    protected $cachedAnalytics = null;

    /**
     * Get the global variables for our Twig context
     *
     * @param $title
     *
     * @return IAnalytics
     */
    public function getGlobals($title)
    {
        if ($this->cachedAnalytics) {
            $analytics = $this->cachedAnalytics;
        } else {
            $analytics = $this->pageViewAnalytics("", $title);
            $this->cachedAnalytics = $analytics;
        }

        return $analytics;
    }

    /**
     * Get a PageView analytics object
     *
     * @param string $url
     * @param string $title
     *
     * @return null|IAnalytics
     */
    public function pageViewAnalytics($url = "", $title = "")
    {
        $result = null;
        $analytics = $this->analytics();
        if ($analytics) {
            if ($url == "") {
                $url = Craft::$app->getRequest()->getFullPath();
            }

            // We want to send just a path to GA for page views
            if (UrlHelper::isAbsoluteUrl($url)) {
                $urlParts = parse_url($url);
                if (isset($urlParts['path'])) {
                    $url = $urlParts['path'];
                } else {
                    $url = "/";
                }
                if (isset($urlParts['query'])) {
                    $url = $url . "?" . $urlParts['query'];
                }
            }

            // We don't want to send protocol-relative URLs either
            if (UrlHelper::isProtocolRelativeUrl($url)) {
                $url = substr($url, 1);
            }

            // Strip the query string if that's the global config setting
            $settings = InstantAnalytics::$plugin->getSettings();
            if (isset($settings) && isset($settings['stripQueryString']) && $settings['stripQueryString']) {
                $url = UrlHelper::stripQueryString($url);
            }

            // Prepare the Analytics object, and send the pageview
            $analytics->setDocumentPath($url)
                ->setDocumentTitle($title);
            $result = $analytics;
            Craft::info(
                "Created sendPageView for `" . $url . "` - `" . $title . "`",
                __METHOD__
            );
        }

        return $result;
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
    public function eventAnalytics($eventCategory = "", $eventAction = "", $eventLabel = "", $eventValue = 0)
    {
        $result = null;
        $analytics = $this->analytics();
        if ($analytics) {
            $analytics->setEventCategory($eventCategory)
                ->setEventAction($eventAction)
                ->setEventLabel($eventLabel)
                ->setEventValue(intval($eventValue));
            $result = $analytics;
            Craft::info(
                "Created sendEvent for `" . $eventCategory . "` - `" . $eventAction . "` - `" . $eventLabel . "` - `" . $eventValue . "`",
                __METHOD__
            );
        }

        return $result;
    }

    /**
     * getAnalyticsObject() return an analytics object
     *
     * @return IAnalytics object
     */
    public function analytics()
    {
        $analytics = $this->getAnalyticsObj();
        Craft::info(
            "Created generic analytics object",
            __METHOD__
        );

        return $analytics;
    }

    /**
     * Get a PageView tracking URL
     *
     * @param  string $url   the URL to track
     * @param  string $title the page title
     *
     * @return string the tracking URL
     */
    public function pageViewTrackingUrl($url, $title)
    {
        $urlParams = [
            'url'   => $url,
            'title' => $title,
        ];
        $fileName = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_BASENAME);
        $trackingUrl = UrlHelper::siteUrl('instant-analytics/pageViewTrack/' . $fileName, $urlParams);
        Craft::info(
            "Created pageViewTrackingUrl for " . $trackingUrl,
            __METHOD__
        );

        return $trackingUrl;
    }

    /**
     * Get an Event tracking URL
     *
     * @param  string $url           the URL to track
     * @param  string $eventCategory the event category
     * @param  string $eventAction   the event action
     * @param  string $eventLabel    the event label
     * @param  string $eventValue    the event value
     *
     * @return string the tracking URL
     */
    /**
     * @param        $url
     * @param string $eventCategory
     * @param string $eventAction
     * @param string $eventLabel
     * @param int    $eventValue
     *
     * @return mixed
     */
    public function eventTrackingUrl($url, $eventCategory = "", $eventAction = "", $eventLabel = "", $eventValue = 0)
    {
        $urlParams = [
            'url'           => $url,
            'eventCategory' => $eventCategory,
            'eventAction'   => $eventAction,
            'eventLabel'    => $eventLabel,
            'eventValue'    => $eventValue,
        ];
        $fileName = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_BASENAME);
        $trackingUrl = UrlHelper::siteUrl('instant-analytics/eventTrack/' . $fileName, $urlParams);
        Craft::info(
            "Created eventTrackingUrl for " . $trackingUrl,
            __METHOD__
        );

        return $trackingUrl;
    }

    /**
     * _shouldSendAnalytics determines whether we should be sending Google
     * Analytics data
     *
     * @return bool
     */
    public function shouldSendAnalytics()
    {
        $result = true;

        $settings = InstantAnalytics::$plugin->getSettings();
        $request = Craft::$app->getRequest();
        $requestIp = $request->getUserIP();

        if (!$settings->sendAnalyticsData) {
            if ($settings->logExcludedAnalytics) {
                Craft::info(
                    "Analytics excluded for: " . $requestIp . " due to: `sendAnalyticsData`",
                    __METHOD__
                );
            }

            return false;
        }

        if (!$settings->sendAnalyticsInDevMode && Craft::$app->getConfig()->getGeneral()->devMode) {
            if ($settings->logExcludedAnalytics) {
                Craft::info(
                    "Analytics excluded for: " . $requestIp . " due to: `sendAnalyticsInDevMode`",
                    __METHOD__
                );
            }

            return false;
        }

        if ($request->getIsConsoleRequest()) {
            if ($settings->logExcludedAnalytics) {
                Craft::info(
                    "Analytics excluded for: " . $requestIp . " due to: `craft()->isConsole()`",
                    __METHOD__
                );
            }

            return false;
        }

        if ($request->getIsCpRequest()) {
            if ($settings->logExcludedAnalytics) {
                Craft::info(
                    "Analytics excluded for: " . $requestIp . " due to: `craft()->request->isCpRequest()`",
                    __METHOD__
                );
            }

            return false;
        }

        if ($request->getIsLivePreview()) {
            if ($settings->logExcludedAnalytics) {
                Craft::info(
                    "Analytics excluded for: " . $requestIp . " due to: `craft()->request->isLivePreview()`",
                    __METHOD__
                );
            }

            return false;
        }

        // Check the $_SERVER[] super-global exclusions
        if (isset($settings->serverExcludes) && is_array($settings->serverExcludes)) {
            foreach ($settings->serverExcludes as $match => $matchArray) {
                if (isset($_SERVER[$match])) {
                    foreach ($matchArray as $matchItem) {
                        if (preg_match($matchItem, $_SERVER[$match])) {
                            if ($settings->logExcludedAnalytics) {
                                Craft::info(
                                    "Analytics excluded for: " . $requestIp . " due to: `serverExcludes`",
                                    __METHOD__
                                );
                            }

                            return false;
                        }
                    }
                }
            }
        }

        // Filter out bot/spam requests via UserAgent
        if ($settings->filterBotUserAgents) {
            $crawlerDetect = new CrawlerDetect;
            // Check the user agent of the current 'visitor'
            if ($crawlerDetect->isCrawler()) {
                if ($settings->logExcludedAnalytics) {
                    Craft::info(
                        "Analytics excluded for: " . $requestIp . " due to: `filterBotUserAgents`",
                        __METHOD__
                    );
                }

                return false;
            }
        }

        // Filter by user group
        $userService = Craft::$app->getUser();
        /** @var UserElement $user */
        $user = $userService->getIdentity();
        if ($user) {
            if ($settings->adminExclude && $user->admin) {
                if ($settings->logExcludedAnalytics) {
                    Craft::info(
                        "Analytics excluded for: " . $requestIp . " due to: `adminExclude`",
                        __METHOD__
                    );
                }

                return false;
            }

            if (isset($settings->groupExcludes) && is_array($settings->groupExcludes)) {
                foreach ($settings->groupExcludes as $matchItem) {
                    if ($user->isInGroup($matchItem)) {
                        if ($settings->logExcludedAnalytics) {
                            Craft::info(
                                "Analytics excluded for: " . $requestIp . " due to: `groupExcludes`",
                                __METHOD__
                            );
                        }

                        return false;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get the Google Analytics object, primed with the default values
     *
     * @return IAnalytics object
     */
    private function getAnalyticsObj()
    {
        $analytics = null;
        $settings = InstantAnalytics::$plugin->getSettings();
        $request = Craft::$app->getRequest();
        if (isset($settings) && !empty($settings->googleAnalyticsTracking)) {
            $analytics = new IAnalytics();
            if ($analytics) {
                $hostName = $request->getServerName();
                if (empty($hostName)) {
                    $hostName = parse_url(UrlHelper::siteUrl(), PHP_URL_HOST);
                }
                $userAgent = $request->getUserAgent();
                if (empty($userAgent)) {
                    $userAgent = "User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13\r\n";
                }
                $referrer = $request->getReferrer();
                if (empty($referrer)) {
                    $referrer = "";
                }
                $analytics->setProtocolVersion('1')
                    ->setTrackingId($settings->googleAnalyticsTracking)
                    ->setIpOverride($request->getUserIP())
                    ->setUserAgentOverride($userAgent)
                    ->setDocumentHostName($hostName)
                    ->setDocumentReferrer($referrer)
                    ->setAsyncRequest(false)
                    ->setClientId($this->gaParseCookie());

                $gclid = $this->getGclid();
                if ($gclid) {
                    $analytics->setGoogleAdwordsId($gclid);
                }

                // If SEOmatic is installed, set the affiliation as well
                // TODO: handle Seomatic
                /*
                $seomatic = craft()->plugins->getPlugin('Seomatic');
                if ($seomatic && $seomatic->isInstalled && $seomatic->isEnabled) {
                    $seomaticSettings = craft()->seomatic->getSettings(craft()->language);
                    $analytics->setAffiliation($seomaticSettings['siteSeoName']);
                }
                */
            }
        }

        return $analytics;
    } /* -- _getAnalyticsObj */

    /**
     * _getGclid get the `gclid` and sets the 'gclid' cookie
     */
    /**
     * _getGclid get the `gclid` and sets the 'gclid' cookie
     *
     * @return string
     */
    private function getGclid()
    {
        $gclid = "";
        if (isset($_GET['gclid'])) {
            $gclid = $_GET['gclid'];
            if (!empty($gclid)) {
                setcookie("gclid", $gclid, time() + (10 * 365 * 24 * 60 * 60), "/");
            }
        }

        return $gclid;
    }

    /**
     * gaParseCookie handles the parsing of the _ga cookie or setting it to a
     * unique identifier
     *
     * @return string the cid
     */
    private function gaParseCookie()
    {
        if (isset($_COOKIE['_ga'])) {
            list($version, $domainDepth, $cid1, $cid2) = preg_split('[\.]', $_COOKIE["_ga"], 4);
            $contents = ['version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1 . '.' . $cid2];
            $cid = $contents['cid'];
        } else {
            if (isset($_COOKIE['_ia']) && $_COOKIE['_ia'] != '') {
                $cid = $_COOKIE['_ia'];
            } else {
                $cid = $this->gaGenUUID();
            }
        }
        setcookie('_ia', $cid, time() + 60 * 60 * 24 * 730, "/"); // Two years

        return $cid;
    }

    /**
     * gaGenUUID Generate UUID v4 function - needed to generate a CID when one
     * isn't available
     *
     * @return string The generated UUID
     */
    private function gaGenUUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
