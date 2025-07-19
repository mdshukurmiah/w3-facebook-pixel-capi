=== W3 Facebook Pixel Server-Side Tracking (CAPI) ===
Contributors: Md Shukur Miah
Tags: facebook, pixel, conversions api, server-side tracking, woocommerce, marketing, analytics
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enable Facebook Pixel server-side tracking using the Conversions API (CAPI) for improved tracking accuracy and reliability.

== Description ==

The Facebook Pixel Server-Side Tracking (CAPI) plugin enables you to send Facebook Pixel events directly from your WordPress server to Meta using the Conversions API. This provides more reliable tracking compared to browser-based tracking, especially in an era of increasing privacy restrictions and ad blockers.

**Key Features:**

* **Server-Side Tracking**: Send events directly from your server to Facebook, bypassing browser limitations
* **WooCommerce Integration**: Automatic tracking of e-commerce events (AddToCart, Purchase, InitiateCheckout)
* **Event Deduplication**: Prevent duplicate events when using both client-side and server-side tracking
* **Privacy Compliant**: Automatically hash sensitive customer data before sending to Facebook
* **Easy Setup**: Simple configuration through WordPress admin interface
* **Debug Mode**: Comprehensive logging for troubleshooting and development
* **Test Connection**: Built-in tool to verify your Conversions API setup

**Supported Events:**

* PageView - Track page visits
* ViewContent - Track product page views (WooCommerce)
* AddToCart - Track when products are added to cart (WooCommerce)
* InitiateCheckout - Track checkout initiation (WooCommerce)
* Purchase - Track completed orders (WooCommerce)

**Why Use Server-Side Tracking?**

* **Improved Data Quality**: Server-side events are not affected by ad blockers or browser restrictions
* **Better Attribution**: More accurate conversion attribution for your Facebook ads
* **Privacy Compliant**: Sensitive data is hashed before transmission
* **Reliable Tracking**: Events are sent regardless of JavaScript execution or page load issues
* **Future-Proof**: Prepared for the cookieless future of web tracking

**Requirements:**

* WordPress 5.0 or higher
* PHP 7.4 or higher
* Facebook Business Manager account
* Facebook Pixel ID
* Conversions API Access Token
* WooCommerce (optional, for e-commerce tracking)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/w3-facebook-pixel-capi` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to Settings > Facebook Pixel CAPI to configure the plugin.
4. Enter your Facebook Pixel ID and Conversions API Access Token.
5. Select which events you want to track.
6. Test your connection to ensure everything is working correctly.

== Frequently Asked Questions ==

= How do I get a Facebook Pixel ID? =

1. Go to Facebook Events Manager (https://business.facebook.com/events_manager)
2. Create a new Pixel or select an existing one
3. Your Pixel ID is displayed in the Pixel details

= How do I generate a Conversions API Access Token? =

1. In Facebook Events Manager, select your Pixel
2. Go to Settings tab
3. Find the Conversions API section
4. Click "Generate access token" under "Set up manually"
5. Follow the instructions to create your token

= Do I need to remove my existing Facebook Pixel code? =

No! This plugin is designed to work alongside your existing client-side Facebook Pixel. The plugin automatically handles event deduplication to prevent duplicate events from being sent to Facebook.

= What data is sent to Facebook? =

The plugin sends standard Facebook Pixel events along with relevant customer data. All sensitive information (email, phone, names, addresses) is automatically hashed using SHA256 before transmission to protect customer privacy.

= Does this work with WooCommerce? =

Yes! The plugin has built-in WooCommerce integration and will automatically track e-commerce events like AddToCart, InitiateCheckout, and Purchase when WooCommerce is active.

= How can I test if the plugin is working? =

Use the "Test Connection" feature in the plugin settings to verify your Conversions API setup. You can also enable Debug Mode to see detailed logs of all API requests and responses.

= Is this plugin GDPR compliant? =

The plugin helps with GDPR compliance by hashing all sensitive customer data before sending it to Facebook. However, you should still ensure you have proper consent mechanisms in place for tracking users.

== Screenshots ==

1. Plugin settings page with configuration options
2. Event settings to choose which events to track
3. Debug information and connection testing
4. WooCommerce integration status

== Changelog ==

= 1.1.0 =
* Added ViewContent event tracking for WooCommerce product pages
* Enhanced brand detection from product attributes and custom fields
* Improved variable product price handling
* Added comprehensive debug logging for ViewContent events
* Updated admin interface to include ViewContent event option

= 1.0.0 =
* Initial release
* Support for PageView, AddToCart, InitiateCheckout, and Purchase events
* WooCommerce integration
* Event deduplication
* Debug mode and logging
* Connection testing
* Privacy-compliant data hashing

== Upgrade Notice ==

= 1.1.0 =
New ViewContent event tracking for product pages! Enable this event in plugin settings to track when users view your WooCommerce products.

= 1.0.0 =
Initial release of the Facebook Pixel Server-Side Tracking plugin.

== Privacy Policy ==

This plugin sends data to Facebook's servers via the Conversions API. The data sent includes:

* Event information (page views, purchases, etc.)
* Hashed customer information (email, phone, names, addresses)
* Technical information (IP address, user agent)
* Product information (for e-commerce events)

All sensitive customer data is hashed using SHA256 before transmission. No plain-text personal information is sent to Facebook.

For more information about Facebook's data handling practices, please review Facebook's Privacy Policy and Data Processing Terms.

== Support ==

For support, please visit the plugin's GitHub repository or contact the plugin author through the WordPress.org plugin directory.

== Contributing ==

This plugin is open source and contributions are welcome! Please visit the GitHub repository to report issues or submit pull requests.

== Credits ==

Developed by Md Shukur Miah
Facebook Conversions API documentation: https://developers.facebook.com/docs/marketing-api/conversions-api/

