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

namespace nystudio107\instantanalytics\helpers;

use nystudio107\instantanalytics\InstantAnalytics;

use Craft;

use \TheIconic\Tracking\GoogleAnalytics\Analytics;
use \TheIconic\Tracking\GoogleAnalytics\AnalyticsResponse;

/**
 * Class InstantAnalytics
 *
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 */
class IAnalytics extends Analytics
{

    /**
     * @var bool
     */
    protected $shouldSendAnalytics = null;

    /**
     * IAnalytics constructor.
     *
     * @param bool $isSsl
     */
    public function __construct($isSsl = false)
    {
        $this->shouldSendAnalytics = InstantAnalytics::$plugin->ia->shouldSendAnalytics();
        return parent::__construct($isSsl);
    }

    /**
     * Return an empty value so the twig tags {{ }} can be used
     *
     * @return string
     */
    public function __toString()
    {
        return "";
    }

    /**
     * Override sendHit() so that we can prevent Analytics data from being sent
     *
     * @param $methodName
     *
     * @return null|AnalyticsResponse
     */
    protected function sendHit($methodName)
    {
        $loggingFlag = Craft::$app->config->get('logExcludedAnalytics', 'instantanalytics');
        $requestIp = $_SERVER['REMOTE_ADDR'];
        if ($this->shouldSendAnalytics) {
            try {
                return parent::sendHit($methodName);
            } catch (\Exception $e) {
                Craft::error(
                    '*** sendHit(): error sending analytics: ' . $e->getMessage(),
                    __METHOD__
                );
            }
        } else {
            Craft::info(
                '*** sendHit(): analytics not sent for' . $requestIp,
                __METHOD__
            );
        }

        return null;
    }

    /**
     * Add a product impression to the Analytics object
     *
     * @param null   $productVariant
     * @param int    $index
     * @param string $listName
     * @param int    $listIndex
     */
    public function addCommerceProductImpression(
        $productVariant = null,
        $index = 0,
        $listName = "default",
        $listIndex = 1
    ) {
        if ($productVariant) {
            InstantAnalytics::$plugin->ia->addCommerceProductImpression(
                $this,
                $productVariant,
                $index,
                $listName,
                $listIndex
            );
        }
    }

    /**
     * Add a product detail view to the Analytics object
     *
     * @param null $productVariant
     */
    public function addCommerceProductDetailView($productVariant = null)
    {
        if ($productVariant) {
            InstantAnalytics::$plugin->ia->addCommerceProductDetailView($this, $productVariant);
        }
    }

    /**
     * Add a checkout step to the Analytics object
     *
     * @param null   $orderModel
     * @param int    $step
     * @param string $option
     */
    public function addCommerceCheckoutStep($orderModel = null, $step = 1, $option = "")
    {
        if ($orderModel) {
            InstantAnalytics::$plugin->ia->addCommerceCheckoutStep($this, $orderModel, $step, $option);
        }
    }
}
