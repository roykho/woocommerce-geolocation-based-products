<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// if uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

delete_option( 'wc_geolocation_based_products_settings' );
delete_option( 'wc_geolocation_based_products_test_country' );
wp_clear_scheduled_hook( 'wc_glbp_db_update' );
