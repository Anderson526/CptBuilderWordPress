/**
 * CPT Builder — builder admin pages.
 */
( function ( $ ) {
	'use strict';

	function slugify( text, separator ) {
		return text
			.toLowerCase()
			.normalize( 'NFD' )
			.replace( /[\u0300-\u036f]/g, '' )
			.replace( /[^a-z0-9]+/g, separator )
			.replace( new RegExp( '^\\' + separator + '+|\\' + separator + '+$', 'g' ), '' );
	}

	$( function () {
		// Auto-generate key from name while creating.
		var $source = $( '[data-cptb-slug-source]' );
		var $target = $( '[data-cptb-slug-target]' );

		if ( $source.length && $target.length && ! $target.prop( 'readonly' ) ) {
			var touched = '' !== $target.val();
			$target.on( 'input', function () {
				touched = true;
			} );
			$source.on( 'input', function () {
				if ( ! touched ) {
					$target.val( slugify( $( this ).val(), '_' ).substring( 0, $target.attr( 'maxlength' ) || 40 ) );
				}
			} );
		}

		// Confirm configuration deletion.
		$( document ).on( 'click', '.cptb-delete', function ( e ) {
			if ( ! window.confirm( window.cptbAdmin ? window.cptbAdmin.confirmDelete : 'Delete configuration?' ) ) {
				e.preventDefault();
			}
		} );

		// Field group builder: add / remove / toggle rows.
		var $rows = $( '#cptb-field-rows' );

		function toggleTypeOptions( $row ) {
			var type = $row.find( '.cptb-field-type' ).val();
			$row.find( '.cptb-opt' ).hide();
			$row.find( '.cptb-opt-' + type ).show();
		}

		if ( $rows.length ) {
			$rows.find( '.cptb-field-row' ).each( function () {
				toggleTypeOptions( $( this ) );
			} );

			$( '#cptb-add-field' ).on( 'click', function () {
				var index = parseInt( $rows.attr( 'data-next-index' ), 10 ) || 0;
				var html = $( '#cptb-field-row-template' ).html().replace( /__INDEX__/g, index );
				$rows.attr( 'data-next-index', index + 1 ).append( html );
				toggleTypeOptions( $rows.find( '.cptb-field-row' ).last() );
			} );

			$( document ).on( 'click', '.cptb-remove-field', function () {
				$( this ).closest( '.cptb-field-row' ).remove();
			} );

			$( document ).on( 'change', '.cptb-field-type', function () {
				toggleTypeOptions( $( this ).closest( '.cptb-field-row' ) );
			} );

			$( document ).on( 'input', '.cptb-field-label', function () {
				var $row = $( this ).closest( '.cptb-field-row' );
				$row.find( '.cptb-field-row-title' ).text( $( this ).val() || 'New Field' );
				var $key = $row.find( 'input[name$="[key]"]' );
				if ( ! $key.data( 'touched' ) && ! $key.val().length || $key.data( 'auto' ) ) {
					$key.val( slugify( $( this ).val(), '_' ) ).data( 'auto', true );
				}
			} );

			$( document ).on( 'input', '.cptb-field-row input[name$="[key]"]', function () {
				$( this ).data( 'touched', true ).data( 'auto', false );
			} );

			$( document ).on( 'click', '.cptb-field-row-head', function ( e ) {
				if ( ! $( e.target ).is( '.cptb-remove-field' ) ) {
					$( this ).closest( '.cptb-field-row' ).toggleClass( 'cptb-collapsed' );
				}
			} );
		}

		// Copy generated code.
		$( '#cptb-copy-code' ).on( 'click', function () {
			var $btn = $( this );
			var code = $( '#cptb-generated-code' ).val();
			navigator.clipboard.writeText( code ).then( function () {
				var original = $btn.text();
				$btn.text( $btn.data( 'copied' ) );
				setTimeout( function () {
					$btn.text( original );
				}, 1500 );
			} );
		} );
	} );
} )( jQuery );
