<?php
/**
 * Instant Analytics plugin for Craft CMS 3.x
 *
 * Instant Analytics brings full Google Analytics support to your Twig templates
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

/**
 * Instant Analytics en Translation
 *
 * Returns an array with the string to be translated (as passed to `Craft::t('instant-analytics', '...')`) as
 * the key, and the translation as the value.
 *
 * http://www.yiiframework.com/doc-2.0/guide-tutorial-i18n.html
 *
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 */
return [
    '{name} plugin loaded'            => '{name} plugin loaded',
    'Craft Commerce is not installed' => 'Craft Commerce is not installed',
    'Created sendPageView for: {eventCategory} - {eventAction} - {eventLabel} - {eventValue}' => 'Created sendPageView for: {eventCategory} - {eventAction} - {eventLabel} - {eventValue}',
    'Created eventTrackingUrl for: {trackingUrl}' => 'Created eventTrackingUrl for: {trackingUrl}',
    'Created pageViewTrackingUrl for: {trackingUrl}' => 'Created pageViewTrackingUrl for: {trackingUrl}',
    'Analytics excluded for:: {requestIp} due to: `{setting}`' => 'Analytics excluded for:: {requestIp} due to: `{setting}`',
    'Created sendPageView for: {url} - {title}' => 'Created sendPageView for: {url} - {title}',
    'Created generic analytics object' => 'Created generic analytics object',
    'Analytics not sent because googleAnalyticsTracking is not set' => 'Analytics not sent because googleAnalyticsTracking is not set',
    'pageView sent, response:: {response}' => 'pageView sent, response:: {response}',
    'addCommerceCheckoutStep step: `{step}` with option: `{option}`' => 'addCommerceCheckoutStep step: `{step}` with option: `{option}`',
    'removeFromCart for `Commerce` - `Remove to Cart` - `{title}` - `{quantity}`' => 'removeFromCart for `Commerce` - `Remove to Cart` - `{title}` - `{quantity}`',
    'Manifest file not found at: {manifestPath}' => 'Manifest file not found at: {manifestPath}',
    'orderComplete for `Commerce` - `Purchase` - `{number}` - `{price}`' => 'orderComplete for `Commerce` - `Purchase` - `{number}` - `{price}`',
    'addCommerceProductImpression for `{sku}` - `{name}` - `{name}` - `{index}`' => 'addCommerceProductImpression for `{sku}` - `{name}` - `{name}` - `{index}`',
    'Module does not exist in the manifest: {moduleName}' => 'Module does not exist in the manifest: {moduleName}',
    'addCommerceProductDetailView for `{sku}` - `{name} - `{name}`' => 'addCommerceProductDetailView for `{sku}` - `{name} - `{name}`',
    'addToCart for `Commerce` - `Add to Cart` - `{title}` - `{quantity}`' => 'addToCart for `Commerce` - `Add to Cart` - `{title}` - `{quantity}`'
];
