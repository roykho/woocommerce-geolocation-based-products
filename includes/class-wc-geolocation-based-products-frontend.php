<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Geolocation_Based_Products_Frontend {
	private static $_this;

	public $location_data;
	public $matches;
	public $geolocate;

	/**
	 * init
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.4.0
	 * @return bool
	 */
	public function __construct( WC_Geolocation_Based_Products_Geolocate $geolocate ) {
		self::$_this = $this;

		$this->geolocate     = $geolocate;
		$this->location_data = $this->get_location_data();
		$this->matches     = $this->get_matches();

		add_action( 'pre_get_posts', array( $this, 'filter_query' ) );

		// hide from category view
		add_filter( 'woocommerce_product_subcategories_args', array( $this, 'hide_from_categories_view' ) );

		// hide from category widget
		add_filter( 'woocommerce_product_categories_widget_dropdown_args', array( $this, 'hide_from_categories_view' ) );
		add_filter( 'woocommerce_product_categories_widget_args', array( $this, 'hide_from_categories_view' ) );

		// hide from products widget
		add_filter( 'woocommerce_products_widget_query_args', array( $this, 'hide_from_products_widget' ) );

		// hide related products
		add_filter( 'woocommerce_related_products_args', array( $this, 'hide_related_products' ) );

		// hide upsell products
		add_filter( 'woocommerce_product_upsell_ids', array( $this, 'hide_upsell_products' ) );

		// hide crossell products
		add_filter( 'woocommerce_product_crosssell_ids', array( $this, 'hide_crosssell_products' ) );

		// hide products from menu
		add_filter( 'wp_nav_menu_objects', array( $this, 'hide_products_from_menu' ), 10, 2 );

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
		$user_country = apply_filters( 'wc_geolocation_based_products_user_country', $this->location_data['country_code'] );

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
		$user_region = apply_filters( 'wc_geolocation_based_products_user_region', $this->location_data['region_code'] );

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
	 * Gets the matched products and product categories based on rules
	 * and current user location.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return array $matches
	 */
	public function get_matches() {
		$match = false;

		$product_cats = array();

		$products = array();

		$rows = get_option( 'wc_geolocation_based_products_settings', false );

		if ( $rows !== false ) {
			// loop through the rows and get data
			foreach( $rows as $row ) {
				$match = false;

				// check if test is enabled
				if ( isset( $row['test'] ) && $row['test'] === 'true' ) {
					$match = true;

				} else {
					// check if country is set and matched
					if ( $this->country_is_matched( $row['country'] ) && ( $this->region_isset( $row['region'] ) || $this->city_isset( $row['city'] ) ) ) {
						// if both region and city is set they both have to match
						if ( $this->region_isset( $row['region'] ) && $this->city_isset( $row['city'] ) ) {
							if ( $this->region_is_matched( $row['region'] ) && $this->city_is_matched( $row['city'] ) ) {
								$match = true;
							}
						} elseif ( $this->region_isset( $row['region'] ) ) {
							if ( $this->region_is_matched( $row['region'] ) ) {
								$match = true;
							}	
						} elseif ( $this->city_isset( $row['city'] ) ) {
							if ( $this->city_is_matched( $row['city'] ) ) {
								$match = true;
							}
						}
					} elseif ( $this->country_is_matched( $row['country'] ) ) {
						$match = true;
					}
				}

				if ( $match ) {
					$product_cats = $row['product_categories'];

					$products = $row['products'];	

					break; // after a match no need to look at the rest				
				}
			}
		}

		if ( $match ) {
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
	public function country_is_matched( $saved_country = null ) {
		$user_country = $this->get_user_country();

		if ( ! empty( $saved_country ) && strtolower( $saved_country ) === strtolower( $user_country ) ) {
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
	public function region_is_matched( $saved_region = null ) {
		$user_region = $this->get_user_region();

		if ( ! empty( $saved_region ) && strtolower( $saved_region ) === strtolower( $user_region ) ) {
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
	public function region_isset( $saved_region = null ) {
		if ( ! empty( $saved_region ) ) {
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
	public function city_is_matched( $saved_city = null ) {
		$user_city = $this->get_user_city();

		if ( ! empty( $saved_city ) && strtolower( $saved_city ) === strtolower( $user_city ) ) {
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
	public function city_isset( $saved_city = null ) {
		if ( ! empty( $saved_city ) ) {
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

		if ( $this->matches ) {

			$taxquery = array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'id',
					'terms'    => $this->matches['product_cats'],
					'operator' => 'NOT IN'
				) 
			);
			
			$product_ids = array_filter( array_map( 'absint', explode( ',', $this->matches['products'] ) ) );

			$q->set( 'tax_query', $taxquery );

			// single post
			if ( is_single() ) {

				$q->set( 'post__not_in', array_unique( array_merge( $product_ids, $this->get_product_ids_from_excluded_cats() ) ) );

				return;
			}

			$q->set( 'post__not_in', $product_ids );
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
					'terms'    => $this->matches['product_cats'],
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
		if ( $this->matches ) {	
			$args['exclude'] = implode( ',', $this->matches['product_cats'] );

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

		if ( $this->matches ) {
			if ( ! is_object( $terms[0] ) ) {
				return $terms;
			}

			$product_ids = array_filter( array_map( 'absint', explode( ',', $this->matches['products'] ) ) );

			foreach( $terms as $term_obj ) {
				$args = array(
					'tax_query' => array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'product_cat',
							'field'    => 'id',
							'terms'    => $term_obj->term_id,
							'operator' => 'IN'
						),
						array(
							'taxonomy' => 'product_cat',
							'field'    => 'id',
							'terms'    => $this->matches['product_cats'],
							'operator' => 'NOT IN'
						)
					),
					'posts_per_page' => -1,
					'post__not_in'   => $product_ids,
					'fields'         => 'ids'
				);

				$ids = new WP_Query( $args );

				wp_reset_postdata();

				$terms[ $i ]->count = $ids->found_posts;

				$i++;
			}
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
		if ( $this->matches ) {
			$product_ids = array_filter( array_map( 'absint', explode( ',', $this->matches['products'] ) ) );

			$args['post__not_in'] = array_unique( array_merge( $product_ids, $this->get_product_ids_from_excluded_cats() ) );
		}

		return $args;
	}

	/**
	 * Hide related products
	 *
	 * @access public
	 * @since 1.3.2
	 * @version 1.3.2
	 * @param array $args
	 * @return array $args
	 */
	public function hide_related_products( $args ) {
		if ( $this->matches ) {
			$product_ids = array_filter( array_map( 'absint', explode( ',', $this->matches['products'] ) ) );

			$excluded_ids = array_unique( array_merge( $product_ids, $this->get_product_ids_from_excluded_cats() ) );

			foreach( $args['post__in'] as $k => $id ) {
				if ( in_array( (int) $id, $excluded_ids ) ) {
					unset( $args['post__in'][ $k ] );
				}
			}

			// set a non existing id so it won't display all products when empty
			if ( empty( $args['post__in'] ) ) {
				$args['post__in'] = array( 0 );
			}
		}

		return $args;
	}

	/**
	 * Hide upsell products
	 *
	 * @access public
	 * @since 1.3.2 
	 * @version 1.3.2
	 * @param array $ids
	 * @return array $ids
	 */
	public function hide_upsell_products( $ids ) {
		if ( $this->matches ) {
			$product_ids = array_filter( array_map( 'absint', explode( ',', $this->matches['products'] ) ) );

			$excluded_ids = array_unique( array_merge( $product_ids, $this->get_product_ids_from_excluded_cats() ) );

			foreach( $ids as $k => $id ) {
				if ( in_array( (int) $id, $excluded_ids ) ) {
					unset( $ids[ $k ] );
				}
			}
		}

		return $ids;
	}

	/**
	 * Hide crosssell products
	 *
	 * @access public
	 * @since 1.3.2 
	 * @version 1.3.2
	 * @param array $ids
	 * @return array $ids
	 */
	public function hide_crosssell_products( $ids ) {
		if ( $this->matches ) {
			$product_ids = array_filter( array_map( 'absint', explode( ',', $this->matches['products'] ) ) );

			$excluded_ids = array_unique( array_merge( $product_ids, $this->get_product_ids_from_excluded_cats() ) );

			foreach( $ids as $k => $id ) {
				if ( in_array( (int) $id, $excluded_ids ) ) {
					unset( $ids[ $k ] );
				}
			}
		}

		return $ids;
	}

	/**
	 * Hide products from menu
	 *
	 * @access public
	 * @since 1.1.4
	 * @param array $atts HTML attributes
	 * @param array $args config of the nav item
	 * @return array $atts
	 */
	public function hide_products_from_menu( $items, $args ) {
		if ( $this->matches ) {
			$product_ids = array_filter( array_map( 'absint', explode( ',', $this->matches['products'] ) ) );

			foreach( $items as $key => $item ) {
				if ( in_array( (int) $item->object_id, $product_ids ) 
					|| in_array( (int) $item->object_id, $this->matches['product_cats'] ) 
					|| in_array( (int) $item->object_id, $this->get_product_ids_from_excluded_cats() )
				) {
					unset( $items[ $key ] );
				}
			}
		}

		return $items;
	}

	/**
	 * Gets the location data
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.4.0
	 * @return array $location
	 */
	public function get_location_data() {
		$logger = new WC_Logger();

		try {
			return $this->geolocate->geolocate_ip();
		} catch( Exception $e ) {
			$logger->add( 'wc_geolocation_based_products', $e->getMessage() );
			return;
		}
	}
}

new WC_Geolocation_Based_Products_Frontend( new WC_Geolocation_Based_Products_Geolocate );
