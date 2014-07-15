<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Geolocation_Based_Products_Frontend {
	/**
	 * init
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool
	 */
	public function __construct() {

		add_action( 'pre_get_posts', array( $this, 'filter_query' ) );

		add_filter( 'wc_geolocation_based_products_user_country', array( $this, 'filter_user_country' ) );

		// hide from category view
		add_filter( 'woocommerce_product_subcategories_args', array( $this, 'hide_categories_view' ) );

		return true;
	}

	/**
	 * gets the user country
	 *
	 * @access public
	 * @since 1.0.0
	 * @return string $user_country
	 */
	public function get_user_country() {
		$ip_data = $this->get_location_data();

		$user_country = apply_filters( 'wc_geolocation_based_products_user_country', $ip_data['countryCode'] );

		return $user_country;
	}

	/**
	 * gets the excluded products and product categories
	 *
	 * @access public
	 * @since 1.0.0
	 * @return string $user_country
	 */
	public function get_exclusion() {
		$exclude = false;

		$product_cats = array();

		$products = array();

		$user_country = $this->get_user_country();

		$rows = get_option( 'wc_geolocation_based_products_settings', false );

		if ( $rows !== false ) {
			// loop through the rows and get data
			foreach( $rows as $row ) {
				// match the country of ip with saved settings
				if ( $row['countrys'] === $user_country ) {
					$exclude = true;

					$product_cats = $row['product_categories'];

					$products = $row['products'];
				}
			}
		}

		if ( $exclude ) {
			return array( 'product_cats' => $product_cats, 'products' => $products );
		} else {
			return false;
		}
	}

	/**
	 * filters the query for output
	 *
	 * @access public
	 * @since 1.0.0
	 * @param object $q | the main query object
	 * @return bool
	 */
	public function filter_query( $q ) {
		// if it is not main query or not a shop page or is admin bail
		if ( ! $q->is_main_query() || is_admin() ) { 
			return;
		}

		$exclusion = $this->get_exclusion();

		if ( $exclusion ) {
			$taxquery = array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'id',
					'terms'    => $exclusion['product_cats'],
					'operator' => 'NOT IN'
				) 
			);

			$q->set( 'tax_query', $taxquery );

			$q->set( 'post__not_in', $exclusion['products'] );
    	} else {
    		return;
    	}
	}

	/**
	 * filter to hide categories from category view
	 *
	 * @access public
	 * @since 1.0.0 
	 * @return bool
	 */
	public function hide_categories_view( $args ) {
		$exclusion = $this->get_exclusion();

		if ( $exclusion ) {	
			$args['exclude'] = implode( ',', $exclusion['product_cats'] );
		}

		return $args;
	}

	/**
	 * filters the user country for testing
	 *
	 * @access public
	 * @since 1.0.0
	 * @param string 
	 * @return string $test_country
	 */
	public function filter_user_country( $ip_country ) {
		$test_country = get_option( 'wc_geolocation_based_products_test_country', false );

		if ( $test_country && ! empty( $test_country ) ) {
			return $test_country;
		} else {
			return $ip_country;
		}
	}

	/**
	 * gets the location data
	 *
	 * attribution goes to ip-api.com for making this public API available
	 *
	 * @access public
	 * @since 1.0.0
	 * @param string $ip | the ip to check
	 * @return array $response_body
	 */
	public function get_location_data( $ip = '' ) {
		if ( empty( $ip ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		$url = 'http://ip-api.com/php/' . $ip;

		$args = apply_filters( 'wc_geolocation_based_products_get_location_args', array(
			'sslverify' => false
		) );
		
		$response = wp_remote_get( $url, $args );

		$response_body = @maybe_unserialize( wp_remote_retrieve_body( $response ) );

		if ( isset( $response_body['status'] ) && $response_body['status'] === 'success' ) {
			return $response_body;
		} else {
			return;
		}
	}
}

new WC_Geolocation_Based_Products_Frontend();