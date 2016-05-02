<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Geolocation_Based_Products_Admin {
	private static $_this;

	/**
	 * init
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool
	 */
	public function __construct() {
		self::$_this = $this;
		
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		add_action( 'wp_ajax_wc_geolocation_based_products_search_products_ajax', array( $this, 'search_products' ) );

		add_action( 'wc_geolocation_based_products_admin_save', array( $this, 'admin_settings_save' ) );

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
	 * load admin scripts
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool
	 */
	public function admin_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'geolocation_based_products_admin_script', plugins_url( 'plugin-assets/js/admin-settings' . $suffix . '.js' , dirname( __FILE__ ) ), array( 'jquery', 'select2', 'wc-enhanced-select', 'jquery-tiptip', 'jquery-ui-sortable' ), WC_GEOLOCATION_BASED_PRODUCTS_VERSION, true );

		wp_enqueue_script( 'geolocation_based_products_admin_script' );

		$cats = $this->get_product_categories();

		$localized_vars = array(
			'placeholderSelectCategories' => __( 'Select Product Categories', 'woocommerce-geolocation-based-products' ),
			'placeholderSearchProducts'   => __( 'Search Products by Name', 'woocommerce-geolocation-based-products' ),
			'optionShow'                  => __( 'Show', 'woocommerce-geolocation-based-products' ),
			'optionHide'                  => __( 'Hide', 'woocommerce-geolocation-based-products' ), 
			'categories'                  => json_encode( $cats )
		);
		
		wp_localize_script( 'geolocation_based_products_admin_script', 'wc_geolocation_based_products_local', $localized_vars );
		
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		
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
				wp_die( __( 'Cheatin&#8217; huh?', 'woocommerce-geolocation-based-products' ) );
			}

			$rows = array();

			// loop through the rows
			foreach( $_POST['row'] as $row ) {
				if ( isset( $row['disable'] ) ) {
					$disable = 'yes';
				} else {
					$disable = 'no';
				}

				// sanitize submited data
				$country = isset( $row['country'] ) ? strtoupper( sanitize_text_field( $row['country'] ) ) : '';

				$region = isset( $row['region'] ) ? strtoupper( sanitize_text_field( $row['region'] ) ) : '';

				$city = isset( $row['city'] ) ? strtoupper( sanitize_text_field( $row['city'] ) ) : '';

				$show_hide = isset( $row['show_hide'] ) ? sanitize_text_field( $row['show_hide'] ) : '';

				if ( isset( $row['product_categories'] ) ) {
					$product_categories = is_array( $row['product_categories'] ) ? array_map( 'absint', $row['product_categories'] ) : absint( $row['	product_categories'] );
				} else {
					$product_categories = array();
				}

				if ( isset( $row['products'] ) ) {
					$products = array_filter( array_map( 'intval', explode( ',', $row['products'] ) ) );
				} else {
					$products = array();
				}

				if ( isset( $row['test'] ) ) {
					$test = 'yes';
				} else {
					$test = 'no';
				}

				$rows[] = array(
					'disable'            => $disable,
					'country'            => $country,
					'region'             => $region,
					'city'               => $city, 
					'product_categories' => $product_categories, 
					'products'           => $products,
					'show_hide'          => $show_hide,
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
		<table class="wc-glbp-settings widefat">
			<thead>
				<tr>
					<th><?php echo wc_help_tip( __( 'Sort Rules. You can drag and drop the rules. Rules towards the bottom will supercede rules above.', 'woocommerce-geolocation-based-products' ) ); ?></th>
					<th><?php esc_html_e( 'Remove', 'woocommerce-geolocation-based-products' ); ?></th>

					<th width="10%"><?php esc_html_e( 'Disable', 'woocommerce-geolocation-based-products' ); ?>

						<?php echo wc_help_tip( __( 'Check the box if you want to disable this rule.', 'woocommerce-geolocation-based-products' ) ); ?>
					</th>

					<th width="5%"><?php esc_html_e( 'ISO Country Code', 'woocommerce-geolocation-based-products' ); ?>
						
						<?php echo wc_help_tip( __( 'A 2 letter ISO country code, e.g. US. Leaving blank matches all countries.', 'woocommerce-geolocation-based-products' ) ); ?>
					</th>

					<th width="10%"><?php esc_html_e( 'ISO Region Code', 'woocommerce-geolocation-based-products' ); ?>
						<?php echo wc_help_tip( __( 'A ISO region code for your state or province, e.g. CA for California. Leaving blank matches all regions.', 'woocommerce-geolocation-based-products' ) ); ?>
					</th>

					<th width="18%"><?php esc_html_e( 'City', 'woocommerce-geolocation-based-products' ); ?>

						<?php echo wc_help_tip( __( 'A city name. Leaving blank matches all cities.', 'woocommerce-geolocation-based-products' ) ); ?>
					</th>

					<th width="20%"><?php esc_html_e( 'Product Categories', 'woocommerce-geolocation-based-products' ); ?>

						<?php echo wc_help_tip( __( 'Product Categories to show/hide.', 'woocommerce-geolocation-based-products' ) ); ?>
					</th>

					<th width="20%"><?php esc_html_e( 'Products', 'woocommerce-geolocation-based-products' ); ?>
						
						<?php echo wc_help_tip( __( 'Products to show/hide.', 'woocommerce-geolocation-based-products' ) ); ?>
					</th>

					<th width="20%"><?php esc_html_e( 'Show/Hide', 'woocommerce-geolocation-based-products' ); ?></th>

					<th width="2%"><?php esc_html_e( 'Test Mode', 'woocommerce-geolocation-based-products' ); ?>
						
						<?php echo wc_help_tip( __( 'Check the box to enable test mode for this rule. Leave blank to disable.', 'woocommerce-geolocation-based-products' ) ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th colspan="10">
						<a href="#" class="button wc-glbp-insert-row"><?php esc_html_e( 'Insert row', 'woocommerce-geolocation-based-products' ); ?></a>
						<a href="#" class="button wc-glbp-remove-row"><?php esc_html_e( 'Remove selected row(s)', 'woocommerce-geolocation-based-products' ); ?></a>
					</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
					if ( false === $rows ) {
				?>
						<tr class="entry">
							<td class="wc-glbp-sort"></td>
							<td class="wc-glbp-column-remove-row">
								<input type="checkbox" value="remove" class="wc-glbp-remove-row-cb" />
							</td>

							<td class="wc-glbp-column-disable">
								<input type="checkbox" value="row[0][disable]" class="wc-glbp-disable" />
							</td>

							<td class="wc-glbp-column-country">
								<input type="text" name="row[0][country]" value="" placeholder="*" maxlength="2" class="wc-glbp-country" />
							</td>

							<td class="wc-glbp-column-region">
								<input type="text" name="row[0][region]" value="" placeholder="*" class="wc-glbp-region" />
							</td>

							<td class="wc-glbp-column-city">
								<input type="text" name="row[0][city]" value="" placeholder="*" class="wc-glbp-city" />
							</td>

							<td class="wc-glbp-column-product-categories">
								<select name="row[0][product_categories][]" class="wc-enhanced-select wc-glbp-categories" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select Product Categories', 'woocommerce-geolocation-based-products' ); ?>" style="width: 70%;">
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

							<td class="wc-glbp-column-products">
								<input type="hidden" class="wc-product-search wc-glbp-products" data-multiple="true" name="row[0][products]" style="width: 100%;" data-placeholder="<?php esc_attr_e( 'Search Products by Name', 'woocommerce-geolocation-based-products' ); ?>" data-action="woocommerce_json_search_products" />
							</td>

							<td class="wc-glbp-column-show-hide">
								<select name="row[0][show_hide]" class="wc-glbp-show-hide">
									<option value="hide"><?php esc_html_e( 'Hide', 'woocommerce-geolocation-based-products' ); ?></option>
									<option value="show"><?php esc_html_e( 'Show', 'woocommerce-geolocation-based-products' ); ?></option>
								</select>
							</td>

							<td class="wc-glbp-column-test">
								<input type="checkbox" name="row[0][test]" value="" class="wc-glbp-test" />
							</td>
						</tr>
				<?php
					} else {		
						$row_count = 0;

						foreach ( $rows as $row ) {

							$disable   = ( isset( $row['disable'] ) && ! empty( $row['disable'] ) ) ? $row['disable'] : '';

							$country   = ( isset( $row['country'] ) && ! empty( $row['country'] ) ) ? $row['country'] : '';
							
							$region    = ( isset( $row['region'] ) && ! empty( $row['region'] ) ) ? $row['region'] : '';
							
							$city      = ( isset( $row['city'] ) && ! empty( $row['city'] ) ) ? $row['city'] : '';
							
							$show_hide = ( isset( $row['show_hide'] ) && ! empty( $row['show_hide'] ) ) ? $row['show_hide'] : '';
							
							$test      = ( isset( $row['test'] ) && ! empty( $row['test'] ) ) ? $row['test'] : '';
				?>
							<tr class="entry">
								<td class="wc-glbp-sort"></td>
								<td class="wc-glbp-column-remove-row">
									<input type="checkbox" value="remove" class="wc-glbp-remove-row-cb" />
								</td>

								<td class="wc-glbp-column-disable">
									<input type="checkbox" name="row[<?php echo esc_attr( $row_count ); ?>][disable]" class="wc-glbp-disable" <?php checked( 'yes', $disable ); ?> />
								</td>

								<td class="wc-glbp-column-country">
									<input type="text" name="row[<?php echo esc_attr( $row_count ); ?>][country]" value="<?php echo esc_attr( $country ); ?>" placeholder="*" maxlength="2" class="wc-glbp-country" />
								</td>

								<td class="wc-glbp-column-region">
									<input type="text" name="row[<?php echo esc_attr( $row_count ); ?>][region]" value="<?php echo esc_attr( $region ); ?>" placeholder="*" class="wc-glbp-region" />
								</td>

								<td class="wc-glbp-column-city">
									<input type="text" name="row[<?php echo esc_attr( $row_count ); ?>][city]" value="<?php echo esc_attr( $city ); ?>" placeholder="*" class="wc-glbp-city" />
								</td>

								<td class="wc-glbp-column-product-categories">
									<select name="row[<?php echo esc_attr( $row_count ); ?>][product_categories][]" class="wc-enhanced-select wc-glbp-categories" multiple="multiple" data-placeholder="<?php _e( 'Select Product Categories', 'woocommerce-geolocation-based-products' ); ?>" style="width: 70%;">
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

								<td class="wc-glbp-column-products">
									<input type="hidden" class="wc-product-search wc-glbp-products" data-multiple="true" name="row[<?php echo esc_attr( $row_count ); ?>][products]" style="width: 100%;" data-placeholder="<?php esc_attr_e( 'Search Products by Name', 'woocommerce-geolocation-based-products' ); ?>" data-action="woocommerce_json_search_products" data-selected="<?php
										$product_ids = array_filter( array_map( 'absint', $row['products'] ) );
										$json_ids    = array();

										foreach ( $product_ids as $product_id ) {
											$product = wc_get_product( $product_id );
											if ( is_object( $product ) ) {
												$json_ids[ $product_id ] = wp_kses_post( $product->get_formatted_name() );
											}
										}

										echo esc_attr( json_encode( $json_ids ) );
										?>" value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>" />
								</td>

								<td class="wc-glbp-column-show-hide">
									<select name="row[<?php echo esc_attr( $row_count ); ?>][show_hide]" class="wc-glbp-show-hide">
										<option value="hide" <?php selected( $show_hide, 'hide' ); ?>><?php esc_html_e( 'Hide', 'woocommerce-geolocation-based-products' ); ?></option>
										<option value="show" <?php selected( $show_hide, 'show' ); ?>><?php esc_html_e( 'Show', 'woocommerce-geolocation-based-products' ); ?></option>
									</select>
								</td>

								<td class="wc-glbp-column-test">
									<input type="checkbox" name="row[<?php echo esc_attr( $row_count ); ?>][test]" class="wc-glbp-test" <?php checked( 'yes', $test ); ?> />
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
}

new WC_Geolocation_Based_Products_Admin();
