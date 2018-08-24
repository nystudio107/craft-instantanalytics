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
    'pageView sent, response:: {response}' => 'pageView sent, response:: {response}'
];
