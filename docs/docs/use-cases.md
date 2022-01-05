# Use Cases

## Simple Page Tracking

If all you want is simple page tracking data sent to Google Analytics, Instant Analytics will do that for you automatically.  Instant Analytics uses the [Google Measurement Protocol](https://developers.google.com/analytics/devguides/collection/protocol/v1/) to send PageViews to your Google Analytics account the same way the Google Analytics Tracking Code JavaScript tag does.

In addition, Instant Analytics injects an `instantAnalytics` object into your templates, which you can manipulate as you see fit, adding Google Analytics properties to be sent along with your PageView.

It has the added benefit of not having to load any JavaScript on the frontend to do this, which results in the following benefits:

* Your pages will render quicker in-browser, with no external resources loaded just for PageView tracking
* Pages will be tracked even if the client’s browser has JavaScript disabled or blocked
* JavaScript errors will not cause Google Analytics data to fail to be collected

## Craft Commerce 2 Integration with Google Enhanced Ecommerce

If you are using Craft Commerce 2, Instant Analytics will recognize this, and automatically send Google Enhanced Ecommerce data for the following actions:

* **Add to Cart** - When someone adds an item from your Craft Commerce store to their cart.  This will include data for the Product or Variant that was added to the cart.
* **Remove from Cart** - When someone removes an item from your Craft Commerce store cart (requires Craft Commerce 2.0.0-beta.x or later).  This will include data for the Product or Variant that was removed from the cart.
* **Purchase** - When someone completes a purchase in your Craft Commerce store.  This will include all of the LineItems that were added to the cart, as well as the Order Reference, Revenue, Tax, Shipping, and Coupon Code used (if any).

Additionally, you can add simple Twig tags to your templates to track Product Impressions, Product Detail Views, and track each step of the Checkout process.  In Google Analytics, you will be able to view detailed information on the sales from your Craft Commerce store, and other useful information such as where customers are abandoning their cart in the Checkout process.

## Tracking Assets/Resources

Instant Analytics lets you track assets/resources that you can’t normally track.  For instance, you may have a collection of PDF documents that you’d like to know when they are viewed.

Using a simple `pageViewTrackingUrl(myAsset.url, myAsset.title)` or `eventTrackingUrl(myAsset.url, myAsset.title, "action", "label", "value")` Twig function, Instant Analytics will generate a public URL that will register a PageView in Google Analytics for the asset/resource, and then display or download the asset/resource.

## Tracking RSS Feeds

Getting actual tracking statistics on RSS feeds can be notoriously difficult, because they are often consumed by clients that are not web browsers, and therefor will not run JavaScript tracking code.

With Instant Analytics, if your RSS feed is a Twig template, accesses will automatically be tracked.  Additionally, you can use the `pageViewTrackingUrl(myAsset.url, myAsset.title)` or `eventTrackingUrl(myAsset.url, myAsset.title, "action", "label", "value")` Twig functions to track individual episode accesses in Google Analytics.

## Custom Tracking via Twig or Plugin

If your needs are more specialized, Instant Analytics will give your Twig templates or plugin full access to an `Analytics` object that allows you to send arbitrary Google Analytics tracking data to Google Analytics.

You can do anything from customized PageViews to complicated Google Enhanced eCommerce tracking, 

Brought to you by [nystudio107](http://nystudio107.com)
