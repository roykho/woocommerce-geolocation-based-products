jQuery( document ).ready( function( $ ) {
	'use strict';

	// create namespace to avoid any possible conflicts
	$.wc_geolocation_based_products_admin = {
		runTipTip: function() {
			// Remove any lingering tooltips
			$( '#tiptip_holder' ).removeAttr( 'style' );
			$( '#tiptip_arrow' ).removeAttr( 'style' );
			$( '.woocommerce-help-tip' ).tipTip({
				'attribute': 'data-tip',
				'fadeIn': 50,
				'fadeOut': 50,
				'delay': 200
			});
		},

		reInitRows: function() {
			// re-init row positions
			$( 'table.wc-glbp-settings' ).find( 'tr.entry' ).each( function( row ) {
				// reinit row position country 
				var rowPos = String( $( 'input.wc-glbp-country', this ).prop( 'name' ) ),
					replacedName = rowPos.replace( /row\[\d+\]/, 'row[' + row + ']' );

				$( 'input.wc-glbp-country', this ).prop( 'name', replacedName );

				// reinit row position region
				rowPos = String( $( 'input.wc-glbp-region', this ).prop( 'name' ) );
				replacedName = rowPos.replace( /row\[\d+\]/, 'row[' + row + ']' );

				$( 'input.wc-glbp-region', this ).prop( 'name', replacedName );

				// reinit row position city
				rowPos = String( $( 'input.wc-glbp-city', this ).prop( 'name' ) );
				replacedName = rowPos.replace( /row\[\d+\]/, 'row[' + row + ']' );

				$( 'input.wc-glbp-city', this ).prop( 'name', replacedName );

				// reinit row position products
				rowPos = String( $( 'input.wc-glbp-products', this ).prop( 'name' ) );
				replacedName = rowPos.replace( /row\[\d+\]/, 'row[' + row + ']' );

				$( 'input.wc-glbp-products', this ).prop( 'name', replacedName );

				// reinit row position product categories
				rowPos = String( $( 'select.wc-glbp-categories', this ).prop( 'name' ) );
				replacedName = rowPos.replace( /row\[\d+\]/, 'row[' + row + ']' );

				$( 'select.wc-glbp-categories', this ).prop( 'name', replacedName );

				// reinit row position test
				rowPos = String( $( 'input.wc-glbp-test', this ).prop( 'name' ) );
				replacedName = rowPos.replace( /row\[\d+\]/, 'row[' + row + ']' );

				$( 'input.wc-glbp-test', this ).prop( 'name', replacedName );
			});
		},

		init: function() {
			// add row
			$( 'table.wc-glbp-settings' ).on( 'click', '.wc-glbp-insert-row', function( e ) {
				e.preventDefault();

				var table = $( this ).parents( 'table.wc-glbp-settings' ),
					row = '';

				row += '<tr class="entry">';

				row += '<td class="wc-glbp-column-remove-row">';
				row += '<input type="checkbox" value="remove" class="wc-glbp-remove-row-cb" />';
				row += '</td>';

				row += '<td class="wc-glbp-column-country" width="5%">';
				row += '<input type="text" name="row[0][country]" value="" placeholder="' + wc_geolocation_based_products_local.placeholderCountry + '" maxlength="2" class="wc-glbp-country" />';
				row += '</td>';

				row += '<td class="wc-glbp-column-region" width="10%">';
				row += '<input type="text" name="row[0][region]" value="" placeholder="' + wc_geolocation_based_products_local.placeholderRegion + '" class="wc-glbp-region" />';
				row += '</td>';

				row += '<td class="wc-glbp-column-city" width="18%">';
				row += '<input type="text" name="row[0][city]" value="" placeholder="' + wc_geolocation_based_products_local.placeholderCity + '" class="wc-glbp-city" />';
				row += '</td>';

				row += '<td class="wc-glbp-column-product-categories" width="30%">';
				row += '<select name="row[0][product_categories][]" class="wc-enhanced-select wc-glbp-categories" multiple="multiple" data-placeholder="' + wc_geolocation_based_products_local.placeholderSelectCategories + '" style="width: 50%;">';
				row += '<option value=""></option>';
				
				var categories = $.parseJSON( wc_geolocation_based_products_local.categories );
				
				if ( categories.length ) {
					$( categories ).each( function( index, element ) {
						row += '<option value="' + element.term_id + '">' + element.name + '</option>';
					});
				}

				row += '</select>';
				row += '</td>';

				row += '<td class="wc-glbp-column-products" width="30%">';
				row += '<input type="hidden" class="wc-product-search wc-glbp-products" data-multiple="true" name="row[0][products]" style="width: 50%;" data-placeholder="' + wc_geolocation_based_products_local.placeholderSearchProducts + '" data-action="woocommerce_json_search_products" />';
				row += '</td>';

				row += '<td class="wc-glbp-column-test" width="2%">';
				row += '<input type="checkbox" name="row[0][test]" value="" class="wc-glbp-test" />';
				row += '</td>';
				row += '</tr>';

				// append row to table
				table.find( 'tbody' ).append( row );

				// re-init select2
				$( document.body ).trigger( 'wc-enhanced-select-init' );

				// re-init row positions
				$.wc_geolocation_based_products_admin.reInitRows();
			});

			// remove row
			$( 'table.wc-glbp-settings' ).on( 'click', '.wc-glbp-remove-row', function( e ) {
				e.preventDefault();

				var table = $( this ).parents( 'table.wc-glbp-settings' );

				table.find( '.wc-glbp-remove-row-cb:checked' ).each( function() {
					// if last row, don't delete just remove options
					if ( table.find( 'tr.entry' ).length === 1 ) {
						// remove checkmark
						table.find( '.wc-glbp-remove-row-cb' ).prop( 'checked', false );
						table.find( '.wc-glbp-test' ).prop( 'checked', false );

						// remove country field value
						table.find( '.wc-glbp-country' ).val( '' );

						// remove region field value
						table.find( '.wc-glbp-region' ).val( '' );

						// remove city field value
						table.find( '.wc-glbp-city' ).val( '' );

						// reset select options
						table.find( 'select.wc-product-search' ).select2( 'val', '' );

						// reset select options
						table.find( 'select.wc-enhanced-select' ).select2( 'val', '' );
					} else {
						$( this ).parents( 'tr.entry' ).eq( 0 ).remove();
					}
				});

				// re-init row positions
				$.wc_geolocation_based_products_admin.reInitRows();
			});

			// test checkbox toggles
			$( 'table.wc-glbp-settings' ).on( 'click', '.wc-glbp-test', function() {
				// remove checkmark from all
				$( this ).parents( 'table.wc-glbp-settings' ).find( '.wc-glbp-test' ).not( this ).prop( 'checked', false );

				return true;
			});

			$.wc_geolocation_based_products_admin.runTipTip();
		}
	}; // close namespace
	
	// run init
	$.wc_geolocation_based_products_admin.init();
// end document ready
});
