function send_map_update( item_id, activity_type ){
		jQuery.ajax({
            type: 'GET',
	        url: 'http://commonsdev.local/wp-admin/admin-ajax.php',
            cache: false,
            dataType: 'jsonp',
            crossDomain: true,
            async:   false,
            data: {
                'action': 'cc-update-maps-reports', //calls wp_ajax_cc-update-maps-reports
                'user_id': 2,
                'activity_type': activity_type, // other value: report_updated
	    		'item_id': item_id,
            },
            success: function( response ){
            	console.log( 'doc_created success' );
            	console.log( response.doc_id );
            	console.log( response.message );
            },
            error: function( response ){
            	console.log( 'doc_created error' );
            	console.log( response.doc_id );
            	console.log( response.message );
            }
        });
    }

	function send_map_delete( item_id, activity_type ){
		jQuery.ajax({
            type: 'GET',
	        url: 'http://commonsdev.local/wp-admin/admin-ajax.php',
            cache: false,
            dataType: 'jsonp',
            crossDomain: true,
            async:   false,
            data: {
                'action': 'cc-update-maps-reports', //calls wp_ajax_cc-update-maps-reports
                'user_id': 2,
                'activity_type': activity_type, // other value: report_updated
	    		'item_id': item_id,
            },
            success: function( response ){
            	console.log( 'map_deleted success' );
            	console.log( response.doc_id );
            	console.log( response.message );
            },
            error: function( response ){
            	console.log( 'map_deleted error' );
            	console.log( response.doc_id );
            	console.log( response.message );
            }
        });
    }