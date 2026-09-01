/**
 * CPT Builder — image fields on post edit screens.
 */
( function ( $ ) {
	'use strict';

	$( function () {
		$( document ).on( 'click', '.cptb-select-image', function ( e ) {
			e.preventDefault();

			var $wrap = $( this ).closest( '.cptb-image-field' );
			var frame = wp.media( {
				title: 'Select Image',
				library: { type: 'image' },
				multiple: false
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				var thumb = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
				$wrap.find( 'input[type="hidden"]' ).val( attachment.id );
				$wrap.find( '.cptb-image-preview' ).html( '<img src="' + thumb + '" alt="" />' );
				$wrap.find( '.cptb-remove-image' ).show();
			} );

			frame.open();
		} );

		$( document ).on( 'click', '.cptb-remove-image', function ( e ) {
			e.preventDefault();
			var $wrap = $( this ).closest( '.cptb-image-field' );
			$wrap.find( 'input[type="hidden"]' ).val( '' );
			$wrap.find( '.cptb-image-preview' ).empty();
			$( this ).hide();
		} );
	} );
} )( jQuery );
