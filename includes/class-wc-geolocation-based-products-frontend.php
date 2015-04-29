<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Geolocation_Based_Products_Frontend {
	private static $_this;

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
		self::$_this = $this;

		add_action( 'pre_get_posts', array( $this, 'filter_query' ) );

		// hide from category view
		add_filter( 'woocommerce_product_subcategories_args', array( $this, 'hide_from_categories_view' ) );

		// hide from category widget
		add_filter( 'woocommerce_product_categories_widget_dropdown_args', array( $this, 'hide_from_categories_view' ) );
		add_filter( 'woocommerce_product_categories_widget_args', array( $this, 'hide_from_categories_view' ) );

		// hide from products widget
		add_filter( 'woocommerce_products_widget_query_args', array( $this, 'hide_from_products_widget' ) );

		$this->location_data = $this->get_location_data();

		$this->exclusion = $this->get_exclusion();

		return true;
	}

	/**
	 * public access to instance object
	 *
	 * @since 1.1.1
	 * @return bool
	 */
	public function get_instance() {
		return self::$_this;
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
	 * Checks if country matches current user's country
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
		}
		
		return false;
	}

	/**
	 * Checks if country isset
	 *
	 * @access public
	 * @since 1.1.0
	 * @param string $saved_country | saved country setting to match
	 * @return bool
	 */
	public function country_isset( $saved_country ) {
		if ( isset( $saved_country ) && ! empty( $saved_country ) ) {
			return true;
		}
		
		return false;
	}

	/**
	 * Checks if region matches current user's region
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
		}
		
		return false;
	}

	/**
	 * Checks if region isset
	 *
	 * @access public
	 * @since 1.1.0
	 * @param string $saved_region | saved region setting to match
	 * @return bool
	 */
	public function region_isset( $saved_region ) {
		if ( isset( $saved_region ) && ! empty( $saved_region ) ) {
			return true;
		}
		
		return false;
	}

	/**
	 * Checks if city matches current user's city
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
		}
		
		return false;
	}

	/**
	 * Checks if city isset
	 *
	 * @access public
	 * @since 1.1.0
	 * @param string $saved_city | saved city setting to match
	 * @return bool
	 */
	public function city_isset( $saved_city ) {
		if ( isset( $saved_city ) && ! empty( $saved_city ) ) {
			return true;
		}
		
		return false;
	}

	/**
	 * Filters the query for output
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

			// single post
			if ( is_single() ) {

				$q->set( 'post__not_in', array_unique( array_merge( $this->exclusion['products'], $this->get_product_ids_from_excluded_cats() ) ) );

				return;
			}

			$q->set( 'post__not_in', $this->exclusion['products'] );
		}

		return;
	}

	/**
	 * Get product ids that are excluded from specific categories
	 * This is used for single product pages as tax_query doesn't work in single post
	 *
	 * @access public
	 * @since 1.1.4
	 * @return array $ids
	 */
	public function get_product_ids_from_excluded_cats() {
		$args = array(
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'id',
					'terms'    => $this->exclusion['product_cats'],
					'operator' => 'IN'
				)
			),
			'posts_per_page' => -1,
			'fields'         => 'ids'
		);

		$ids = new WP_Query( $args );

		wp_reset_postdata();
		
		if ( $ids->found_posts > 0 ) {
			return $ids->posts;
		}

		return array();
	}

	/**
	 * Hide categories from category view
	 *
	 * @access public
	 * @since 1.0.0 
	 * @return bool
	 */
	public function hide_from_categories_view( $args ) {
		if ( $this->exclusion ) {	
			$args['exclude'] = implode( ',', $this->exclusion['product_cats'] );

			// this is expensive allow user to not use as they can choose to hide product counts
			apply_filters( 'woocommerce_geolocation_based_products_update_category_count', add_filter( 'get_terms', array( $this, 'update_category_count' ) ) );
		}

		return $args;
	}

	/**
	 * Update the product category products count
	 *
	 * @access public
	 * @since 1.1.4
	 * @return array $terms
	 */
	public function update_category_count( $terms ) {

		$i = 0;

		foreach( $terms as $term_obj ) {
			$args = array(
				'tax_query' => array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'id',
						'terms'    => $term_obj->term_id,
						'operator' => 'IN'
					)
				),
				'posts_per_page' => -1,
				'post__not_in'   => $this->exclusion['products'],
				'fields'         => 'ids'
			);

			$ids = new WP_Query( $args );

			wp_reset_postdata();

			$terms[ $i ]->count = $ids->found_posts;

			$i++;
		}

		return $terms;
	}

	/**
	 * Hide products from products widget
	 *
	 * @access public
	 * @since 1.0.0 
	 * @param array $args
	 * @return array $args
	 */
	public function hide_from_products_widget( $args ) {
		if ( $this->exclusion ) {
			$args['post__not_in'] = array_unique( array_merge( $this->exclusion['products'], $this->get_product_ids_from_excluded_cats() ) );
		}

		return $args;
	}

	/**
	 * Gets the location data
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
		}

		return;
	}
}

new WC_Geolocation_Based_Products_Frontend();