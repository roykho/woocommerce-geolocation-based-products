=== WooCommerce Geolocation Based Products ===
Contributors: splashingpixels.com
Tags: woocommerce, products, conditional products, location, geolocation, splashing pixels, roy ho
WordPress requires at least: 3.9.1
Tested up to: 3.9.1
WooCommerce requires at least: 2.1.12
Stable tag: 1.0.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A WooCommerce plugin/extension that adds ability for your store to show/hide products based on visitors geolocation taken from IP address.

== Description ==

A WooCommerce plugin/extension that adds ability for your store to show/hide products based on visitors geolocation taken from IP address.

== Installation ==

1. Be sure WooCommerce plugin is already installed.
2. Upload the folder `woocommerce-geolocation-based-products` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Click on settings link to get to the settings page or get to the settings page by clicking on Geolocation link under WooCommerce Products Menu.

== Usage ==

You can add many different countries in which you want the settings to apply to.  You do this by adding a row and setting the 2 letter country code in which you want to apply to.  For example "US".  Then you set whether you want to hide certain product categories or just products themselves.  You may select more than one for each.

You can also use the country test simulator to test out your settings as if you're visiting the site from another country.  Please note that you will need to use this especially when you're testing this on localhost as that IP will not be valid.  Be sure to remove this country code when done testing and ready to go live.

== Frequently Asked Questions ==

= This plugin does not seem to working, anything I set to hide is not hiding. =

This plugin utilizes the IP address of the visitor to obtain location information.  If this IP is somehow blocked or is unknown, there is no way for this plugin to know which country the visitor is from.  In this case, nothing will be hidden.

== Screenshots ==

1. This screen shot shows the options.

== Changelog ==

= 1.0.0 =
Release