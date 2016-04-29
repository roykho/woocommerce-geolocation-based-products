=== WooCommerce Geolocation Based Products ===
Contributors: royho
Tags: woocommerce, products, conditional products, location, geolocation, roy ho
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=TPFCAQV2VEQE2
WordPress requires at least: 3.9.1
Tested up to: 4.2
WooCommerce requires at least: 2.1.12
Stable tag: 1.3.2
Author URI: http://royho.me
Plugin URI: https://wordpress.org/plugins/woocommerce-geolocation-based-products/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A WooCommerce plugin/extension that adds ability for your store to hide products based on visitors geolocation taken from IP address. This product includes GeoLite2 data created by MaxMind, available from http://www.maxmind.com

== Description ==

A WooCommerce plugin/extension that adds ability for your store to hide products based on visitors geolocation taken from IP address.

You can add many different countries in which you want the settings to apply to.  You do this by adding a row and setting the 2 letter country code in which you want to apply to.  For example "US".  Then you set whether you want to hide certain product categories or just products themselves.  You may select more than one for each.

You can also add a region. For example if I want to target anyone that is in the US and in California, I would enter the region code of CA for California.  

Furthermore, you can also filter by city. For example if I want to target anyone that is in the US and California but also in the city of Los Angeles, I would enter the city name Los Angeles in the field.

You can also test out your settings as if you're visiting the site from another country. Enabling the testmode on a particular entry row, your site will hide the products/categories per the rules of that row.  Please note that you will need to use this especially when you're testing this on localhost as that IP may not always be valid.  Be sure to turn off testmode when done testing and ready to go live.

There is no active support for this plugin however you can post your questions to https://wordpress.org/support/plugin/woocommerce-geolocation-based-products. If you want to contribute or want to fill a repeatable bug, please go to https://github.com/roykho/woocommerce-geolocation-based-products

== Installation ==

1. Be sure WooCommerce plugin is already installed.
2. Upload the folder `woocommerce-geolocation-based-products` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Click on settings link to get to the settings page or get to the settings page by clicking on Geolocation link under WooCommerce Products Menu.

You can add many different countries in which you want the settings to apply to. You do this by adding a row and setting the 2 letter country code in which you want to apply to.  For example "US".  Then you set whether you want to hide certain product categories or just products themselves.  You may select more than one for each.

You can also add a region.  For example if I want to target anyone that is in the US and in California, I would enter the region code of CA for California.  

Furthermore, you can also filter by city.  For example if I want to target anyone that is in the US and California but also in the city of Los Angeles, I would enter the city name Los Angeles in the field.

You can also test out your settings as if you're visiting the site from another country. Enabling the testmode on a particular entry row, your site will hide the products/categories per the rules of that row.  Please note that you will need to use this especially when you're testing this on localhost as that IP may not always be valid.  Be sure to turn off testmode when done testing and ready to go live.

== Frequently Asked Questions ==

= This plugin does not seem to working, anything I set to hide is not hiding. =

This plugin utilizes the IP address of the visitor to obtain location information.  If this IP is somehow blocked or is unknown, there is no way for this plugin to know which country the visitor is from.  In this case, nothing will be hidden.

== Screenshots ==

1. This screen shot shows the options.

== Upgrade Notice ==

= 1.3.2 =
* Fixed - Related, crosssell and upsell products were not hiding

= 1.3.1 =
* Fixed - Suppress error when geoip API returning an error at times
* Fixed - Category widget count sometimes displays a warning when debug is on

== Changelog ==

= 1.3.2 | 09-13-2015 =
* Fixed - Related, crosssell and upsell products were not hiding

= 1.3.1 | 07-11-2015 =
* Fixed - Suppress error when geoip API returning an error at times
* Fixed - Category widget count sometimes displays a warning when debug is on

= 1.3.0 | 06-25-2015 =
* Removed - IP check API from ip-api.com
* Added - IP check API from https://freegeoip.net

= 1.2.0 | 04-30-2015 =
* Fixed - Excluded product categories showing in WooCommerce product category widget
* Fixed - Excluded products showing in WooCommerce products widget ( Fixed from WooCommerce 2.4 )
* Fixed - Product category count was not updated from the excluded products and product categories
* Fixed - Tooltip was no longer popping up after WC 2.3
* Fixed - Hide excluded products and product categories from menus
* Added - POT file

= 1.1.3 | 12-29-2014 =
* Update - Cleaned up code

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
