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
use nystudio107\instantanalytics\models\Settings;

use Jaybizzle\CrawlerDetect\CrawlerDetect;

use Craft;
use craft\base\Component;
use craft\elements\User as UserElement;
use craft\helpers\UrlHelper;

use nystudio107\seomatic\Seomatic;

use yii\base\Exception;

/** @noinspection MissingPropertyAnnotationsInspection */

/**
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 */
class IA extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * @var null|IAnalytics
     */
    protected $cachedAnalytics;

    /**
     * Get the global variables for our Twig context
     *
     * @param $title
     *
     * @return null|IAnalytics
     */
    public function getGlobals($title)
    {
        if ($this->cachedAnalytics) {
            $analytics = $this->cachedAnalytics;
        } else {
            $analytics = $this->pageViewAnalytics('', $title);
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
    public function pageViewAnalytics($url = '', $title = '')
    {
        $result = null;
        $analytics = $this->analytics();
        if ($analytics) {
            $url = $this->documentPathFromUrl($url);
            // Prepare the Analytics object, and send the pageview
            $analytics->setDocumentPath($url)
                ->setDocumentTitle($title);
            $result = $analytics;
            Craft::info(
                Craft::t(
                    'instant-analytics',
                    'Created sendPageView for: {url} - {title}',
                    [
                        'url' => $url,
                        'title' => $title
                    ]
                ),
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
    public function eventAnalytics($eventCategory = '', $eventAction = '', $eventLabel = '', $eventValue = 0)
    {
        $result = null;
        $analytics = $this->analytics();
        if ($analytics) {
            $url = $this->documentPathFromUrl();
            $analytics->setDocumentPath($url)
                ->setEventCategory($eventCategory)
                ->setEventAction($eventAction)
                ->setEventLabel($eventLabel)
                ->setEventValue((int)$eventValue);
            $result = $analytics;
            Craft::info(
                Craft::t(
                    'instant-analytics',
                    'Created sendPageView for: {eventCategory} - {eventAction} - {eventLabel} - {eventValue}',
                    [
                        'eventCategory' => $eventCategory,
                        'eventAction' => $eventAction,
                        'eventLabel' => $eventLabel,
                        'eventValue' => $eventValue
                    ]
                ),
                __METHOD__
            );
        }

        return $result;
    }

    /**
     * getAnalyticsObject() return an analytics object
     *
     * @return null|IAnalytics object
     */
    public function analytics()
    {
        $analytics = $this->getAnalyticsObj();
        Craft::info(
            Craft::t(
                'instant-analytics',
                'Created generic analytics object'
            ),
            __METHOD__
        );

        return $analytics;
    }

    /**
     * Get a PageView tracking URL
     *
     * @param $url
     * @param $title
     *
     * @return string
     * @throws \yii\base\Exception
     */
    public function pageViewTrackingUrl($url, $title): string
    {
        $urlParams = [
            'url'   => $url,
            'title' => $title,
        ];
        $path = parse_url($url, PHP_URL_PATH);
        $pathFragments = explode('/', rtrim($path, '/'));
        $fileName = end($pathFragments);
        $trackingUrl = UrlHelper::siteUrl('instantanalytics/pageViewTrack/'.$fileName, $urlParams);
        Craft::info(
            Craft::t(
                'instant-analytics',
                'Created pageViewTrackingUrl for: {trackingUrl}',
                [
                    'trackingUrl' => $trackingUrl,
                ]
            ),
            __METHOD__
        );

        return $trackingUrl;
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
     * @return string
     * @throws \yii\base\Exception
     */
    public function eventTrackingUrl(
        $url,
        $eventCategory = '',
        $eventAction = '',
        $eventLabel = '',
        $eventValue = 0
    ): string {
        $urlParams = [
            'url'           => $url,
            'eventCategory' => $eventCategory,
            'eventAction'   => $eventAction,
            'eventLabel'    => $eventLabel,
            'eventValue'    => $eventValue,
        ];
        $fileName = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_BASENAME);
        $trackingUrl = UrlHelper::siteUrl('instantanalytics/eventTrack/'.$fileName, $urlParams);
        Craft::info(
            Craft::t(
                'instant-analytics',
                'Created eventTrackingUrl for: {trackingUrl}',
                [
                    'trackingUrl' => $trackingUrl,
                ]
            ),
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
    public function shouldSendAnalytics(): bool
    {
        $result = true;

        /** @var Settings $settings */
        $settings = InstantAnalytics::$plugin->getSettings();
        $request = Craft::$app->getRequest();

        if (!$settings->sendAnalyticsData) {
            $this->logExclusion('sendAnalyticsData');

            return false;
        }

        if (!$settings->sendAnalyticsInDevMode && Craft::$app->getConfig()->getGeneral()->devMode) {
            $this->logExclusion('sendAnalyticsInDevMode');

            return false;
        }

        if ($request->getIsConsoleRequest()) {
            $this->logExclusion('Craft::$app->getRequest()->getIsConsoleRequest()');

            return false;
        }

        if ($request->getIsCpRequest()) {
            $this->logExclusion('Craft::$app->getRequest()->getIsCpRequest()');

            return false;
        }

        if ($request->getIsLivePreview()) {
            $this->logExclusion('Craft::$app->getRequest()->getIsLivePreview()');

            return false;
        }

        // Check the $_SERVER[] super-global exclusions
        if ($settings->serverExcludes !== null && \is_array($settings->serverExcludes)) {
            foreach ($settings->serverExcludes as $match => $matchArray) {
                if (isset($_SERVER[$match])) {
                    foreach ($matchArray as $matchItem) {
                        if (preg_match($matchItem, $_SERVER[$match])) {
                            $this->logExclusion('serverExcludes');

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
                $this->logExclusion('filterBotUserAgents');

                return false;
            }
        }

        // Filter by user group
        $userService = Craft::$app->getUser();
        /** @var UserElement $user */
        $user = $userService->getIdentity();
        if ($user) {
            if ($settings->adminExclude && $user->admin) {
                $this->logExclusion('adminExclude');

                return false;
            }

            if ($settings->groupExcludes !== null && \is_array($settings->groupExcludes)) {
                foreach ($settings->groupExcludes as $matchItem) {
                    if ($user->isInGroup($matchItem)) {
                        $this->logExclusion('groupExcludes');

                        return false;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Log the reason for excluding the sending of analytics
     *
     * @param string $setting
     */
    protected function logExclusion(string $setting)
    {
        /** @var Settings $settings */
        $settings = InstantAnalytics::$plugin->getSettings();
        if ($settings->logExcludedAnalytics) {
            $request = Craft::$app->getRequest();
            $requestIp = $request->getUserIP();
            Craft::info(
                Craft::t(
                    'instant-analytics',
                    'Analytics excluded for:: {requestIp} due to: `{setting}`',
                    [
                        'requestIp' => $requestIp,
                        'setting' => $setting,
                    ]
                ),
                __METHOD__
            );
        }
    }

    /**
     * Return a sanitized documentPath from a URL
     *
     * @param $url
     *
     * @return string
     */
    protected function documentPathFromUrl($url = ''): string
    {
        if ($url === '') {
            $url = Craft::$app->getRequest()->getFullPath();
        }

        // We want to send just a path to GA for page views
        if (UrlHelper::isAbsoluteUrl($url)) {
            $urlParts = parse_url($url);
            if (isset($urlParts['path'])) {
                $url = $urlParts['path'];
            } else {
                $url = '/';
            }
            if (isset($urlParts['query'])) {
                $url = $url.'?'.$urlParts['query'];
            }
        }

        // We don't want to send protocol-relative URLs either
        if (UrlHelper::isProtocolRelativeUrl($url)) {
            $url = substr($url, 1);
        }

        // Strip the query string if that's the global config setting
        $settings = InstantAnalytics::$plugin->getSettings();
        if (isset($settings, $settings->stripQueryString) && $settings->stripQueryString) {
            $url = UrlHelper::stripQueryString($url);
        }

        // We always want the path to be / rather than empty
        if ($url === '') {
            $url = '/';
        }

        return $url;
    }

    /**
     * Get the Google Analytics object, primed with the default values
     *
     * @return null|IAnalytics object
     */
    private function getAnalyticsObj()
    {
        $analytics = null;
        $settings = InstantAnalytics::$plugin->getSettings();
        $request = Craft::$app->getRequest();
        if ($settings !== null && !empty($settings->googleAnalyticsTracking)) {
            $analytics = new IAnalytics();
            if ($analytics) {
                $hostName = $request->getServerName();
                if (empty($hostName)) {
                    try {
                        $hostName = parse_url(UrlHelper::siteUrl(), PHP_URL_HOST);
                    } catch (Exception $e) {
                        Craft::error(
                            $e->getMessage(),
                            __METHOD__
                        );
                    }
                }
                $userAgent = $request->getUserAgent();
                if (empty($userAgent)) {
                    $userAgent = "User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13\r\n";
                }
                $referrer = $request->getReferrer();
                if (empty($referrer)) {
                    $referrer = '';
                }
                $analytics->setProtocolVersion('1')
                    ->setTrackingId($settings->googleAnalyticsTracking)
                    ->setIpOverride($request->getUserIP())
                    ->setUserAgentOverride($userAgent)
                    ->setDocumentHostName($hostName)
                    ->setDocumentReferrer($referrer)
                    ->setAsyncRequest(false)
                    ->setClientId($this->gaParseCookie());

                // Set the gclid
                $gclid = $this->getGclid();
                if ($gclid) {
                    $analytics->setGoogleAdwordsId($gclid);
                }

                // Handle UTM parameters
                $utm_source = $request->getParam('utm_source');
                if (!empty($utm_source)) {
                    $analytics->setCampaignSource($utm_source);
                }
                $utm_medium = $request->getParam('utm_medium');
                if (!empty($utm_medium)) {
                    $analytics->setCampaignMedium($utm_medium);
                }
                $utm_campaign = $request->getParam('utm_campaign');
                if (!empty($utm_campaign)) {
                    $analytics->setCampaignName($utm_campaign);
                }
                $utm_content = $request->getParam('utm_content');
                if (!empty($utm_content)) {
                    $analytics->setCampaignContent($utm_content);
                }

                // If SEOmatic is installed, set the affiliation as well
                if (InstantAnalytics::$seomaticPlugin && Seomatic::$settings->renderEnabled) {
                    if (Seomatic::$plugin->metaContainers->metaSiteVars !== null) {
                        $siteName = Seomatic::$plugin->metaContainers->metaSiteVars->siteName;
                        $analytics->setAffiliation($siteName);
                    }
                }
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
        $gclid = '';
        if (isset($_GET['gclid'])) {
            $gclid = $_GET['gclid'];
            if (!empty($gclid)) {
                setcookie('gclid', $gclid, strtotime('+10 years'), '/');
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
        $cid = '';
        if (isset($_COOKIE['_ga'])) {
            $parts = preg_split('[\.]', $_COOKIE["_ga"], 4);
            if ($parts !== false) {
                $cid = implode('.', \array_slice($parts, 2));
            }
        } else {
            if (isset($_COOKIE['_ia']) && $_COOKIE['_ia'] !== '') {
                $cid = $_COOKIE['_ia'];
            } else {
                $cid = $this->gaGenUUID();
            }
        }
        setcookie('_ia', $cid, strtotime('+2 years'), '/'); // Two years

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
