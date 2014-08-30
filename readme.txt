=== WooCommerce Geolocation Based Products ===
Contributors: splashingpixels.com
Tags: woocommerce, products, conditional products, location, geolocation, splashing pixels, roy ho
WordPress requires at least: 3.9.1
Tested up to: 3.9.1
WooCommerce requires at least: 2.1.12
Stable tag: 1.1.2
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

You can add many different countries in which you want the settings to apply to.  You do this by adding a row and setting the 2 letter country code in which you want to apply to.  For example "US".  Then you set whether you want to hide certain product categories or just products themselves.  You may select more than one for each.

You can also add a region.  For example if I want to target anyone that is in the US and in California, I would enter the region code of CA for California.  

Furthermore, you can also filter by city.  For example if I want to target anyone that is in the US and California but also in the city of Los Angeles, I would enter the city name Los Angeles in the field.

You can also test out your settings as if you're visiting the site from another country.  Enabling the testmode on a particular entry row, your site will hide/show the products/categories per the rules of that row.  Please note that you will need to use this especially when you're testing this on localhost as that IP will not be valid.  Be sure to turn off testmode when done testing and ready to go live.

== Frequently Asked Questions ==

= This plugin does not seem to working, anything I set to hide is not hiding. =

This plugin utilizes the IP address of the visitor to obtain location information.  If this IP is somehow blocked or is unknown, there is no way for this plugin to know which country the visitor is from.  In this case, nothing will be hidden.

== Screenshots ==

1. This screen shot shows the options.

== Changelog ==

= 1.1.2 | 9-02-2014 =
* Added - Instance variable for instance targetting

= 1.1.1 | 8-27-2014 =
* Added - missing dependency files to check WC active

= 1.1.0 | 8-2-2014 =
* Added - Ability to geolocate by region
* Added - Ability to geolocate by city
* Update - Test mode is now against each individual row of settings

= 1.0.0 =
Release