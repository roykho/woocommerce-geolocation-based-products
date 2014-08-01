<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Geolocation_Based_Products_Admin {
	/**
	 * init
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		add_action( 'wp_ajax_wc_geolocation_based_products_search_products_ajax', array( $this, 'search_products' ) );

		add_action( 'wc_geolocation_based_products_admin_save', array( $this, 'admin_settings_save' ) );

		return true;
	}

	/**
	 * load admin scripts
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool
	 */
	public function admin_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'geolocation_based_products_admin_script', plugins_url( 'plugin-assets/js/admin-settings' . $suffix . '.js' , dirname( __FILE__ ) ), array( 'jquery', 'ajax-chosen', 'chosen' ), '', true );

		wp_enqueue_script( 'geolocation_based_products_admin_script' );

		$localized_vars = array(
			'ajaxURL'                   => admin_url( 'admin-ajax.php' ),
			'ajaxProductSearchNonce'    => wp_create_nonce( '_wc_geolocation_based_products_search_products_nonce' )
		);
		
		wp_localize_script( 'geolocation_based_products_admin_script', 'wc_geolocation_based_products_local', $localized_vars );

		wp_enqueue_style( 'geolocation_based_products_admin_style', plugins_url( 'plugin-assets/css/admin.css', dirname( __FILE__ ) ) );

		return true;
	}

	/**
	 * add the admin menu
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool
	 */
	public function add_admin_menu() {
		$cap = apply_filters( 'wc_geolocation_based_products_menu_cap', 'edit_products' );

		$page = add_submenu_page( 'edit.php?post_type=product', __( 'Geolocation Products', 'woocommerce-geolocation-based-products' ), __( 'Geolocation', 'woocommerce-geolocation-based-products' ), $cap, 'geolocation_products', array( $this, 'admin_menu_page' ) );

		// conditional loads the scripts only on this page
		add_action( 'load-' . $page, array( $this, 'admin_scripts' ) );

		return true;
	}

	/**
	 * admin settings save
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool
	 */
	public function admin_settings_save() {
		if ( isset( $_POST ) && ! empty( $_POST['wc_geolocation_based_products_save_admin_settings_nonce'] ) && ! empty( $_POST['row'] ) ) {
			// bail if security fails
			if ( false === wp_verify_nonce( $_POST['wc_geolocation_based_products_save_admin_settings_nonce'], 'wc_geolocation_based_products_save_admin_settings' ) ) {
				die( 'error' );
			}

			$rows = array();

			// loop through the rows
			foreach( $_POST['row'] as $row ) {
	
				// sanitize submited data
				$countrys = isset( $row['countrys'] ) ? strtoupper( sanitize_text_field( $row['countrys'] ) ) : '';

				$regions = isset( $row['regions'] ) ? strtoupper( sanitize_text_field( $row['regions'] ) ) : '';

				$cities = isset( $row['cities'] ) ? strtoupper( sanitize_text_field( $row['cities'] ) ) : '';

				if ( isset( $row['product_categories'] ) ) {
					$product_categories = is_array( $row['product_categories'] ) ? array_map( 'absint', $row['product_categories'] ) : absint( $row['	product_categories'] );
				} else {
					$product_categories = array();
				}

				if ( isset( $row['products'] ) ) {
					$products = is_array( $row['products'] ) ? array_map( 'absint', $row['products'] ) : absint( $row['products'] );
				} else {
					$products = array();
				}

				if ( isset( $row['test'] ) ) {
					$test = is_array( $row['test'] ) ? array_map( 'sanitize_text_field', $row['test'] ) : sanitize_text_field( $row['test'] );
				} else {
					$test = array();
				}

				$rows[] = array( 
					'countrys'           => $countrys,
					'regions'            => $regions,
					'cities'             => $cities, 
					'product_categories' => $product_categories, 
					'products'           => $products,
					'test'               => $test
				);
			}

			// update options
			update_option( 'wc_geolocation_based_products_settings', $rows );
		}

		return true;
	}

	/**
	 * admin settings field
	 *
	 * @access public
	 * @since 1.0.0
	 * @return html $field
	 */
	public function admin_settings_field() {
		do_action( 'wc_geolocation_based_products_admin_save' );

		$rows = get_option( 'wc_geolocation_based_products_settings', false );

		$cats = $this->get_product_categories();
		?>
		<table class="wc-geolocation-based-products-settings widefat">
			<thead>
				<tr>
					<th><?php _e( 'Remove', 'woocommerce-geolocation-based-products' ); ?></th>

					<th width="5%"><?php _e( 'Country Code', 'woocommerce-geolocation-based-products' ); ?>&nbsp;<span class="tips" data-tip="<?php _e( 'A 2 letter country code, e.g. US.  This is required. Leave blank to disable.', 'woocommerce-geolocation-based-products' ); ?>">[?]</span></th>

					<th width="10%"><?php _e( 'Region Code', 'woocommerce-geolocation-based-products' ); ?>&nbsp;<span class="tips" data-tip="<?php _e( 'A region code for your state or province, e.g. CA for California. Leave blank to disable.', 'woocommerce-geolocation-based-products' ); ?>">[?]</span></th>

					<th width="18%"><?php _e( 'City', 'woocommerce-geolocation-based-products' ); ?>&nbsp;<span class="tips" data-tip="<?php _e( 'A city name. Leave blank to disable.', 'woocommerce-geolocation-based-products' ); ?>">[?]</span></th>

					<th width="30%"><?php _e( 'Product Categories to Hide', 'woocommerce-geolocation-based-products' ); ?>&nbsp;<span class="tips" data-tip="<?php _e( 'Select all the product categories in which you want to hide for this country.', 'woocommerce-geolocation-based-products' ); ?>">[?]</span></th>

					<th width="30%"><?php _e( 'Products to Hide', 'woocommerce-geolocation-based-products' ); ?>&nbsp;<span class="tips" data-tip="<?php _e( 'Search for the products in which you want to hide for this country.', 'woocommerce-geolocation-based-products' ); ?>">[?]</span></th>

					<th width="2%"><?php _e( 'Test Mode', 'woocommerce-geolocation-based-products' ); ?>&nbsp;<span class="tips" data-tip="<?php _e( 'Check the box to enable test mode for this rule. Leave blank to disable.', 'woocommerce-geolocation-based-products' ); ?>">[?]</span></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th colspan="10">
						<a href="#" class="button plus insert-row"><?php _e( 'Insert row', 'woocommerce-geolocation-based-products' ); ?></a>
						<a href="#" class="button minus remove-row"><?php _e( 'Remove selected row(s)', 'woocommerce-geolocation-based-products' ); ?></a>
					</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
					if ( false === $rows ) {
				?>
						<tr class="entry">
							<td>
								<input type="checkbox" value="remove" class="wc-geolocation-based-products-remove-row" />
							</td>

							<td class="country" width="5%">
								<input type="text" name="row[0][countrys]" value="" placeholder="<?php esc_attr_e( '2 Letter Country Code', 'woocommerce-geolocation-based-products' ); ?>" maxlength="2" class="wc-geolocation-based-products-country" />
							</td>

							<td class="region" width="10%">
								<input type="text" name="row[0][regions]" value="" placeholder="<?php esc_attr_e( 'Region Code', 'woocommerce-geolocation-based-products' ); ?>" class="wc-geolocation-based-products-region" />
							</td>

							<td class="city" width="18%">
								<input type="text" name="row[0][cities]" value="" placeholder="<?php esc_attr_e( 'City Name', 'woocommerce-geolocation-based-products' ); ?>" class="wc-geolocation-based-products-city" />
							</td>

							<td class="product-categories" width="30%">
								<select name="row[0][product_categories][]" class="wc-geolocation-based-products-choose-product-categories" multiple="multiple" data-placeholder="<?php _e( 'Select Product Categories', 'woocommerce-geolocation-based-products' ); ?>">
									<option value=""></option>
									<?php
										if ( ! empty( $cats ) ) {
											foreach( $cats as $cat ) {
									?>
												<option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo $cat->name; ?></option>
									<?php
											}
										}
									?>
								</select>
							</td>

							<td class="products" width="30%">
								<select name="row[0][products][]" class="wc-geolocation-based-products-choose-products" multiple="multiple" data-placeholder="<?php _e( 'Search Products by Name', 'woocommerce-geolocation-based-products' ); ?>">
									<option value=""></option>
								</select>
							</td>

							<td class="test" width="2%">
								<input type="checkbox" name="row[0][test]" value="" class="wc-geolocation-based-products-test" />
							</td>
						</tr>
				<?php
					} else {		
						$row_count = 0;

						foreach ( $rows as $row ) {

							$countrys = ( isset( $row['countrys'] ) && ! empty( $row['countrys'] ) ) ? $row['countrys'] : '';

							$regions = ( isset( $row['regions'] ) && ! empty( $row['regions'] ) ) ? $row['regions'] : '';

							$cities = ( isset( $row['cities'] ) && ! empty( $row['cities'] ) ) ? $row['cities'] : '';

							$test = ( isset( $row['test'] ) && ! empty( $row['test'] ) ) ? $row['test'] : '';
				?>
							<tr class="entry">
								<td>
									<input type="checkbox" value="remove" class="wc-geolocation-based-products-remove-row" />
								</td>

								<td class="country" width="5%">
									<input type="text" name="row[<?php echo esc_attr( $row_count ); ?>][countrys]" value="<?php echo esc_attr( $countrys ); ?>" placeholder="<?php esc_attr_e( '2 Letter Country Code', 'woocommerce-geolocation-based-products' ); ?>" maxlength="2" class="wc-geolocation-based-products-country" />
								</td>

								<td class="region" width="10%">
									<input type="text" name="row[<?php echo esc_attr( $row_count ); ?>][regions]" value="<?php echo esc_attr( $regions ); ?>" placeholder="<?php esc_attr_e( 'Region Code', 'woocommerce-geolocation-based-products' ); ?>" class="wc-geolocation-based-products-region" />
								</td>

								<td class="city" width="18%">
									<input type="text" name="row[<?php echo esc_attr( $row_count ); ?>][cities]" value="<?php echo esc_attr( $cities ); ?>" placeholder="<?php esc_attr_e( 'City Name', 'woocommerce-geolocation-based-products' ); ?>" class="wc-geolocation-based-products-city" />
								</td>

								<td class="product-categories" width="30%">
									<select name="row[<?php echo esc_attr( $row_count ); ?>][product_categories][]" class="wc-geolocation-based-products-choose-product-categories" multiple="multiple" data-placeholder="<?php _e( 'Select Product Categories', 'woocommerce-geolocation-based-products' ); ?>">
										<option value=""></option>
										<?php
											if ( ! empty( $cats ) ) {
												foreach( $cats as $cat ) {
													$selected = in_array( $cat->term_id, $row['product_categories'] ) ? 'selected="selected"' : '';
										?>
													<option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php echo $selected; ?>><?php echo $cat->name; ?></option>
										<?php
												}
											}
										?>
									</select>
								</td>

								<td class="products" width="30%">
									<select name="row[<?php echo esc_attr( $row_count ); ?>][products][]" class="wc-geolocation-based-products-choose-products" multiple="multiple" data-placeholder="<?php _e( 'Search Products by Name', 'woocommerce-geolocation-based-products' ); ?>">
										<option value=""></option>
										<?php
											if ( ! empty( $row['products'] ) ) {

												foreach( $row['products'] as $product ) {
													$name = get_post( $product );

													if ( $name !== NULL ) {
										?>
														<option value="<?php echo esc_attr( $product ); ?>" selected="selected"><?php echo $name->post_title; ?></option>
										<?php
													}
												}
											}
										?>
									</select>
								</td>

								<td class="test" width="2%">
									<input type="checkbox" name="row[<?php echo esc_attr( $row_count ); ?>][test]" value="true" class="wc-geolocation-based-products-test" <?php checked( 'true', $test ); ?> />
								</td>
							</tr>
				<?php
							$row_count++;
						}
					}
				?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * return all product categories
	 *
	 * @access public
	 * @since 1.0.0
	 * @return array|object $cats
	 */
	public function get_product_categories() {
		$args = array(
			'hide_empty' => false
		);

		$cats = get_terms( 'product_cat', $args );

		return $cats;
	}

	/**
	 * display admin menu page
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool
	 */
	public function admin_menu_page() {
	?>
		<div class="wrap">

		<h2><?php _e( 'WooCommerce Geolocation Settings', 'woocommerce-geolocation-based-products' ); ?></h2>

		<form action="" method="post">

		<?php $this->admin_settings_field(); ?>
		
		<?php wp_nonce_field( 'wc_geolocation_based_products_save_admin_settings', 'wc_geolocation_based_products_save_admin_settings_nonce' ); ?>

    	<?php submit_button(); ?>

		</form>

		</div><!--close .wrap-->

	<?php

		return true;		
	}

	/**
	 * Search for products and echo json
	 *
	 * @param string $x (default: '')
	 * @param string $post_types (default: array('product'))
	 */
	public function search_products( $x = '', $post_types = array( 'product' ) ) {
		$nonce = $_GET['security'];

		// bail if nonce don't check out
		if ( ! wp_verify_nonce( $nonce, '_wc_geolocation_based_products_search_products_nonce' ) ) {
		     die ( 'error' );	
		 }

		header( 'Content-Type: application/json; charset=utf-8' );

		$term = (string) wc_clean( stripslashes( $_GET['term'] ) );

		if ( empty( $term ) ) {
			die();
		}

		if ( is_numeric( $term ) ) {

			$args = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'post__in'       => array(0, $term),
				'fields'         => 'ids'
			);

			$args2 = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'post_parent'    => $term,
				'fields'         => 'ids'
			);

			$args3 = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => '_sku',
						'value'   => $term,
						'compare' => 'LIKE'
					)
				),
				'fields'         => 'ids'
			);

			$posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ), get_posts( $args3 ) ) );

		} else {

			$args = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				's'              => $term,
				'fields'         => 'ids'
			);

			$args2 = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
					'key'     => '_sku',
					'value'   => $term,
					'compare' => 'LIKE'
					)
				),
				'fields'         => 'ids'
			);

			$posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ) ) );

		}

		$found_products = array();

		if ( $posts ) {
			foreach ( $posts as $post ) {
				$product = get_product( $post );

				$found_products[ $post ] = $product->get_formatted_name();
			}
		}

		$found_products = apply_filters( 'woocommerce_json_search_found_products', $found_products );

		echo json_encode( $found_products );

		die();
	}
}

new WC_Geolocation_Based_Products_Admin();