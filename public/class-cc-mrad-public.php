<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CC_BuddyPress_Docs_Maps_Reports_Extension
 * @subpackage CC_BuddyPress_Docs_Maps_Reports_Extension/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    CC_BuddyPress_Docs_Maps_Reports_Extension
 * @subpackage CC_BuddyPress_Docs_Maps_Reports_Extension/public
 * @author     David Cavins
 */
class CC_MRAD_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name       The name of the plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cc-mrad-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Public_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Public_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cc-group-pages-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_edit_scripts() {

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cc-group-pages-edit.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register new XML-RPC methods to catch incoming updates from the Maps system.
	 *
	 * @since    1.0.0
	 */
	public function filter_xmlrpc_methods( $methods ) {
	    $methods['cc.record_map_activity'] = array( $this, 'xmlrpc_update_maps_reports' );
	    $methods['cc.delete_map_activity'] = array( $this, 'xmlrpc_delete_maps_reports' );
	    return $methods;
	}

	// API requests against the Maps/Reports DB
	/**
	 * Returns an array of arrays of saved maps for the viewed USER.
	 ***/
	function get_saved_maps_reports_for_user( $user_id, $item_type ) {
		if ( ! $user_id )
			return false;

		return $this->json_svc_make_request( $item_type, $group_id = false, $user_id );
	}

	/**
	 * Returns an array of arrays of saved maps for the viewed GROUP.
	 ***/
	function get_saved_maps_reports_for_group( $group_id, $item_type ) {
		if ( ! $group_id )
			return false;

		return $this->json_svc_make_request( $item_type, $group_id );
	}

	/**
	 * Returns an array of arrays of saved maps for the USER in the viewed GROUP.
	 ***/
	function get_saved_maps_reports_for_user_in_group( $group_id, $user_id, $item_type ) {
		if ( ! $group_id || ! $user_id )
			return false;

		return $this->json_svc_make_request( $item_type, $group_id, $user_id );
	}
	/**
	 * Returns an array describing a single saved map or report.
	 ***/
	function get_single_map_report( $item_id, $item_type ) {
		if ( ! $item_id )
			return false;

		return $this->json_svc_make_request( $item_type, $group_id = false, $user_id = false, $search = false, $item_id );
	}

	/**
	 * Returns an array of arrays of saved areas for the USER in the viewed GROUP.
	 ***/
	function get_saved_areas_for_user_in_group( $group_id, $user_id ) {
		if ( ! $group_id || ! $user_id )
			return false;

		return $this->json_svc_make_request( 'area', $group_id, $user_id );
	}

	/**
	 * Returns an array of arrays of featured items.
	 ***/
	function get_featured_items( $item_type, $number_of_items, $keywords = false ) {
		// Get some map results.
		$item_details = cc_json_svc_make_request( $item_type, $group_id = false, $user_id = false, $search = $keywords, $item_id = false, $featured = true );

		// Sort them by id DESC.
		$date_order = array();
		foreach ( $item_details as $key => $row)	{
		    $date_order[$key] = $row['id'];
		}
		// Return the most recent x.
		if ( array_multisort( $date_order, SORT_DESC, $item_details ) ) {
			return array_slice( $item_details, 0, (int) $number_of_items );
		} else {
			return false;
		}

	}

	/**
	* Actually send and receive the result
	* @param 	$item_type 	string 	map|report|area
	* @param 	$group_id 	int
	* @param 	$user_id 	int
	* @param 	$search 	string 	search terms
	* @param 	$item_id 	bool 	ID of individual item. Must be used in combination with $item_type.
	*
	* @return 	array of arrays
	*/
	function json_svc_make_request( $item_type, $group_id = false, $user_id = false, $search = false, $item_id = false, $featured = false ) {
		if ( ! in_array( $item_type, array( 'map', 'report', 'area' ) ) ) {
			return false;
		}

		$uri = "http://maps.communitycommons.org/apiservice/savedcontent.svc/?itemtype={$item_type}";

		if ( $group_id ) {
			$uri = $uri . "&hubid={$group_id}";
		}
		if ( $user_id ) {
			$uri = $uri . "&userid={$user_id}";
		}
		if ( $search ) {
			$uri = $uri . "&keywords={$search}";
		}
		if ( $item_id ) {
			$uri = $uri . "&itemid={$item_id}";
		}
		if ( $featured ) {
			$uri = $uri . "&featured=1";
		}
	    // $towrite = PHP_EOL . print_r( $uri, TRUE );
	    // $fp = fopen('svc-request.txt', 'a');
	    // fwrite($fp, $towrite);
	    // fclose($fp);

		$response = wp_remote_get( $uri );

	    if ( is_wp_error( $response ) )
	        return false;

	    if ( $body = wp_remote_retrieve_body( $response ) ) {
			$decoded = json_decode( $body, true );
			if ( $item_id ) {
				// For single results, only return the result--no wrapper array.
				return $decoded[0];
			} else {
				return $decoded;
			}
		} else {
			return false;
		}
	}

	/**
	 * We'll need to create/update docs to mirror what's happening on the maps/reports side.
	 *
	 * @since    1.0.0
	 */
	function xmlrpc_update_maps_reports( $args ) {
	    global $wp_xmlrpc_server;
	    $wp_xmlrpc_server->escape( $args );

	    $towrite = PHP_EOL . print_r($date = date('Y-m-d H:i:s'), TRUE) . ' | ' . print_r($args, TRUE);
	    $fp = fopen('xml-rpc-request-args.txt', 'a');
	    fwrite($fp, $towrite);
	    fclose($fp);

	    //New idea, get simple request, use JSON request to get details (for reliable escaping and better security)
	    $username = $args[0];
	    $password = $args[1];
	    $activity_type = $args[2];
	    $item_id = $args[3]; //Should be the id of the map or report, they're in different tables, so could have duplicate ids, unfort. If item is saved to a group, we use that ID for the item_id

	    //Make sure that the requester is legit
	    $user = get_user_by( 'login', $username );

	    //Compare the hashed password to the submitted pw
	    if ( ! $user || $user->user_pass != $password ) {
	        return 'The username or password is incorrect.';
	      }

	    //Make sure the request is a type we recognize, if it is, get the details
	    switch ( $activity_type ) {
	      case 'map_created':
	        $item_type = 'map';
	        $meta_query_key = 'map_table_ID';
	        break;
	      case 'report_created':
	        $item_type = 'report';
  	        $meta_query_key = 'report_table_ID';
	        break;
	      default:
	          // No match, bail
	          return $wp_xmlrpc_server->error;
	        break;
	    }
	    //This function makes a JSON request to get the item details
	    $item = $this->get_single_map_report( $item_id, $item_type );

	    $towrite = PHP_EOL . 'json item response ' . print_r($item, TRUE);
	    $fp = fopen('xml-rpc-request-args.txt', 'a');
	    fwrite($fp, $towrite);
	    fclose($fp);

	    if ( $item ) {
	    	$mrad_class = CC_MRAD::get_instance();
	    	$bp = buddypress();
	    	$bp_docs_tag_tax_name = $bp->bp_docs->docs_tag_tax_name;

			$args = array(
				'title'			=> $item['title'],
				'content' 		=> $item['description'],
				'author_id'		=> $user->ID,
				'group_id'		=> 0,
				'is_auto'		=> 0,
				// 'taxonomies'	=> array( $bp_docs_tag_tax_name => $item['tags'] ),
				'settings'		=> array(   'read' => 'creator',
											'edit' => 'creator',
											'read_comments' => 'creator',
											'post_comments' => 'creator',
											'view_history' => 'creator'
										),
				'parent_id'		=> 0,
			);


			// $user_link = bp_core_get_userlink( $item['owner'] );
			// $item_link = '<a href="' . $item['link'] . '">' . $item['title'] . '</a>';

			// Prepare the item to be saved.

			// See if a post already exists, and we should just update it.
			// We store the map or report table ID as post meta because they are the masters.
			// Note that duplicate IDs will exist because they're stored in two tables.

			// BuddyPress Docs hides off-limit docs, so we'll need to temporarily turn off its access protection
			remove_action( 'pre_get_posts', 'bp_docs_general_access_protection', 28 );

			$meta_query = get_posts( array(
					'post_type'    => bp_docs_get_post_type_name(),
					'meta_key'     => $meta_query_key,
					'meta_value'   => $item_id,
					'meta_compare' => '='
			) );

			add_action( 'pre_get_posts', 'bp_docs_general_access_protection', 28 );

		    // $towrite = PHP_EOL . 'meta query found posts # ' . print_r($meta_query->found_posts, TRUE);
   		    // $towrite .= PHP_EOL . 'meta query ' . print_r ( $meta_query, TRUE);

		    if ( ! empty( $meta_query ) ) {
   				// $found_post_ids = wp_list_pluck( $meta_query, 'ID' );
		    	// Grab the ID for passing into our save function.
		    	$args['doc_id'] = current( $meta_query )->ID;
		    }

		    // Sharing
		    switch ( $item['sharing'] ) {
		    	case 'public':
		    		// $args['group_id'] = 0;
    				$args['settings'] = array( 	'read' => 'anyone',
													'edit' => 'creator',
													'read_comments' => 'anyone',
													'post_comments' => 'anyone',
													'view_history' => 'creator'
						);
		    		break;
		    	case 'personal':
		    		// $args['group_id'] = 0;
    				// $args[ 'settings' ] Uses the defaults.
			    	break;
		    	// Anything else is a group id.
		    	default:
		    		$args['group_id'] = (int) $item['sharing'];
    				$args['settings'] = array(    'read' => 'group-members',
													'edit' => 'creator',
													'read_comments' => 'group-members',
													'post_comments' => 'group-members',
													'view_history' => 'creator'
						);
		    		break;
		    }

		    // if ( $item['itemtype'] == 'map' ) {
		    // 	$args['taxonomies'][$mrad_class->get_taxonomy_name()] = 'map';
		    // } elseif ( $item['itemtype'] == 'report' ) {
		    // 	$args['taxonomies'][$mrad_class->get_taxonomy_name()] = 'report';
		    // }

		    $towrite = PHP_EOL . 'args just before save ' . print_r( $args, TRUE );
		    $fp = fopen('xml-rpc-request-args.txt', 'a');
		    fwrite($fp, $towrite);
		    fclose($fp);

		    // Save!
    		$instance = new CC_MRAD_BP_Doc_Save;
			$post_id = $instance->save( $args );

		    $towrite = PHP_EOL . 'post id ' . print_r( $post_id, TRUE );
		    $fp = fopen('xml-rpc-request-args.txt', 'a');
		    fwrite($fp, $towrite);
		    fclose($fp);

			// If the save was successful, save some post meta.
			if ( ! empty( $post_id ) ) {
				update_post_meta( $post_id, $meta_query_key, $item['id'] );

			    if ( $item['itemtype'] == 'map' ) {
			    	// $args['taxonomies'][$mrad_class->get_taxonomy_name()] = 'map';
                    $tax_result = wp_set_object_terms( $post_id, 'map', $mrad_class->get_taxonomy_name() );
			    } elseif ( $item['itemtype'] == 'report' ) {
			    	// $args['taxonomies'][$mrad_class->get_taxonomy_name()] = 'report';
                    $tax_result = wp_set_object_terms( $post_id, 'report', $mrad_class->get_taxonomy_name() );
			    }

			    if ( ! empty( $item['tags'] ) ) {
			    	wp_set_object_terms( $post_id, $item['tags'], $bp_docs_tag_tax_name );
			    }
			}

	  } else {

	    $towrite = PHP_EOL . 'The JSON request returned an empty object.';
	    $fp = fopen('xml-rpc-request-args.txt', 'a');
	    fwrite($fp, $towrite);
	    fclose($fp);

	  }
	}

	/**
	 * We'll also need to delete docs when receiving notice via XML-RPC
	 *
	 * @since    1.0.0
	 */
	function xmlrpc_delete_maps_reports( $args ) {
	    global $wp_xmlrpc_server;
	    $wp_xmlrpc_server->escape( $args );

	    $towrite = PHP_EOL . print_r($date = date('Y-m-d H:i:s'), TRUE) . ' | ' . print_r($args, TRUE);
	    $fp = fopen('xml-rpc-request-args.txt', 'a');
	    fwrite($fp, $towrite);
	    fclose($fp);

	    //New idea, get simple request, use JSON request to get details (for reliable escaping and better security)
	    $username = $args[0];
	    $password = $args[1];
	    $activity_type = $args[2];
	    $item_id = $args[3]; //Should be the id of the map or report, they're in different tables, so could have duplicate ids, unfort. If item is saved to a group, we use that ID for the item_id

	    //Make sure that the requester is legit
	    $user = get_user_by( 'login', $username );

	    //Compare the hashed password to the submitted pw
	    if ( ! $user || $user->user_pass != $password ) {
	        return 'The username or password is incorrect.';
	      }

	    //Make sure the request is a type we recognize, if it is, get the details
	    switch ( $activity_type ) {
	      case 'map_deleted':
	        $item_type = 'map';
	        $meta_query_key = 'map_table_ID';
	        break;
	      case 'report_deleted':
	        $item_type = 'report';
  	        $meta_query_key = 'report_table_ID';
	        break;
	      default:
	          // No match, bail
	          return $wp_xmlrpc_server->error;
	        break;
	    }

	    // Find the correct doc
		// BuddyPress Docs hides off-limit docs, so we'll need to temporarily turn off its access protection
		remove_action( 'pre_get_posts', 'bp_docs_general_access_protection', 28 );

		$meta_query = get_posts( array(
				'post_type'    => bp_docs_get_post_type_name(),
				'meta_key'     => $meta_query_key,
				'meta_value'   => $item_id,
				'meta_compare' => '='
		) );

		add_action( 'pre_get_posts', 'bp_docs_general_access_protection', 28 );

	    if ( ! empty( $meta_query ) ) {
			$found_post_ids = wp_list_pluck( $meta_query, 'ID' );
			foreach ( $found_posts as $post_id ) {
				wp_delete_post( $post_id, $force_delete = true );
			}
	    }

	    return 'The post was deleted successfully';
	}
} // End class