=== WooCommerce Geolocation Based Products ===
Contributors: royho
Tags: woocommerce, products, conditional products, location, geolocation, roy ho
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=TPFCAQV2VEQE2
WordPress requires at least: 3.9.1
Tested up to: 4.9
WooCommerce requires at least: 2.5.0
Stable tag: 1.5.3
Author URI: https://royho.me
Plugin URI: https://wordpress.org/plugins/woocommerce-geolocation-based-products/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A WooCommerce plugin/extension that adds ability for your store to show/hide products based on visitors geolocation. 

== Description ==

This product includes GeoLite2 data created by MaxMind, available from http://www.maxmind.com

A WooCommerce plugin/extension that adds ability for your store to show/hide products based on visitors geolocation. 

You can add many different countries in which you want the settings to apply to. You do this by adding a rule and setting the 2 letter ISO country code in which you want to apply to. For example "US". You then can set which products and categories to show/hide.  You may select more than one for each rule.

You can also add a region. For example if you want to target anyone that is in the US and in California, you would enter the region code of CA for California.  

Furthermore, you can also filter by city. For example if you want to target anyone that is in the US and California but also in the city of Los Angeles, you would enter the city name Los Angeles in the field.

You can also test out your settings as if you're visiting the site from another country. Enabling the testmode on a particular rule, your site will hide/show the products/categories per the rules of that row. Please note that you will need to use this especially when you're testing this on localhost as that IP may not always be valid. Be sure to turn off testmode when done testing and ready to go live.

There is no active support for this plugin however you can post your questions to https://wordpress.org/support/plugin/woocommerce-geolocation-based-products. 

If you want to contribute or want to file a repeatable bug, please go to https://github.com/roykho/woocommerce-geolocation-based-products

== Installation ==

= Minimum Requirements =

* WordPress 3.9.1 or greater
* PHP version 5.4.0 or greater
* WooCommerce 2.5.0 or greater

1. Be sure WooCommerce plugin is already installed.
2. Upload the folder `woocommerce-geolocation-based-products` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Click on settings link to get to the settings page or get to the settings page by clicking on Geolocation link under WooCommerce Products Menu.

= Note =

Rules that are towards the bottom will supercede the rules above it. You can drag and drop the rules to rearrange the order.

You can test each individual rule by checking the "test" checkbox. This will simulate your current location to match what is set for that rule.

This plugin utilizes the MaxMind GeoIP API and thus will store a copy of the database on your site. This makes for a better experience for users. The API will download the database once a week to refresh any updated geolocation data.

== Frequently Asked Questions ==

= This plugin does not seem to working =

This plugin utilizes the IP address of the visitor to obtain location information. If this IP is somehow blocked or is unknown, there is no way for this plugin to know which country the visitor is from. Also this plugin may not show expected results if you have a caching plugin.

= Where do I get support? =

You can post your support question here https://wordpress.org/support/plugin/woocommerce-geolocation-based-products

= How can I contribute or file a repeatable bug? =

You can post a GitHub issue here https://github.com/roykho/woocommerce-geolocation-based-products

== Screenshots ==

1. This screen shot shows the options.

== Upgrade Notice ==

= 1.5.3 =
* Fix - Undefined index in product category widget.
* Fix - WC 30 Compatibility.

== Changelog ==

= 1.5.4 =
* Fix - Filter rules not working product filter widgets.
* New - Filter to change the db path "woocommerce_geolocation_local_city_db_path".
* New - Filter to change the db URL "woocommerce_geolocation_database".

= 1.5.3 =
* Fix - Undefined index in product category widget.
* Fix - WC 30 Compatibility.

= 1.5.2 | 06-28-2016 =
* Fix - Test mode not working.

= 1.5.1 | 06-01-2016 =
* Tweak - Performance.
* Add - Show/hide ability when using featured products shortcode.

= 1.5.0 | 05-02-2016 =
* Add - Ability to show and hide products and categories by location

= 1.4.1 | 04-30-2016 =
* Update - Missing Vendor folder when uploading to repo

= 1.4.0 | 04-29-2016 =
* Update - Removed freegeoip infavor of MaxMind GeoIP
* Update - Removed Chosen for Select2

= 1.3.2 | 09-13-2015 =
* Fix - Related, crosssell and upsell products were not hiding

= 1.3.1 | 07-11-2015 =
* Fix - Suppress error when geoip API returning an error at times
* Fix - Category widget count sometimes displays a warning when debug is on

= 1.3.0 | 06-25-2015 =
* Removed - IP check API from ip-api.com
* Added - IP check API from https://freegeoip.net

= 1.2.0 | 04-30-2015 =
* Fix - Excluded product categories showing in WooCommerce product category widget
* Fix - Excluded products showing in WooCommerce products widget ( Fixed from WooCommerce 2.4 )
* Fix - Product category count was not updated from the excluded products and product categories
* Fix - Tooltip was no longer popping up after WC 2.3
* Fix - Hide excluded products and product categories from menus
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
