<?php
/**
 * Instant Analytics plugin for Craft CMS 3.x
 *
 * Instant Analytics brings full Google Analytics support to your Twig templates
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\instantanalytics\models;

use nystudio107\instantanalytics\InstantAnalytics;

use Craft;
use craft\base\Model;
use craft\validators\ArrayValidator;

/**
 * InstantAnalytics Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a
 * model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    nystudio107
 * @package   InstantAnalytics
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * The default Google Analytics tracking ID
     *
     * @var string
     */
    public $googleAnalyticsTracking = '';

    /**
     * Should the query string be stripped from the page tracking URL?
     *
     * @var bool
     */
    public $stripQueryString = true;

    /**
     * The field in a Commerce Product Variant that should be used for the category
     *
     * @var string
     */
    public $productCategoryField = '';

    /**
     * The field in a Commerce Product Variant that should be used for the brand
     *
     * @var string
     */
    public $productBrandField = '';

    /**
     * Whether add to cart events should be automatically sent
     *
     * @var bool
     */
    public $autoSendAddToCart = true;

    /**
     * Whether remove from cart events should be automatically sent
     *
     * @var bool
     */
    public $autoSendRemoveFromCart = true;

    /**
     * Whether purchase complete events should be automatically sent
     *
     * @var bool
     */
    public $autoSendPurchaseComplete = true;

    /**
     * Controls whether Instant Analytics will send analytics data.
     *
     * @var bool
     */
    public $sendAnalyticsData = true;

    /**
     * Controls whether Instant Analytics will send analytics data when
     * `devMode` is on.
     *
     * @var bool
     */
    public $sendAnalyticsInDevMode = true;

    /**
     * Controls whether we should filter out bot UserGents.
     *
     * @var bool
     */
    public $filterBotUserAgents = true;

    /**
     * Controls whether we should exclude users logged into an admin account
     * from Analytics tracking.
     *
     * @var bool
     */
    public $adminExclude = false;

    /**
     * Controls whether analytics that blocked from being sent should be logged
     * to storage/logs/web.log These are always logged if `devMode` is on
     *
     * @var bool
     */
    public $logExcludedAnalytics = false;

    /**
     * Contains an array of Craft user group handles to exclude from Analytics
     * tracking.  If there's a match for any of them, analytics data is not
     * sent.
     *
     * @var array
     */
    public $groupExcludes = [
    ];

    /**
     * Contains an array of keys that correspond to $_SERVER[] super-global
     * array keys to test against. Each item in the sub-array is tested against
     * the $_SERVER[] super-global key via RegEx; if there's a match for any of
     * them, analytics data is not sent.  This allows you to filter based on
     * whatever information you want. Reference:
     * http://php.net/manual/en/reserved.variables.server.php RegEx tester:
     * http://regexr.com
     *
     * @var array
     */
    public $serverExcludes = [
        'REMOTE_ADDR' => [
            '/^localhost$|^127(?:\.[0-9]+){0,2}\.[0-9]+$|^(?:0*\:)*?:?0*1$/',
        ],
    ];

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values
     * are valid. Child classes may override this method to declare different
     * validation rules.
     *
     * More info:
     * http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'stripQueryString',
                    'autoSendAddToCart',
                    'autoSendRemoveFromCart',
                    'autoSendPurchaseComplete',
                    'sendAnalyticsData',
                    'sendAnalyticsInDevMode',
                    'filterBotUserAgents',
                    'adminExclude',
                    'logExcludedAnalytics',
                ],
                'boolean'
            ],
            [
                [
                    'googleAnalyticsTracking',
                    'productCategoryField',
                    'productBrandField',
                    'googleAnalyticsTracking',
                ],
                'string'
            ],
            [
                [
                    'groupExcludes',
                    'serverExcludes',
                ],
                ArrayValidator::class
            ],
        ];
    }
}
