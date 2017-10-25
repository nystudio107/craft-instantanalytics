<?php
/**
 * Instant Analytics plugin for Craft CMS
 *
 * @author    nystudio107
 * @copyright Copyright (c) 2016 nystudio107
 * @link      http://nystudio107.com
 * @package   InstantAnalytics
 * @since     1.0.0
 */

namespace nystudio107\instantanalytics\helpers;

use nystudio107\instantanalytics\InstantAnalytics;
use nystudio107\instantanalytics\models\Settings;

use Craft;

use \TheIconic\Tracking\GoogleAnalytics\Analytics;
use \TheIconic\Tracking\GoogleAnalytics\AnalyticsResponseInterface;

class IAnalytics extends Analytics
{

    protected $shouldSendAnalytics = null;

    /**
     * Override __construct() to store whether or not we should be sending
     * Analytics data
     *
     * @param bool $isSsl
     */
    public function __construct($isSsl = false)
    {
        /** @var Settings $settings */
        $settings = InstantAnalytics::$plugin->getSettings();
        $this->shouldSendAnalytics = $settings->sendAnalyticsData;

        return parent::__construct($isSsl);
    }

    /**
     * Turn an empty value so the twig tags {{ }} can be used
     *
     * @return string ""
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
     * @return AnalyticsResponseInterface|null
     */
    protected function sendHit($methodName)
    {
        /** @var Settings $settings */
        $settings = InstantAnalytics::$plugin->getSettings();
        $requestIp = $_SERVER['REMOTE_ADDR'];
        if ($this->shouldSendAnalytics) {
            try {
                return parent::sendHit($methodName);
            } catch (\Exception $e) {
                if ($settings->logExcludedAnalytics) {
                    Craft::info(
                        "*** sendHit(): error sending analytics: " . $e->getMessage(),
                        __METHOD__
                    );
                }
            }
        } else {
            if ($settings->logExcludedAnalytics) {
                Craft::info(
                    "*** sendHit(): analytics not sent for " . $requestIp,
                    __METHOD__
                );
            }

            return null;
        }
    }

    /**
     * Add a product impression to the Analytics object
     *
     * @param     Commerce_ProductModel or Commerce_VariantModel
     *                                     $productVariant the Product or
     *                                     Variant
     * @param int $index                Where the product appears in the list
     */
    public function addCommerceProductImpression($productVariant = null, $index = 0, $listName = "default", $listIndex = 1)
    {

        if ($productVariant) {
            craft()->instantAnalytics->addCommerceProductImpression($this, $productVariant, $index, $listName, $listIndex);
        }
    } /* -- addCommerceProductImpression */

    /**
     * Add a product detail view to the Analytics object
     *
     * @param Commerce_ProductModel or Commerce_VariantModel  $productVariant
     *                                 the Product or Variant
     */
    public function addCommerceProductDetailView($productVariant = null)
    {
        if ($productVariant) {
            craft()->instantAnalytics->addCommerceProductDetailView($this, $productVariant);
        }
    } /* -- addCommerceProductDetailView */

    /**
     * Add a checkout step to the Analytics object
     *
     * @param Commerce_ProductModel or Commerce_VariantModel  $productVariant
     *                                 the Product or Variant
     */
    public function addCommerceCheckoutStep($orderModel = null, $step = 1, $option = "")
    {
        if ($orderModel) {
            craft()->instantAnalytics->addCommerceCheckoutStep($this, $orderModel, $step, $option);
        }
    } /* -- addCommerceCheckoutStep */

}