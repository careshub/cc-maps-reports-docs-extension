(function( $ ) {
	'use strict';

	 $(function() {
		// initialize the megamenu applied to the create link
		$('#bp-create-doc-button-menu').accessibleMegaMenu();

		// On map-type doc pages, we generate the map.
		// Let's base the size of the fetched image on how much space we have to work with.
		// base_map_widget_src is output at the action `bp_docs_after_doc_content`
		if ( typeof base_map_widget_src !== 'undefined' ) {
			var width = jQuery( '.doc-content' ).width() - 80,
				height = 240,
				script_src = '',
				dimensions = '';

			width = Math.round( width );

			if ( width > 500 ) {
				height = Math.round( width * 2 / 3 );
			}
			dimensions = '&w=' + width + '&h=' + height;

			// Troubleshooting
			// console.log( 'width is: ' + width );
			// console.log( 'height is: ' + height );
			// console.log( 'calculated height is: ' + height );
			// console.log( 'dimensions statement: ' + dimensions );

			// This is a hack. This widget should only be loaded at page load because it uses document.write().
			// W're loading these widgets asynchronously, so we have to overload doc.write.
			var widget_container = document.getElementById('map-widget-container');
			if ( ! document._write ) {
				document._write = document.write;
			}
			document.write = function (str) {
				widget_container.innerHTML += str;
			};

			// Fetch the script with the correct arguments
			jQuery.ajax({
			      url: base_map_widget_src + dimensions,
			      dataType: "script",
			      cache: true,
			      crossDomain: true
			}).success(function( data, textStatus, jqxhr ) {
				// console.log( data ); // Data returned
				// console.log( textStatus ); // Success
				// console.log( jqxhr.status );
			});
		}

	 });

})( jQuery );