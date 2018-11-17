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

use Craft;
use craft\base\Component;
use craft\elements\db\CategoryQuery;
use craft\elements\db\MatrixBlockQuery;
use craft\elements\db\TagQuery;

use craft\commerce\Plugin as CommercePlugin;
use craft\commerce\base\Purchasable;
use craft\commerce\elements\Order;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\commerce\models\LineItem;

/**
 * Commerce Service
 *
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 */
class Commerce extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Send analytics information for the completed order
     *
     * @param Order  $order the Product or Variant
     */
    public function orderComplete($order = null)
    {
        if ($order) {
            $analytics = InstantAnalytics::$plugin->ia->eventAnalytics(
                'Commerce',
                'Purchase',
                $order->number,
                $order->totalPrice
            );
            
            if ($analytics) {
                $this->addCommerceOrderToAnalytics($analytics, $order);
                // Don't forget to set the product action, in this case to PURCHASE
                $analytics->setProductActionToPurchase();
                $analytics->sendEvent();

                Craft::info(Craft::t(
                    'instant-analytics',
                    'orderComplete for `Commerce` - `Purchase` - `{number}` - `{price}`',
                    [ 'number' => $order->number, 'price' => $order->totalPrice ]
                ), __METHOD__);
            }
        }
    }

    /**
     * Send analytics information for the item added to the cart
     * @param Order  $order the Product or Variant
     * @param LineItem  $lineItem the line item that was added
     */
    public function addToCart(/** @noinspection PhpUnusedParameterInspection */ $order = null, $lineItem = null)
    {
        if ($lineItem) {
            $title = $lineItem->purchasable->title;
            $quantity = $lineItem->qty;
            $analytics = InstantAnalytics::$plugin->ia->eventAnalytics('Commerce', 'Add to Cart', $title, $quantity);
            
            if ($analytics) {
                $title = $this->addProductDataFromLineItem($analytics, $lineItem);
                $analytics->setEventLabel($title);
                // Don't forget to set the product action, in this case to ADD
                $analytics->setProductActionToAdd();
                $analytics->sendEvent();

                Craft::info(Craft::t(
                    'instant-analytics',
                    'addToCart for `Commerce` - `Add to Cart` - `{title}` - `{quantity}`',
                    [ 'title' => $title, 'quantity' => $quantity ]
                ), __METHOD__);
            }
        }
    }

    /**
     * Send analytics information for the item removed from the cart
     *
     * @param Order|null    $order
     * @param LineItem|null $lineItem
     */
    public function removeFromCart(/** @noinspection PhpUnusedParameterInspection */ $order = null, $lineItem = null)
    {
        if ($lineItem) {
            $title = $lineItem->purchasable->title;
            $quantity = $lineItem->qty;
            $analytics = InstantAnalytics::$plugin->ia->eventAnalytics(
                'Commerce',
                'Remove from Cart',
                $title,
                $quantity
            );
            
            if ($analytics) {
                $title = $this->addProductDataFromLineItem($analytics, $lineItem);
                $analytics->setEventLabel($title);
                // Don't forget to set the product action, in this case to ADD
                $analytics->setProductActionToRemove();
                $analytics->sendEvent();

                Craft::info(Craft::t(
                    'instant-analytics',
                    'removeFromCart for `Commerce` - `Remove to Cart` - `{title}` - `{quantity}`',
                    [ 'title' => $title, 'quantity' => $quantity ]
                ), __METHOD__);
            }
        }
    }


    /**
     * Add a Craft Commerce OrderModel to an Analytics object
     *
     * @param IAnalytics $analytics the Analytics object
     * @param Order  $order the Product or Variant
     */
    public function addCommerceOrderToAnalytics($analytics = null, $order = null)
    {
        if ($order && $analytics) {
            // First, include the transaction data
            $analytics->setTransactionId($order->number)
                ->setRevenue($order->totalPrice)
                ->setTax($order->getAdjustmentsTotalByType('tax', true))
                ->setShipping($order->getAdjustmentsTotalByType('shipping', true));
            
            // Coupon code?
            if ($order->couponCode) {
                $analytics->setCouponCode($order->couponCode);
            }

            // Add each line item in the transaction
            // Two cases - variant and non variant products
            $index = 1;

            foreach ($order->lineItems as $key => $lineItem) {
                $this->addProductDataFromLineItem($analytics, $lineItem, $index, '');
                $index++;
            }
        }
    }

    /**
     * Add a Craft Commerce LineItem to an Analytics object
     *
     * @param IAnalytics|null   $analytics
     * @param LineItem|null   $lineItem
     * @param int    $index
     * @param string $listName
     *
     * @return string the title of the product
     */
    public function addProductDataFromLineItem($analytics = null, $lineItem = null, $index = 0, $listName = ''): string
    {
        $result = '';
        if ($lineItem && $analytics) {
            //This is the same for both variant and non variant products
            $productData = [
                'sku' => $lineItem->purchasable->sku,
                'price' => $lineItem->salePrice,
                'quantity' => $lineItem->qty,
            ];

            $productVariant = $lineItem->purchasable;
            $productData['name'] = $lineItem->purchasable->title;
            /**
             * @TODO: See if there is a Commerce 2 equivalent
            $productData['category'] = $lineItem->purchasable->type->name;
             */

            $result = $productData['name'];

            if ($index) {
                $productData['position'] = $index;
            }

            if ($listName) {
                $productData['list'] = $listName;
            }

            $settings = InstantAnalytics::$plugin->getSettings();

            if ($settings) {
                if (isset($settings['productCategoryField']) && !empty($settings['productCategoryField'])) {
                    $productData['category'] = $this->pullDataFromField(
                        $productVariant,
                        $settings['productCategoryField']
                    );
                }

                if (isset($settings['productBrandField']) && !empty($settings['productBrandField'])) {
                    $productData['brand'] = $this->pullDataFromField(
                        $productVariant,
                        $settings['productBrandField']
                    );
                }
            }

            //Add each product to the hit to be sent
            $analytics->addProduct($productData);
        }

        return $result;
    }

    /**
     * Add a product impression from a Craft Commerce Product or Variant
     *
     * @param IAnalytics      $analytics      the Analytics object
     * @param Product|Variant $productVariant the Product or Variant
     * @param int             $index          Where the product appears in the list
     * @param string          $listName
     * @param int             $listIndex
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function addCommerceProductImpression(
        $analytics = null,
        $productVariant = null,
        $index = 0,
        $listName = 'default',
        $listIndex = 1
    ) {
        if ($productVariant && $analytics) {
            $productData = $this->getProductDataFromProduct($productVariant);

            /**
             * As per: https://github.com/theiconic/php-ga-measurement-protocol/issues/26
             */
            if ($listName && $listIndex) {
                $analytics->setProductImpressionListName($listName, $listIndex);
            }

            if ($index) {
                $productData['position'] = $index;
            }

            //Add the product to the hit to be sent
            $analytics->addProductImpression($productData, $listIndex);

            Craft::info(Craft::t(
                'instant-analytics',
                'addCommerceProductImpression for `{sku}` - `{name}` - `{name}` - `{index}`',
                [ 'sku' => $productData['sku'], 'name' => $productData['name'], 'index' => $index ]
            ), __METHOD__);
        }
    }

    /**
     * Add a product detail view from a Craft Commerce Product or Variant
     *
     * @param IAnalytics      $analytics      the Analytics object
     * @param Product|Variant $productVariant the Product or Variant
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function addCommerceProductDetailView($analytics = null, $productVariant = null)
    {
        if ($productVariant && $analytics) {
            $productData = $this->getProductDataFromProduct($productVariant);

            // Don't forget to set the product action, in this case to DETAIL
            $analytics->setProductActionToDetail();

            //Add the product to the hit to be sent
            $analytics->addProduct($productData);

            Craft::info(Craft::t(
                'instant-analytics',
                'addCommerceProductDetailView for `{sku}` - `{name} - `{name}`',
                [ 'sku' => $productData['sku'], 'name' => $productData['name'] ]
            ), __METHOD__);
        }
    }

    /**
     * Add a checkout step and option to an Analytics object
     *
     * @param IAnalytics $analytics the Analytics object
     * @param Order      $order     the Product or Variant
     * @param int        $step      the checkout step
     * @param string     $option    the checkout option
     */
    public function addCommerceCheckoutStep($analytics = null, $order = null, $step = 1, $option = '')
    {
        if ($order && $analytics) {
            // Add each line item in the transaction
            // Two cases - variant and non variant products
            $index = 1;

            foreach ($order->lineItems as $key => $lineItem) {
                $this->addProductDataFromLineItem($analytics, $lineItem, $index, '');
                $index++;
            }

            $analytics->setCheckoutStep($step);
            
            if ($option) {
                $analytics->setCheckoutStepOption($option);
            }

            // Don't forget to set the product action, in this case to CHECKOUT
            $analytics->setProductActionToCheckout();

            Craft::info(Craft::t(
                'instant-analytics',
                'addCommerceCheckoutStep step: `{step}` with option: `{option}`',
                [ 'step' => $step, 'option' => $option ]
            ), __METHOD__);
        }
    }

    /**
     * Extract product data from a Craft Commerce Product or Variant
     *
     * @param Product|Variant $productVariant the Product or Variant
     *
     * @return array the product data
     * @throws \yii\base\InvalidConfigException
     */
    public function getProductDataFromProduct($productVariant = null): array
    {
        $result = [];
        
        // Extract the variant if it's a Product or Purchasable
        if ($productVariant && \is_object($productVariant)) {
            if (is_a($productVariant, Product::class)
                || is_a($productVariant, Purchasable::class)
            ) {
                $productType = property_exists($productVariant, 'typeId')
                    ? InstantAnalytics::$commercePlugin->getProductTypes()->getProductTypeById($productVariant->typeId)
                    : null;
                
                if ($productType && $productType->hasVariants) {
                    $productVariants = $productVariant->getVariants();
                    $productVariant = reset($productVariants);
                    $product = $productVariant->getProduct();
                    
                    if ($product) {
                        $category = $product->getType()['name'];
                        $name = $product->title;
                        $variant = $productVariant->title;
                    } else {
                        $category = $productVariant->getType()['name'];
                        $name = $productVariant->title;
                        $variant = '';
                    }
                } else {
                    if (!empty($productVariant->defaultVariantId)) {
                        /** @var Variant $productVariant */
                        $productVariant = InstantAnalytics::$commercePlugin->getVariants()->getVariantById(
                            $productVariant->defaultVariantId
                        );
                        $category = $productVariant->getProduct()->getType()['name'];
                        $name = $productVariant->title;
                        $variant = '';
                    } else {
                        if (isset($productVariant->product)) {
                            $category = $productVariant->product->getType()['name'];
                            $name = $productVariant->product->title;
                        } else {
                            $category = $productVariant->getType()['name'];
                            $name = $productVariant->title;
                        }
                        $variant = $productVariant->title;
                    }
                }
            }

            $productData = [
                'sku' => $productVariant->sku,
                'name' => $name,
                'price' => number_format($productVariant->price, 2, '.', ''),
                'category' => $category,
            ];

            if ($variant) {
                $productData['variant'] = $variant;
            }

            $settings = InstantAnalytics::$plugin->getSettings();
            $isVariant = is_a($productVariant, Variant::class);
            
            if ($settings && isset($settings['productCategoryField']) && !empty($settings['productCategoryField'])) {
                $productData['category'] = $this->pullDataFromField(
                    $productVariant,
                    $settings['productCategoryField']
                );

                if (empty($productData['category']) && $isVariant) {
                    $productData['category'] = $this->pullDataFromField(
                        $productVariant->product,
                        $settings['productCategoryField']
                    );
                }
            }

            if ($settings && isset($settings['productBrandField']) && !empty($settings['productBrandField'])) {
                $productData['brand'] = $this->pullDataFromField(
                    $productVariant,
                    $settings['productBrandField'],
                    true
                );

                if (empty($productData['brand']) && $isVariant) {
                    $productData['brand'] = $this->pullDataFromField(
                        $productVariant,
                        $settings['productBrandField'],
                        true
                    );
                }
            }

            $result = $productData;
        }

        return $result;
    }

    /**
     * @param Product|Variant|null $productVariant
     * @param string $fieldHandle
     * @param bool $isBrand
     *
     * @return string
     */
    private function pullDataFromField($productVariant, $fieldHandle, $isBrand = false): string
    {
        $result = '';

        if ($productVariant && $fieldHandle) {
            $srcField = $productVariant[$fieldHandle];

            if ($srcField === null) {
                $srcField = $productVariant->product->$fieldHandle;
            }

            switch (\get_class($srcField)) {
                case MatrixBlockQuery::class:
                    break;
                case TagQuery::class:
                    break;
                case CategoryQuery::class:
                    $cats = [];

                    if ($isBrand) {
                        // Because we can only have one brand, we'll get
                        // the very last category. This means if our
                        // brand is a sub-category, we'll get the child
                        // not the parent.
                        foreach ($srcField->all() as $cat) {
                            $cats = [$cat->title];
                        }
                    } else {
                        // For every category, show its ancestors
                        // delimited by a slash.
                        foreach ($srcField->all() as $cat) {
                            $name = $cat->title;

                            while ($cat = $cat->parent) {
                                $name = $cat->title .'/'. $name;
                            }

                            $cats[] = $name;
                        }
                    }

                    // Join separate categories with a pipe.
                    $result = implode('|', $cats);
                    break;


                default:
                    $result = strip_tags($srcField);
                    break;
            }
        }

        return $result;
    }
}
