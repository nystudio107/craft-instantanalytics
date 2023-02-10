<?php
/**
 * Instant Analytics plugin for Craft CMS
 *
 * @author    nystudio107
 * @copyright Copyright (c) 2017 nystudio107
 * @link      http://nystudio107.com
 * @package   InstantAnalytics
 * @since     1.0.0
 */

namespace nystudio107\instantanalytics\helpers;

use Craft;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use Exception;
use nystudio107\instantanalytics\InstantAnalytics;
use TheIconic\Tracking\GoogleAnalytics\Analytics;
use TheIconic\Tracking\GoogleAnalytics\AnalyticsResponseInterface;

/**
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 */
class IAnalytics extends Analytics
{

    protected bool $shouldSendAnalytics = false;

    /**
     * @inheritdoc
     */
    public function __construct($isSsl = false, $isDisabled = false, array $options = [])
    {
        // Store whether or not we should be sending Analytics data
        $this->shouldSendAnalytics = InstantAnalytics::$settings->sendAnalyticsData;

        parent::__construct($isSsl, $isDisabled, $options);
    }

    /**
     * Turn an empty value so the twig tags {{ }} can be used
     *
     * @return string ''
     */
    public function __toString()
    {
        return '';
    }

    /**
     * Add a product impression to the Analytics object
     *
     * @param ?string $productVariant
     * @param int $index
     * @param string $listName
     * @param int $listIndex
     */
    public function addCommerceProductImpression(
        ?string $productVariant = null,
        int     $index = 0,
        string  $listName = 'default',
        int     $listIndex = 1
    ): void
    {

        if (InstantAnalytics::$commercePlugin) {
            if ($productVariant) {
                InstantAnalytics::$plugin->commerce->addCommerceProductImpression(
                    $this,
                    $productVariant,
                    $index,
                    $listName,
                    $listIndex
                );
            }
        } else {
            Craft::warning(
                Craft::t(
                    'instant-analytics',
                    'Craft Commerce is not installed'
                ),
                __METHOD__
            );
        }
    }

    /**
     * Add a product detail view to the Analytics object
     *
     * @param null|Product|Variant $productVariant
     */
    public function addCommerceProductDetailView(null|Product|Variant $productVariant = null): void
    {
        if (InstantAnalytics::$commercePlugin) {
            if ($productVariant) {
                InstantAnalytics::$plugin->commerce->addCommerceProductDetailView($this, $productVariant);
            }
        } else {
            Craft::warning(
                Craft::t(
                    'instant-analytics',
                    'Craft Commerce is not installed'
                ),
                __METHOD__
            );
        }
    }

    /**
     * Add a checkout step to the Analytics object
     *
     * @param        $orderModel
     * @param int $step
     * @param string $option
     */
    public function addCommerceCheckoutStep($orderModel = null, $step = 1, $option = ""): void
    {
        if (InstantAnalytics::$commercePlugin) {
            if ($orderModel) {
                InstantAnalytics::$plugin->commerce->addCommerceCheckoutStep($this, $orderModel, $step, $option);
            }
        } else {
            Craft::warning(
                Craft::t(
                    'instant-analytics',
                    'Craft Commerce is not installed'
                ),
                __METHOD__
            );
        }
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
        $requestIp = $_SERVER['REMOTE_ADDR'];
        if ($this->shouldSendAnalytics) {
            if ($this->getClientId() !== null || $this->getUserId() !== null) {
                try {
                    Craft::info(
                        'Send hit for IAnalytics object: ' . print_r($this, true),
                        __METHOD__
                    );

                    return parent::sendHit($methodName);
                } catch (Exception $e) {
                    if (InstantAnalytics::$settings->logExcludedAnalytics) {
                        Craft::info(
                            '*** sendHit(): error sending analytics: ' . $e->getMessage(),
                            __METHOD__
                        );
                    }
                }
            } elseif (InstantAnalytics::$settings->logExcludedAnalytics) {
                Craft::info(
                    '*** sendHit(): analytics not sent for ' . $requestIp . ' because no clientId or userId is set',
                    __METHOD__
                );
            }
        } elseif (InstantAnalytics::$settings->logExcludedAnalytics) {
            Craft::info(
                '*** sendHit(): analytics not sent for ' . $requestIp,
                __METHOD__
            );
        }

        return null;
    }
}
