# Configuring Instant Analytics

Once you have installed Instant Analytics, you’ll see a welcome screen.  Click on **Get Started** to configure Instant Analytics:

* **Google Analytics Tracking ID:** Enter your Google Analytics Tracking ID here. Only enter the ID, for example: UA-XXXXXX-XX, not the entire script code.
* **Auto Send PageViews:** If this setting is on, a PageView will automatically be sent to Google after a every page is rendered. If it is off, you’ll need to send it manually using `{% hook 'iaSendPageView' %}`
* **Strip Query String from PageView URLs:** If this setting is on, the query string will be stripped from PageView URLs before being sent to Google Analytics.  For example: `/some/path?token=1235312` would be sent as just `/some/path`
* **Auto Send "Add To Cart" Events:** If this setting is on, Google Analytics Enhanced Ecommerce events are automatically sent when an item is added to your Craft Commerce cart.
* **Auto Send "Remove From Cart" Events:** If this setting is on, Google Analytics Enhanced Ecommerce events are automatically sent when an item is removed from your Craft Commerce cart.
* **Auto Send "Purchase Complete" Events:** If this setting is on, Google Analytics Enhanced Ecommerce events are automatically sent a purchase is completed.
* **Commerce Product Category Field:** Choose the field in your Product or Variant field layout that should be used for the product’s Category field for Google Analytics Enhanced Ecommerce
* **Commerce Product Brand Field** Choose the field in your Product or Variant field layout that should be used for the product’s Brand field for Google Analytics Enhanced Ecommerce

If you have the [SEOmatic](https://github.com/nystudio107/seomatic) plugin installed, Instant Analytics will automatically grab your **Google Analytics Tracking ID:** from it.

**NOTE:** Instant Analytics will work with the traditional Google Analytics Tracking Code JavaScript tag; it’s not an either/or, they can coexist.  Instant Analytics is just a different way to send the same data to Google Analytics.

However, to prevent duplicate data from being sent, if you use Instant Analytics to send PageView data, you should turn off the JavaScript sending PageViews automatically by:

* In [SEOmatic](https://github.com/nystudio107/seomatic) turn off **Automatically send Google Analytics PageView**
* If you don’t use SEOmatic, remove the line `ga('send', 'pageview');` from your Google Analytics Tracking Code JavaScript tag

Then you can still use the `ga()` calls to send events to Google Analytics.  Or, if you don’t send events via Javascript, you can just remove the Google Analytics tag/JavaScript from your page entirely.

In addition, to ensure that the data is tracked under a uniform `usersession` in the Google Analytics backend, you should have the **Require GA Cookie clientId** setting **on** (which is the default). This causes Instant Analytics to not send any analytics data unless it has received a `clientId` from the frontend GA cookie.

## Customizing via the config.php file

Instant Analytics has a number of other configuration options that can be customized on a per-environment basis via the `config.php` file.  Don’t edit this file, instead copy it to `craft/config` as `instantanalytics.php` (rename it) and make your changes there.

Brought to you by [nystudio107](http://nystudio107.com)
