jQuery( document ).ready( function( $ ) {
	'use strict';

	// create namespace to avoid any possible conflicts
	$.wc_geolocation_based_products_admin = {
		runTipTip: function() {
			$( '.help_tip' ).tipTip({
				'attribute' : 'data-tip',
				'fadeIn' : 50,
				'fadeOut' : 50,
				'delay' : 200
			});
		},

		runChosen: function() {
			// ajax chosen product search
			$( 'select.wc-geolocation-based-products-choose-products' ).each( function() {
				$( this ).ajaxChosen({
					method:            'GET',
					url:               wc_geolocation_based_products_local.ajaxURL,
					dataType:          'json',
					afterTypeDelay:    100,
					data:              {
						action:        'wc_geolocation_based_products_search_products_ajax',
						security:      wc_geolocation_based_products_local.ajaxProductSearchNonce
					}
				}, function( data ) {

					var terms = {};

					$.each( data, function ( i, val ) {
						terms[i]  = val;
					});

					return terms;
				}).next( '.chosen-container' ).eq( 0 ).css( 'width', '100%' );

				$( this ).next( '.chosen-container' ).find( '.search-field input' ).css( 'width', 'auto' );
			});
			
			// chosen product category
			$( 'select.wc-geolocation-based-products-choose-product-categories' ).each( function() {
				$( this ).chosen({
					allow_single_deselect: 'true',
					width: '100%'
				});
			});
		},

		reInitRows: function() {
			// re-init row positions
			$( 'table.wc-geolocation-based-products-settings' ).find( 'tr.entry' ).each( function( row ) {
				// reinit row position country 
				var rowPos = String( $( 'input.wc-geolocation-based-products-country', this ).prop( 'name' ) ),
					replacedName = rowPos.replace( /row\[\d+\]/, 'row[' + row + ']' );

				$( 'input.wc-geolocation-based-products-country', this ).prop( 'name', replacedName );

				// reinit row position region
				rowPos = String( $( 'input.wc-geolocation-based-products-region', this ).prop( 'name' ) );
				replacedName = rowPos.replace( /row\[\d+\]/, 'row[' + row + ']' );

				$( 'input.wc-geolocation-based-products-region', this ).prop( 'name', replacedName );

				// reinit row position city
				rowPos = String( $( 'input.wc-geolocation-based-products-city', this ).prop( 'name' ) );
				replacedName = rowPos.replace( /row\[\d+\]/, 'row[' + row + ']' );

				$( 'input.wc-geolocation-based-products-city', this ).prop( 'name', replacedName );

				// reinit row position products
				rowPos = String( $( 'select.wc-geolocation-based-products-choose-products', this ).prop( 'name' ) );
				replacedName = rowPos.replace( /row\[\d+\]/, 'row[' + row + ']' );

				$( 'select.wc-geolocation-based-products-choose-products', this ).prop( 'name', replacedName );

				// reinit row position product categories
				rowPos = String( $( 'select.wc-geolocation-based-products-choose-product-categories', this ).prop( 'name' ) );
				replacedName = rowPos.replace( /row\[\d+\]/, 'row[' + row + ']' );

				$( 'select.wc-geolocation-based-products-choose-product-categories', this ).prop( 'name', replacedName );

				// reinit row position test
				rowPos = String( $( 'input.wc-geolocation-based-products-test', this ).prop( 'name' ) );
				replacedName = rowPos.replace( /row\[\d+\]/, 'row[' + row + ']' );

				$( 'input.wc-geolocation-based-products-test', this ).prop( 'name', replacedName );
			});

			$.wc_geolocation_based_products_admin.runTipTip();
		},

		init: function() {
			$.wc_geolocation_based_products_admin.runChosen();

			// add row
			$( 'table.wc-geolocation-based-products-settings' ).on( 'click', '.insert-row', function( e ) {
				e.preventDefault();

				var table = $( this ).parents( 'table.wc-geolocation-based-products-settings' ),
					clonedRow = table.find( 'tr.entry' ).eq( 0 ).clone();

				// remove chosen and reapply
				clonedRow.find( 'select.wc-geolocation-based-products-choose-products' ).css( 'display', 'block' ).val( '' ).removeClass( 'chzn-done' ).next( '.chosen-container' ).remove();

				// remove chosen and reapply
				clonedRow.find( 'select.wc-geolocation-based-products-choose-product-categories' ).css( 'display', 'block' ).val( '' ).removeClass( 'chzn-done' ).next( '.chosen-container' ).remove();

				// remove any checkmark
				clonedRow.find( '.wc-geolocation-based-products-remove-row' ).prop( 'checked', false );
				clonedRow.find( '.wc-geolocation-based-products-test' ).prop( 'checked', false );

				// remove country field value
				clonedRow.find( '.wc-geolocation-based-products-country' ).val( '' );

				// remove region field value
				clonedRow.find( '.wc-geolocation-based-products-region' ).val( '' );

				// remove city field value
				clonedRow.find( '.wc-geolocation-based-products-city' ).val( '' );

				// append row to table
				table.find( 'tbody' ).append( clonedRow );

				// re-init chosen
				$.wc_geolocation_based_products_admin.runChosen();

				// re-init row positions
				$.wc_geolocation_based_products_admin.reInitRows();

				// resets the selected values
				clonedRow.find( 'select.wc-geolocation-based-products-choose-product-categories option' ).prop( 'selected', false ).parents( 'select.wc-geolocation-based-products-choose-product-categories' ).trigger( 'chosen:updated' );

				clonedRow.find( 'select.wc-geolocation-based-products-choose-products option' ).prop( 'selected', false ).parents( 'select.wc-geolocation-based-products-choose-products' ).trigger( 'chosen:updated' );
			});

			// remove row
			$( 'table.wc-geolocation-based-products-settings' ).on( 'click', '.remove-row', function( e ) {
				e.preventDefault();

				var table = $( this ).parents( 'table.wc-geolocation-based-products-settings' );

				table.find( '.wc-geolocation-based-products-remove-row:checked' ).each( function() {
					// if last row, don't delete just remove options
					if ( table.find( 'tr.entry' ).length === 1 ) {
						// remove checkmark
						table.find( '.wc-geolocation-based-products-remove-row' ).prop( 'checked', false );
						table.find( '.wc-geolocation-based-products-test' ).prop( 'checked', false );

						// remove country field value
						table.find( '.wc-geolocation-based-products-country' ).val( '' );

						// remove region field value
						table.find( '.wc-geolocation-based-products-region' ).val( '' );

						// remove city field value
						table.find( '.wc-geolocation-based-products-city' ).val( '' );

						// reset select options
						table.find( 'select.wc-geolocation-based-products-choose-products' ).val( '' ).trigger( 'chosen:updated' );

						// reset select options
						table.find( 'select.wc-geolocation-based-products-choose-product-categories' ).val( '' ).trigger( 'chosen:updated' );
					} else {
						$( this ).parents( 'tr.entry' ).eq( 0 ).remove();
					}
				});

				// re-init row positions
				$.wc_geolocation_based_products_admin.reInitRows();
			});

			// test checkbox toggles
			$( 'table.wc-geolocation-based-products-settings' ).on( 'click', '.wc-geolocation-based-products-test', function() {
				// remove checkmark from all
				$( this ).parents( 'table.wc-geolocation-based-products-settings' ).find( '.wc-geolocation-based-products-test' ).not( this ).prop( 'checked', false );

				return true;
			});

			$.wc_geolocation_based_products_admin.runTipTip();
		}
	}; // close namespace
	
	// run init
	$.wc_geolocation_based_products_admin.init();
// end document ready
});	