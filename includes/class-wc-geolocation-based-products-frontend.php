<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Geolocation_Based_Products_Frontend {

	var $location_data;
	var $exclusion;

	/**
	 * init
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool
	 */
	public function __construct() {

		add_action( 'pre_get_posts', array( $this, 'filter_query' ) );

		// hide from category view
		add_filter( 'woocommerce_product_subcategories_args', array( $this, 'hide_categories_view' ) );

		$this->location_data = $this->get_location_data();

		$this->exclusion = $this->get_exclusion();

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
		$user_country = apply_filters( 'wc_geolocation_based_products_user_country', $this->location_data['countryCode'] );

		return $user_country;
	}

	/**
	 * gets the user region
	 *
	 * @access public
	 * @since 1.1.0
	 * @return string $user_region
	 */
	public function get_user_region() {
		$user_region = apply_filters( 'wc_geolocation_based_products_user_region', $this->location_data['region'] );

		return $user_region;
	}

	/**
	 * gets the user city
	 *
	 * @access public
	 * @since 1.1.0
	 * @return string $user_city
	 */
	public function get_user_city() {
		$user_city = apply_filters( 'wc_geolocation_based_products_user_city', $this->location_data['city'] );

		return $user_city;
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

		$rows = get_option( 'wc_geolocation_based_products_settings', false );

		if ( $rows !== false ) {
			// loop through the rows and get data
			foreach( $rows as $row ) {
				$exclude = false;

				// check if test is enabled
				if ( isset( $row['test'] ) && $row['test'] === 'true' ) {
					$exclude = true;

				} else {

					// check if country is set and matched
					if ( $this->country_is_matched( $row['countrys'] ) && ( $this->region_isset( $row['regions'] ) || $this->city_isset( $row['cities'] ) ) ) {
						// if both region and city is set they both have to match
						if ( $this->region_isset( $row['regions'] ) && $this->city_isset( $row['cities'] ) ) {
							if ( $this->region_is_matched( $row['regions'] ) && $this->city_is_matched( $row['cities'] ) ) {
								$exclude = true;
							}
						} elseif ( $this->region_isset( $row['regions'] ) ) {
							if ( $this->region_is_matched( $row['regions'] ) ) {
								$exclude = true;
							}	
						} elseif ( $this->city_isset( $row['cities'] ) ) {
							if ( $this->city_is_matched( $row['cities'] ) ) {
								$exclude = true;
							}
						}
					} elseif ( $this->country_is_matched( $row['countrys'] ) ) {
						$exclude = true;
					}
				}

				if ( $exclude ) {
					$product_cats = $row['product_categories'];

					$products = $row['products'];	

					break; // after a match no need to look at the rest				
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
	 * checks if country matches current user's country
	 *
	 * @access public
	 * @since 1.1.0
	 * @param string $saved_country | saved country setting to match
	 * @return bool
	 */
	public function country_is_matched( $saved_country ) {
		$user_country = $this->location_data['countryCode'];

		if ( isset( $saved_country ) && ! empty( $saved_country ) && strtolower( $saved_country ) === strtolower( $user_country ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * checks if country isset
	 *
	 * @access public
	 * @since 1.1.0
	 * @param string $saved_country | saved country setting to match
	 * @return bool
	 */
	public function country_isset( $saved_country ) {
		if ( isset( $saved_country ) && ! empty( $saved_country ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * checks if region matches current user's region
	 *
	 * @access public
	 * @since 1.1.0
	 * @param string $saved_region | saved region setting to match
	 * @return bool
	 */
	public function region_is_matched( $saved_region ) {
		$user_region = $this->location_data['region'];

		if ( isset( $saved_region ) && ! empty( $saved_region ) && strtolower( $saved_region ) === strtolower( $user_region ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * checks if region isset
	 *
	 * @access public
	 * @since 1.1.0
	 * @param string $saved_region | saved region setting to match
	 * @return bool
	 */
	public function region_isset( $saved_region ) {
		if ( isset( $saved_region ) && ! empty( $saved_region ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * checks if city matches current user's city
	 *
	 * @access public
	 * @since 1.1.0
	 * @param string $saved_city | saved city setting to match
	 * @return bool
	 */
	public function city_is_matched( $saved_city ) {
		$user_city = $this->location_data['city'];

		if ( isset( $saved_city ) && ! empty( $saved_city ) && strtolower( $saved_city ) === strtolower( $user_city ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * checks if city isset
	 *
	 * @access public
	 * @since 1.1.0
	 * @param string $saved_city | saved city setting to match
	 * @return bool
	 */
	public function city_isset( $saved_city ) {
		if ( isset( $saved_city ) && ! empty( $saved_city ) ) {
			return true;
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

		if ( $this->exclusion ) {
			$taxquery = array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'id',
					'terms'    => $this->exclusion['product_cats'],
					'operator' => 'NOT IN'
				) 
			);

			$q->set( 'tax_query', $taxquery );

			$q->set( 'post__not_in', $this->exclusion['products'] );
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
		if ( $this->exclusion ) {	
			$args['exclude'] = implode( ',', $this->exclusion['product_cats'] );
		}

		return $args;
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
		
		$response = wp_remote_post( $url, $args );

		$response_body = @maybe_unserialize( wp_remote_retrieve_body( $response ) );

		if ( isset( $response_body['status'] ) && $response_body['status'] === 'success' ) {
			return $response_body;
		} else {
			return;
		}
	}
}

new WC_Geolocation_Based_Products_Frontend();