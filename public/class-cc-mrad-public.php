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
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $type_taxonomy_name    The name of the "MRAD Types" taxonomy.
	 */
	public $type_taxonomy_name;

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

		// $mrad_class = CC_MRAD::get_instance();
		// $this->type_taxonomy_name = $mrad_class->get_taxonomy_name();
		$this->type_taxonomy_name = 'bp_docs_type';

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cc-mrad-public.css', array(), $this->version, 'all' );
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cc-mrad-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_edit_scripts() {

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cc-group-pages-edit.js', array( 'jquery' ), $this->version, false );

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
		$item_details = $this->json_svc_make_request( $item_type, $group_id = false, $user_id = false, $search = $keywords, $item_id = false, $featured = true );

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

		// Use production or test value of API endpoint?
		$uri = mrad_map_base_url() . "services/usercontent/savedcontent.svc/?itemtype={$item_type}";

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
		$towrite = PHP_EOL . print_r( $uri, TRUE );
		$fp = fopen('json_update_maps_reports.txt', 'a');
		fwrite($fp, $towrite);
		fclose($fp);

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
	* Send DELETE requests
	* @param 	$item_type 	string 	map|report|area
	* @param 	$item_id 	int 	ID of individual item. Must be used in combination with $item_type.
	* @param 	$group_id 	int Optional. If specified, will disassociate from the group only, not delete the item.
	*
	*/
	function json_svc_make_delete_request( $item_type, $item_id, $group_id = false ) {
		if ( ! in_array( $item_type, array( 'map', 'report', 'area' ) ) ) {
			return false;
		}

		$current_user = wp_get_current_user();
		// Item ID and User ID are required.
		if ( empty( $item_id ) || empty( $current_user->ID ) ) {
			return false;
		}

		// Use production or test value of API endpoint?
		$uri = mrad_map_base_url() . "services/usercontent/savedcontent.svc/?itemtype={$item_type}";
		$uri .= "&userid={$current_user->ID}&key=" . md5( $current_user->user_pass );

		if ( $item_id ) {
			$uri = $uri . "&itemid={$item_id}";
		}
		if ( $group_id ) {
			$uri = $uri . "&hubid={$group_id}";
		}
		$towrite = PHP_EOL . 'Making a DELETE request.' . print_r( $uri, TRUE );
		$fp = fopen('json_update_maps_reports.txt', 'a');
		fwrite($fp, $towrite);
		fclose($fp);

		$args = array(
		    'method' => 'DELETE'
		);

		$response = wp_remote_request( $uri, $args );

		$towrite = PHP_EOL . '$response: ' . print_r( $response, TRUE );
		$fp = fopen('json_update_maps_reports.txt', 'a');
		fwrite($fp, $towrite);
		fclose($fp);

		if ( is_wp_error( $response ) )
			return false;

		// if ( $body = wp_remote_retrieve_body( $response ) ) {
		// 	$decoded = json_decode( $body, true );
		// 	if ( $item_id ) {
		// 		// For single results, only return the result--no wrapper array.
		// 		return $decoded[0];
		// 	} else {
		// 		return $decoded;
		// 	}
		// } else {
		// 	return false;
		// }
	}

	/**
	 * We'll need to create/update/delete docs to mirror what's
	 * happening on the maps/reports side.
	 *
	 * @since    1.0.0
	 */
	function json_update_maps_reports() {
		$towrite = PHP_EOL . print_r( $date = date( 'Y-m-d H:i:s' ), TRUE ) . ' | ' . print_r( $_REQUEST, TRUE );

		// Safe defaults
		$response = array();
		$item = array();
		$found_post_ids = array();
		$doc_id = 0;

		// Receive simple request, use JSON request to get details (for reliable escaping)
		$user_id = (int) $_REQUEST['user_id'];;
		$activity_type = $_REQUEST['activity_type'];
		$item_id = (int) $_REQUEST['item_id']; //Should be the id of the map or report, they're in different tables, so could have duplicate ids, unfort. If item is saved to a group, we use that ID for the item_id

		// Is this user a current member?
		if ( false === get_userdata( $user_id )  ) {
			// Something is wrong. That user doesn't appear to exist.
			// Set JSON response
			$response = array(
				'doc_id' => 0,
				'message' => "That user ID is not valid.",
				);

			// Send response
			header("content-type: text/javascript; charset=utf-8");
			header("Access-Control-Allow-Origin: *");
			echo htmlspecialchars($_REQUEST['callback']) . '(' . json_encode( $response ) . ')';
			exit;
		}

		// Check that the user is properly logged in.
		$current_user_id = get_current_user_id();
		if ( empty( $current_user_id ) ) {
			// Something is wrong. The user must be logged in to save/edit maps.
			// Set JSON response
			$response = array(
				'doc_id' => 0,
				'message' => "The user must be logged in.",
				);

			// Send response
			header("content-type: text/javascript; charset=utf-8");
			header("Access-Control-Allow-Origin: *");
			echo htmlspecialchars($_REQUEST['callback']) . '(' . json_encode( $response ) . ')';
			exit;
		}

		$towrite .= PHP_EOL . 'Who does WP think is logged in? ' . print_r( $current_user_id, TRUE );
		$fp = fopen('json_update_maps_reports.txt', 'a');
		fwrite($fp, $towrite);
		fclose($fp);

		// Make sure the request is a type we recognize, if it is, get the details
		switch ( $activity_type ) {
		  case 'map_updated':
  				$item_type = 'map';
				$meta_query_key = 'map_table_ID';
				$action = 'update';
				break;
		  case 'map_deleted':
				$item_type = 'map';
				$meta_query_key = 'map_table_ID';
				$action = 'delete';
				break;
		  case 'report_updated':
		  		$item_type = 'report';
				$meta_query_key = 'report_table_ID';
				$action = 'update';
				break;
		  case 'report_deleted':
				$item_type = 'report';
				$meta_query_key = 'report_table_ID';
				$action = 'delete';
				break;
		  case 'area_updated':
		  		$item_type = 'area';
				$meta_query_key = 'area_table_ID';
				$action = 'update';
				break;
		  case 'area_deleted':
				$item_type = 'area';
				$meta_query_key = 'area_table_ID';
				$action = 'delete';
				break;
		  default:
				// This is an error condition.
				// Set JSON response
				$response = array(
					'doc_id' => 0,
					'message' => "That activity_type is unknown.",
					);

				// Send response
				header("content-type: text/javascript; charset=utf-8");
				header("Access-Control-Allow-Origin: *");
				echo htmlspecialchars( $_REQUEST['callback'] ) . '(' . json_encode( $response ) . ')';
				exit;

				break;
		}

		// Let's see if a doc already exists, and we're making updates to it, or creating a new doc.
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

		    $towrite = PHP_EOL . 'Found these docs: ' . print_r( implode( ', ', $found_post_ids ), TRUE);
		    $fp = fopen('json_update_maps_reports.txt', 'a');
		    fwrite($fp, $towrite);
		    fclose($fp);
		}


		// OK, so we're updating an existing post. Can this user do that?
		if ( ! empty( $found_post_ids ) ) {
			$doc_id = current( $found_post_ids );
			if ( ! user_can( $user_id, 'bp_docs_edit', $doc_id ) ) {

				$towrite = PHP_EOL . 'This user cannot update this doc.' . print_r( $doc_id, TRUE );
				$fp = fopen('json_update_maps_reports.txt', 'a');
				fwrite($fp, $towrite);
				fclose($fp);
				// This is an error condition.
				// Set JSON response
				$response = array(
					'doc_id' => $doc_id,
					'message' => "This user can't update that doc.",
					);

				// Send response
				header("content-type: text/javascript; charset=utf-8");
				header("Access-Control-Allow-Origin: *");
				echo htmlspecialchars( $_REQUEST['callback'] ) . '(' . json_encode( $response ) . ')';
				exit;
			} else {
				$towrite = PHP_EOL . 'This user is allowed to update this doc.' . print_r( $doc_id, TRUE );
				$fp = fopen('json_update_maps_reports.txt', 'a');
				fwrite($fp, $towrite);
				fclose($fp);
			}
		}

		// Handle the update/create actions
		if ( 'update' == $action ) {

			// Make a JSON request to get the item details.
			$item = $this->get_single_map_report( $item_id, $item_type );

			if ( $item ) {
				$towrite = PHP_EOL . 'json item response ' . print_r( $item, TRUE );
				$fp = fopen('json_update_maps_reports.txt', 'a');
				fwrite($fp, $towrite);
				fclose($fp);

				$mrad_class = CC_MRAD::get_instance();
				$bp = buddypress();
				$bp_docs_tag_tax_name = $bp->bp_docs->docs_tag_tax_name;

				$args = array(
					'title'			=> $item['title'],
					'content' 		=> $item['description'],
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

				// Prepare the item to be saved.

				if ( ! empty( $doc_id ) ) {
					// $found_post_ids = wp_list_pluck( $meta_query, 'ID' );
					// Grab the ID for passing into our save function.
					$args['doc_id'] = $doc_id;
				} else {
					// We add an author_id to new docs
					$args['author_id'] = $user_id;
					// Let's get the date right, too.
					$args['post_date'] = $this->convert_date_to_wp_format( $item['savedate'] );
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

				$towrite = PHP_EOL . 'args just before save ' . print_r( $args, TRUE );
				$fp = fopen('json_update_maps_reports.txt', 'a');
				fwrite($fp, $towrite);
				fclose($fp);

				// Save!
				$instance = new CC_MRAD_BP_Doc_Save;
				$post_id = $instance->save( $args );

				$towrite = PHP_EOL . 'post id ' . print_r( $post_id, TRUE );
				$fp = fopen('json_update_maps_reports.txt', 'a');
				fwrite($fp, $towrite);
				fclose($fp);

				// If the save was successful, save some post meta.
				if ( ! empty( $post_id ) ) {
					$towrite = PHP_EOL . 'beginning post-save meta/taxonomy stuff. ' . print_r( $post_id, TRUE );

					update_post_meta( $post_id, $meta_query_key, $item['id'] );

					if ( $item['itemtype'] == 'map' ) {
						// $args['taxonomies'][$mrad_class->get_taxonomy_name()] = 'map';
						$tax_result = wp_set_object_terms( $post_id, 'map', $this->type_taxonomy_name );
					} elseif ( $item['itemtype'] == 'report' ) {
						// $args['taxonomies'][$mrad_class->get_taxonomy_name()] = 'report';
						$tax_result = wp_set_object_terms( $post_id, 'report', $this->type_taxonomy_name );
					} elseif ( $item['itemtype'] == 'area' ) {
						// $args['taxonomies'][$mrad_class->get_taxonomy_name()] = 'report';
						$tax_result = wp_set_object_terms( $post_id, 'area', $this->type_taxonomy_name );
					}

					if ( ! empty( $item['tags'] ) ) {
						// $item['tags'] is EXACTLY what the user entered in the save form
						// on the mapping environment.
						// No pre-processing, splitting, trimming has been done.
						$tags = array_map( 'trim', explode( ',', $item['tags'] ) );
						$towrite .= PHP_EOL . 'processed tags: ' . print_r( $tags, TRUE );

						wp_set_object_terms( $post_id, $tags, $bp_docs_tag_tax_name );
					}

					if ( ! empty( $item['channel'] ) ) {
						// $item['channel'] is an array of the term names, like:
						// array( "Environment", "Economy")
						// wp_set_object_terms() needs a slug or id, though, so we'll have to
						// translate the terms names to ids.
						$category_ids = array();
						foreach ( $item['channel'] as $cat_name ) {
							$category = get_term_by( 'name', $cat_name, 'category' );
							if ( ! empty( $category ) ) {
								$category_ids[] = (int) $category->term_id;
							}
						}
						$towrite .= PHP_EOL . 'processed categories: ' . print_r( $category_ids, TRUE );

						wp_set_object_terms( $post_id, $category_ids, 'category' );
					}

					if ( isset( $item['featured'] ) ) {
						$towrite .= PHP_EOL . 'item[featured]: ' . print_r( $item['featured'], TRUE );

						if ( $item['featured'] ) {
							update_post_meta( $post_id, 'mrad_featured', true );
							$towrite .= PHP_EOL . 'setting featured meta.';
						} else {
							$is_featured = get_post_meta( $post_id, 'mrad_featured', true );
							$towrite .= PHP_EOL . 'was featured: ' . print_r( $is_featured, TRUE );

							if ( $is_featured ) {
								delete_post_meta( $post_id, 'mrad_featured' );
								$towrite .= PHP_EOL . 'deleting featured meta.';
							}
						}
					}

					$fp = fopen('json_update_maps_reports.txt', 'a');
					fwrite($fp, $towrite);
					fclose($fp);

					// Set JSON response
					$response = array(
						'doc_id' => $post_id,
						'message' => "Success! Created or updated the doc.",
						);
				}

		  } else {
		  		// Our JSON request failed for some reason.
				$towrite = PHP_EOL . 'The JSON request returned an empty object.';
				$fp = fopen('json_update_maps_reports.txt', 'a');
				fwrite($fp, $towrite);
				fclose($fp);

				// Set JSON response
				$response = array(
					'doc_id' => 0,
					'message' => "The JSON request for a $item_type with the ID $item_id failed.",
					);
		  }

			// Send response
			header("content-type: text/javascript; charset=utf-8");
			header("Access-Control-Allow-Origin: *");
			echo htmlspecialchars( $_REQUEST['callback'] ) . '(' . json_encode( $response ) . ')';
			exit;

		} elseif ( 'delete' == $action ) {

			if ( ! empty( $found_post_ids ) ) {
				$towrite = '';
				foreach ( $found_post_ids as $post_id ) {
					// @TODO: Maybe remove the action that pings Yan's system, since we know the request came from there?
					$deleted = bp_docs_trash_doc( $post_id );
				    $towrite .= PHP_EOL . 'success deleting ' . print_r( $post_id, TRUE) . ': ' . print_r( $deleted, TRUE);
				}
			    $fp = fopen('json_update_maps_reports.txt', 'a');
			    fwrite($fp, $towrite);
			    fclose($fp);
			}

			// Success.
			// Set JSON response
			$response = array(
				'message' => "Doc deleted successfully.",
			);

			// Send response
			header("content-type: text/javascript; charset=utf-8");
			header("Access-Control-Allow-Origin: *");
			echo htmlspecialchars( $_REQUEST['callback'] ) . '(' . json_encode( $response ) . ')';
			exit;
		}
	}

	/**
	 * A procedural php function for creating reports/maps.
	 *
	 * @since    1.0.0
	 */
	function php_update_maps_reports( $user_id, $activity_type, $item_id ) {
		$towrite = PHP_EOL . print_r( $date = date( 'Y-m-d H:i:s' ), TRUE ) . ' | user_id: ' . print_r( $user_id, TRUE ) . ' | activity_type: ' . print_r( $activity_type, TRUE ) . ' | item_id: ' . print_r( $item_id, TRUE );
		$fp = fopen('php_update_maps_reports.txt', 'a');
		fwrite($fp, $towrite);
		fclose($fp);

		// Safe defaults
		$response = array();
		$item = array();
		$found_post_ids = array();
		$doc_id = 0;

		// Is this user a current member?
		if ( false === get_userdata( $user_id )  ) {
			// Something is wrong. That user doesn't appear to exist.
			$towrite = PHP_EOL . 'user_id is not a valid user_id.';
			$fp = fopen('php_update_maps_reports.txt', 'a');
			fwrite($fp, $towrite);
			fclose($fp);
			return;
		}

		// Check that the user is properly logged in.
		$current_user_id = get_current_user_id();
		if ( empty( $current_user_id ) ) {
			// Something is wrong. The user must be logged in to save/edit maps.
			$towrite = PHP_EOL . 'no user is logged in.';
			$fp = fopen('php_update_maps_reports.txt', 'a');
			fwrite($fp, $towrite);
			fclose($fp);
			return;
		} else {
			// $towrite .= PHP_EOL . 'WP thinks is logged in? ' . print_r( $current_user_id, TRUE );
			// $fp = fopen('json_update_maps_reports.txt', 'a');
			// fwrite($fp, $towrite);
			// fclose($fp);
		}

		// Make sure the request is a type we recognize, if it is, get the details
		switch ( $activity_type ) {
		  case 'map_updated':
  				$item_type = 'map';
				$meta_query_key = 'map_table_ID';
				$action = 'update';
				break;
		  case 'map_deleted':
				$item_type = 'map';
				$meta_query_key = 'map_table_ID';
				$action = 'delete';
				break;
		  case 'report_updated':
		  		$item_type = 'report';
				$meta_query_key = 'report_table_ID';
				$action = 'update';
				break;
		  case 'report_deleted':
				$item_type = 'report';
				$meta_query_key = 'report_table_ID';
				$action = 'delete';
				break;
		  case 'area_updated':
		  		$item_type = 'area';
				$meta_query_key = 'area_table_ID';
				$action = 'update';
				break;
		  case 'area_deleted':
				$item_type = 'area';
				$meta_query_key = 'area_table_ID';
				$action = 'delete';
				break;
		  default:
				// This is an error condition.
				$towrite = PHP_EOL . 'Unrecognized activity type.';
				$fp = fopen('php_update_maps_reports.txt', 'a');
				fwrite($fp, $towrite);
				fclose($fp);
				return;

				break;
		}

		// Let's see if a doc already exists, and we're making updates to it, or creating a new doc.
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

		    $towrite = PHP_EOL . 'Found these docs: ' . print_r( implode( ', ', $found_post_ids ), TRUE);
		    $fp = fopen('php_update_maps_reports.txt', 'a');
		    fwrite($fp, $towrite);
		    fclose($fp);
		}


		// OK, so we're updating an existing post. Can this user do that?
		if ( ! empty( $found_post_ids ) ) {
			$doc_id = current( $found_post_ids );
			if ( ! user_can( $user_id, 'bp_docs_edit', $doc_id ) ) {
				$towrite = PHP_EOL . 'This user cannot update this doc.' . print_r( $doc_id, TRUE );
				$fp = fopen('php_update_maps_reports.txt', 'a');
				fwrite($fp, $towrite);
				fclose($fp);
				// This is an error condition.
				return;
			} else {
				$towrite = PHP_EOL . 'This user is allowed to update this doc.' . print_r( $doc_id, TRUE );
				$fp = fopen('php_update_maps_reports.txt', 'a');
				fwrite($fp, $towrite);
				fclose($fp);
			}
		}

		// Handle the update/create actions
		if ( 'update' == $action ) {

			// Make a JSON request to get the item details.
			$item = $this->get_single_map_report( $item_id, $item_type );

			if ( $item ) {
				$towrite = PHP_EOL . 'json item response ' . print_r( $item, TRUE );
				$fp = fopen('php_update_maps_reports.txt', 'a');
				fwrite($fp, $towrite);
				fclose($fp);

				$mrad_class = CC_MRAD::get_instance();
				$bp = buddypress();
				$bp_docs_tag_tax_name = $bp->bp_docs->docs_tag_tax_name;

				$args = array(
					'title'			=> $item['title'],
					'content' 		=> $item['description'],
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

				// Prepare the item to be saved.

				if ( ! empty( $doc_id ) ) {
					// $found_post_ids = wp_list_pluck( $meta_query, 'ID' );
					// Grab the ID for passing into our save function.
					$args['doc_id'] = $doc_id;
				} else {
					// We add an author_id to new docs
					$args['author_id'] = $user_id;
					// Let's get the date right, too.
					$args['post_date'] = $this->convert_date_to_wp_format( $item['savedate'] );
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

				$towrite = PHP_EOL . 'args just before save ' . print_r( $args, TRUE );
				$fp = fopen('php_update_maps_reports.txt', 'a');
				fwrite($fp, $towrite);
				fclose($fp);

				// Save!
				$instance = new CC_MRAD_BP_Doc_Save;
				$post_id = $instance->save( $args );

				$towrite = PHP_EOL . 'post id ' . print_r( $post_id, TRUE );
				$fp = fopen('php_update_maps_reports.txt', 'a');
				fwrite($fp, $towrite);
				fclose($fp);

				// If the save was successful, save some post meta.
				if ( ! empty( $post_id ) ) {
					$towrite = PHP_EOL . 'beginning post-save meta/taxonomy stuff. ' . print_r( $post_id, TRUE );

					update_post_meta( $post_id, $meta_query_key, $item['id'] );

					if ( $item['itemtype'] == 'map' ) {
						// $args['taxonomies'][$mrad_class->get_taxonomy_name()] = 'map';
						$tax_result = wp_set_object_terms( $post_id, 'map', $this->type_taxonomy_name );
					} elseif ( $item['itemtype'] == 'report' ) {
						// $args['taxonomies'][$mrad_class->get_taxonomy_name()] = 'report';
						$tax_result = wp_set_object_terms( $post_id, 'report', $this->type_taxonomy_name );
					} elseif ( $item['itemtype'] == 'area' ) {
						// $args['taxonomies'][$mrad_class->get_taxonomy_name()] = 'report';
						$tax_result = wp_set_object_terms( $post_id, 'area', $this->type_taxonomy_name );
					}

					if ( ! empty( $item['tags'] ) ) {
						// $item['tags'] is EXACTLY what the user entered in the save form
						// on the mapping environment.
						// No pre-processing, splitting, trimming has been done.
						$tags = array_map( 'trim', explode( ',', $item['tags'] ) );
						$towrite .= PHP_EOL . 'processed tags: ' . print_r( $tags, TRUE );

						wp_set_object_terms( $post_id, $tags, $bp_docs_tag_tax_name );
					}

					if ( ! empty( $item['channel'] ) ) {
						// $item['channel'] is an array of the term names, like:
						// array( "Environment", "Economy")
						// wp_set_object_terms() needs a slug or id, though, so we'll have to
						// translate the terms names to ids.
						$category_ids = array();
						foreach ( $item['channel'] as $cat_name ) {
							$category = get_term_by( 'name', $cat_name, 'category' );
							if ( ! empty( $category ) ) {
								$category_ids[] = (int) $category->term_id;
							}
						}
						$towrite .= PHP_EOL . 'processed categories: ' . print_r( $category_ids, TRUE );

						wp_set_object_terms( $post_id, $category_ids, 'category' );
					}

					if ( isset( $item['featured'] ) ) {
						// $towrite .= PHP_EOL . 'item[featured]: ' . print_r( $item['featured'], TRUE );

						if ( $item['featured'] ) {
							update_post_meta( $post_id, 'mrad_featured', true );
							$towrite .= PHP_EOL . 'setting featured meta.';
						} else {
							$is_featured = get_post_meta( $post_id, 'mrad_featured', true );
							$towrite .= PHP_EOL . 'was featured: ' . print_r( $is_featured, TRUE );

							if ( $is_featured ) {
								delete_post_meta( $post_id, 'mrad_featured' );
								$towrite .= PHP_EOL . 'deleting featured meta.';
							}
						}
					}

					$fp = fopen('php_update_maps_reports.txt', 'a');
					fwrite($fp, $towrite);
					fclose($fp);
				}

		  } else {
		  		// Our JSON request failed for some reason.
				$towrite = PHP_EOL . 'The JSON request returned an empty object.';
				$fp = fopen('php_update_maps_reports.txt', 'a');
				fwrite($fp, $towrite);
				fclose($fp);

				return;
		  }

		} elseif ( 'delete' == $action ) {

			if ( ! empty( $found_post_ids ) ) {
				$towrite = '';
				foreach ( $found_post_ids as $post_id ) {
					// @TODO: Maybe remove the action that pings Yan's system, since we know the request came from there?
					$deleted = bp_docs_trash_doc( $post_id );
				    $towrite .= PHP_EOL . 'success deleting ' . print_r( $post_id, TRUE) . ': ' . print_r( $deleted, TRUE);
				}
			    $fp = fopen('php_update_maps_reports.txt', 'a');
			    fwrite($fp, $towrite);
			    fclose($fp);
			}
		}
	}

	/**
	 * Maps and reports don't have a "trash" analog, so when one is deleted,
	 * we really delete it here, too.
	 *
	 * @since 1.0.0
	 *
	 * @param array $delete_args Info about deleted doc.
	 */
	public function permanently_delete_maps_reports( $delete_args ) {
		$doc_id = $delete_args['ID'];

	    $towrite = PHP_EOL . 'in permanently_delete_maps_reports ' . print_r( $doc_id, TRUE);
	    $towrite .= PHP_EOL . 'post_data ' . print_r( get_post( $doc_id ), TRUE);
	    $fp = fopen('json_update_maps_reports.txt', 'a');
	    fwrite($fp, $towrite);
	    fclose($fp);

		if ( empty( $doc_id ) ) {
			return;
		}

		// $mrad_class = CC_MRAD::get_instance();
		$terms = wp_get_object_terms( $doc_id, $this->type_taxonomy_name );
		$item_type = '';

		if ( ! empty( $terms ) ) {
			$item_type = current( $terms )->slug;
			switch ( $item_type ) {
				case 'map':
					$really_delete = true;
					$meta_query_key = 'map_table_ID';
					$item_id = get_post_meta( $doc_id, 'map_table_ID', true );
					break;
				case 'report':
					// First, get some details because we'll need to ping the maps/reports environment on delete
					$really_delete = true;
					$item_id = get_post_meta( $doc_id, 'report_table_ID', true );
					break;
				case 'area':
					// First, get some details because we'll need to ping the maps/reports environment on delete
					$really_delete = true;
					$item_id = get_post_meta( $doc_id, 'area_table_ID', true );
					break;
				default:
				    $towrite = PHP_EOL . 'did not try to delete ' . print_r( $doc_id, TRUE);
				    $fp = fopen('json_update_maps_reports.txt', 'a');
				    fwrite($fp, $towrite);
				    fclose($fp);
					// Do nothing.
					break;
			}

			// If the item_id has been set, this is a type we should delete.
			if ( $item_id ) {
				$deleted = wp_delete_post( $doc_id );
			    $towrite = PHP_EOL . 'success piggyback permanent deletion of ' . print_r( $doc_id, TRUE) . ': ' . print_r( $deleted, TRUE);
			    $fp = fopen('json_update_maps_reports.txt', 'a');
			    fwrite($fp, $towrite);
			    fclose($fp);

			    // Has this item been deleted from the map/report environment?
			    $item = $this->get_single_map_report( $item_id, $item_type );

			    if ( ! empty( $item ) ) {
				    $delete_that_item = $this->json_svc_make_delete_request( $item_type, $item_id );
			    }
			}
		}
	}

	/**
	 * Maps and reports don't have a "trash" analog, so when one is deleted,
	 * we really delete it here, too.
	 *
	 * @since 1.0.0
	 *
	 * @param int $doc_id ID of affected doc.
	 * @param int $group_id ID of group that the doc is being removed from.
	 */
	public function ping_map_env_on_doc_unlink_from_group( $doc_id, $group_id ) {
		// $mrad_class = CC_MRAD::get_instance();
		$terms = wp_get_object_terms( $doc_id, $this->type_taxonomy_name );
		$item_type = '';

		if ( ! empty( $terms ) ) {
			$item_type = current( $terms )->slug;
			switch ( $item_type ) {
				case 'map':
					$item_id = get_post_meta( $doc_id, 'map_table_ID', true );
					break;
				case 'report':
					$item_id = get_post_meta( $doc_id, 'report_table_ID', true );
					break;
				case 'area':
					$item_id = get_post_meta( $doc_id, 'area_table_ID', true );
					break;
				default:
					// Do nothing.
					break;
			}

			// If the item_id has been set, this is a type we can work with.
			if ( $item_id ) {
			    $towrite = PHP_EOL . 'Attempting to change the group assoc on maps for doc_id: ' . print_r( $doc_id, TRUE);
			    $towrite .= PHP_EOL . 'item_id: ' . print_r( $item_id, TRUE);
   			    $towrite .= PHP_EOL . 'item_id: ' . print_r( $item_type, TRUE);
   			    $towrite .= PHP_EOL . 'group_id: ' . print_r( $group_id, TRUE);

			    $fp = fopen('json_update_maps_reports.txt', 'a');
			    fwrite($fp, $towrite);
			    fclose($fp);

			    $response = $this->json_svc_make_delete_request( $item_type, $item_id, $group_id );

			    $towrite .= PHP_EOL . 'json response: ' . print_r( $response, TRUE);
			    $fp = fopen('json_update_maps_reports.txt', 'a');
			    fwrite($fp, $towrite);
			    fclose($fp);
			}
		}
	}

	/**
	 * Convert a mapping environment date to a WP-friendly date.
	 *
	 * @since    1.0.0
	 *
	 * @param    $date string Date in map-environment format.
	 */
	public function convert_date_to_wp_format( $date ) {
		// The dates on the mapping environment are stored as
		// original: 6/24/2015 12:12:28 PM
		// WP expects 2015-06-24 12:12:28

		$shuffle = date_create_from_format( 'n/j/Y H:i:s a', $date );
		return date_format( $shuffle, 'Y-m-d H:i:s' );
	}


	/* Templating *************************************************************/

	/**
	 * Get the location of the template directory.
	 *
	 * @since 1.1.0
	 *
	 * @uses apply_filters()
	 * @return string
	 */
	public function get_template_directory() {
		return apply_filters( 'mrad_get_template_directory', plugin_dir_path( __FILE__ ) . 'templates/' );
	}

	/**
	 * Adds the toggle for the doc types filter links container on the docs loop.
	 *
	 * @since 1.0.0
	 *
 	 * @param array $types Filter descriptions for docs list archive filters.
	 */
	public function add_filter_toggle( $types ) {
		$types[] = array(
			'slug' => 'types',
			'title' => __( 'Types', $this->plugin_name ),
			'query_arg' => 'bpd_type',
		);
		$types[] = array(
			'slug' => 'channels',
			'title' => __( 'Channels', $this->plugin_name ),
			'query_arg' => 'bpd_channel',
		);
		return $types;
	}

	/**
	 * Creates the markup for the doc types filter links on the docs loop.
	 *
	 * @since 1.0.0
	 *
	 * @return string html for filter controls.
	 */
	public function filter_markup() {
		$main_class = CC_MRAD::get_instance();
		$active_filters = $this->get_active_filters();

		$channel_filter = in_array( 'channel', $active_filters);
		$type_filter = in_array( 'types', $active_filters);

		$categories = get_terms( 'category', array( 'exclude' => array( 1 ) ) );
		$existing_types = get_terms( $main_class->get_taxonomy_name() );

		// We show the type drawer if a type has been selected or if no filter is chosen
		$show_type_drawer = false;
		if ( $type_filter || empty( $active_filters ) ) {
			$show_type_drawer = true;
		}

		?>
		<div id="docs-filter-section-channels" class="docs-filter-section<?php if ( $channel_filter ) : ?> docs-filter-section-open<?php endif; ?>">
			<ul id="channel-list" class="no-bullets horizontal category-links">
			<?php if ( ! empty( $categories ) ) : ?>
				<?php foreach ( $categories as $cat ) : ?>
					<li>
						<?php echo $this->get_taxonomy_filter_link( 'bpd_channel', $cat ); ?>
					</li>
				<?php endforeach ?>
			<?php else: ?>
				<li><?php _e( 'No channels to show.', $this->plugin_name )  ?></li>
			<?php endif; ?>
			</ul>
		</div>

		<div id="docs-filter-section-types" class="docs-filter-section<?php if ( $show_type_drawer ) : ?> docs-filter-section-open<?php endif; ?>">
			<ul id="types-list" class="no-bullets horizontal mrad-types-filter-list">
			<?php if ( ! empty( $existing_types ) ) : ?>
				<?php foreach ( $existing_types as $term ) : ?>
					<li>
						<?php echo $this->get_taxonomy_filter_link( 'bpd_type', $term ); ?>
					</li>
				<?php endforeach ?>
			<?php else: ?>
				<li><?php _e( 'No types to show.', $this->plugin_name )  ?></li>
			<?php endif; ?>
			</ul>
		</div>

		<?php
	}

	/**
	 * Creates the markup for the doc types filter links on the docs loop.
	 *
	 * @since 1.0.0
	 *
	 * @return string html for filter controls.
	 */
	public function filter_title_class( $current, $filter_type ) {
		$active_filters = $this->get_active_filters();

		if ( empty( $active_filters ) && $filter_type['slug'] == 'types' ) {
			$current = ' current';
		}

		return $current;
	}

	/**
	 * Creates the markup for the doc types filter links on the docs loop.
	 *
	 * @since 1.0.0
	 *
	 * @return array of the slug for currently active filters.
	 */
	public function get_active_filters() {
		$filter_types = apply_filters( 'bp_docs_filter_types', array() );
		$active_filters = array();

		foreach ( $filter_types as $filter ) {
			if ( isset( $_GET[ $filter['query_arg'] ] ) ) {
				$active_filters[] = $filter['slug'];
			}
		}

		return $active_filters;
	}

	/**
	 * Modifies the tax_query on the doc loop to account for doc types & categories.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tax_query a WP-ready tax_query array.
	 * @return array $tax_query a WP-ready tax_query array.
	 */
	public function types_query_filter( $tax_query ) {
		$main_class = CC_MRAD::get_instance();
		$check_operator = false;

		// Check for the existence tag filters in the request URL
		if ( ! empty( $_REQUEST['bpd_type'] ) ) {
			// The bpd_tag argument may be comma-separated
			$types = explode( ',', urldecode( $_REQUEST['bpd_type'] ) );

			// Clean up the input
			$types = array_map( 'esc_attr', $types );

			$tax_query[] = array(
				'taxonomy'	=> $main_class->get_taxonomy_name(),
				'terms'		=> $types,
				'field'		=> 'slug',
			);

			$check_operator = true;
		}

		if ( ! empty( $_REQUEST['bpd_channel'] ) ) {
			// The bpd_channel argument may be comma-separated
			$channels = explode( ',', urldecode( $_REQUEST['bpd_channel'] ) );

			// Clean up the input
			$types = array_map( 'esc_attr', $types );

			$tax_query[] = array(
				'taxonomy'	=>'category',
				'terms'		=> $channels,
				'field'		=> 'slug',
			);

			$check_operator = true;
		}

		if ( $check_operator ) {
			if ( ! empty( $_REQUEST['bool'] ) && $_REQUEST['bool'] == 'and' ) {
				$tax_query['operator'] = 'AND';
			}
		}

		return $tax_query;
	}

	/**
	 * Generate taxonomy-filter links for our various directory taxonomy filters.
	 *
	 * @since 1.0.0
	 *
	 * @return array $terms The item's terms
	 */
	function get_taxonomy_filter_link( $taxonomy_arg = 'bpd_type', $term = '' ) {
		global $bp;

		if ( bp_is_user() ) {
			$current_action = bp_current_action();
			if ( empty( $current_action ) || BP_DOCS_STARTED_SLUG == $current_action ) {
				$item_docs_url = trailingslashit( bp_displayed_user_domain() . bp_docs_get_docs_slug() .  '/' . BP_DOCS_STARTED_SLUG );
			} elseif ( BP_DOCS_EDITED_SLUG == $current_action ) {
				$item_docs_url = trailingslashit( bp_displayed_user_domain() . bp_docs_get_docs_slug() .  '/' . BP_DOCS_EDITED_SLUG );
			}
		} elseif ( bp_is_group() ){
			$item_docs_url = trailingslashit( bp_get_group_permalink() . bp_docs_get_docs_slug() );
		} else {
			$item_docs_url = bp_docs_get_archive_link();
		}

		$url = add_query_arg( $taxonomy_arg, urlencode( $term->slug ), $item_docs_url );

		$html = '<a href="' . $url . '" title="' . sprintf( __( 'Items tagged %s', $this->plugin_name ), esc_attr( $term->name ) ) . '">';
		if ( $taxonomy_arg == 'bpd_type' ) {
			// Add the icons to the list in this case.
			switch ( $term->slug ) {
				case 'map':
					$html .= '<span class="mapx24 icon"></span>';
					break;
				case 'report':
					$html .= '<span class="reportx24 icon"></span>';
					break;
				default:
					$html .= ' <span class="collaborationx24 icon"></span>';
					break;
			}
		}
		$html .= esc_html( $term->name ) . '</a>';

		return apply_filters( 'cc_mrad_get_taxonomy_filter_link', $html, $url, $term, $taxonomy_arg );
	}

	/**
	 * Add type- and channel-related filters to the list of current directory filters.
	 *
	 * @since 1.0.0
	 *
	 * @param array $filters
	 * @return array
	 */
	function add_tax_filters( $filters ) {
		// Are we filtering by type?
		if ( ! empty( $_REQUEST['bpd_type'] ) ) {
			// The bpd_type argument may be comma-separated
			$types = explode( ',', urldecode( $_REQUEST['bpd_type'] ) );

			foreach ( $types as $type ) {
				$filters['types'][] = $type;
			}
		}

		// Are we filtering by channel?
		if ( ! empty( $_REQUEST['bpd_channel'] ) ) {
			// The bpd_channel argument may be comma-separated
			$channels = explode( ',', urldecode( $_REQUEST['bpd_channel'] ) );

			foreach ( $channels as $channel ) {
				$filters['channels'][] = $channel;
			}
		}

		return $filters;
	}

	/**
	 * Modifies the info header message to account for current filters.
	 *
	 * @since 1.0.0
	 *
	 * @param array $message An array of the messages explaining the current view
	 * @param array $filters The filters pulled out of the $_REQUEST global
	 *
	 * @return array $message The maybe modified message array
	 */
	function info_header_message( $message, $filters ) {
		// Check for the existence of our filters in the request URL.
		if ( ! empty( $filters['types'] ) || ! empty( $filters['channels'] ) ){
			$main_class = CC_MRAD::get_instance();
		}

		if ( ! empty( $filters['types'] ) ) {
			$tagtext = array();

			foreach ( $filters['types'] as $type ) {
				$term = get_term_by( 'slug', $type, $main_class->get_taxonomy_name() );
				$tagtext[] = $this->get_taxonomy_filter_link( 'bpd_type', $term );
			}

			$message[] = sprintf( __( 'You are viewing items with the type: %s', 'bp-docs' ), implode( ', ', $tagtext ) );
		}

		if ( ! empty( $filters['channels'] ) ) {
			$tagtext = array();

			foreach ( $filters['channels'] as $channel ) {
				$term = get_term_by( 'slug', $channel, 'category' );
				$tagtext[] = $this->get_taxonomy_filter_link( 'bpd_channel', $term );
			}

			$message[] = sprintf( __( 'You are viewing items in the channel: %s', 'bp-docs' ), implode( ', ', $tagtext ) );
		}

		return $message;
	}

	/**
	 * Prefix the title with the doc type if it's not a regular doc.
	 *
	 * @since 1.0.0
	 *
	 * @param $title The title of the post
	 * @param $post_id The post's ID.
	 * @return array $terms The item's terms
	 */
	public function add_doc_type_to_title( $title, $post_id = 0 ) {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		if ( ! empty( $post_id ) && bp_docs_get_post_type_name() == get_post_type( $post_id ) ) {
			$main_class = CC_MRAD::get_instance();
			$taxonomy = $main_class->get_taxonomy_name();
			$terms = wp_get_post_terms( $post_id, $taxonomy );

			if ( ! empty( $terms ) ) {
				$term_name = current( $terms )->name;
				if ( $term_name != 'Doc' ) {
					$title = $term_name . ': ' . $title;
				}
			}
		}

		return $title;
	}

	/**
	 * Change the doc's genericon if it's not a regular doc.
	 *
	 * @since 1.0.0
	 *
	 * @param string $icon_markup The genericon string of the icon.
	 * @param string $glyph_name The genericon id of the icon.
	 * @param string $object_id The ID of the object we're genericoning.
	 * @return string $icon_markup The genericon string of the icon.
	 */
	public function filter_bp_docs_get_genericon( $icon_markup, $glyph_name, $object_id ) {
		// We want to add granularity to the "document" icon.
		if ( $glyph_name == 'document' ) {
			// $main_class = CC_MRAD::get_instance();
			// $taxonomy = $main_class->get_taxonomy_name();
			$terms = wp_get_post_terms( $object_id, $this->type_taxonomy_name);
			$doc_type = ! empty( $terms ) ? current( $terms )->slug : 'doc';

			// Add the icons.
			switch ( $doc_type ) {
				case 'map':
					$icon_markup = '<span class="mapx24 icon"></span>';
					break;
				case 'report':
					$icon_markup = '<span class="reportx24 icon"></span>';
					break;
				case 'area':
					// @TODO: add an icon
					$icon_markup = '<span class="mapx24 icon"></span>';
					break;
				default:
					$icon_markup = ' <span class="collaborationx24 icon"></span>';
					break;
			}
		}

		return $icon_markup;
	}

	/**
	 * Add a submenu to the docs create button.
	 *
	 * @since 1.0.0
	 *
  	 * @param string $button Create doc button markup.
	 * @return array $button Markup for the create button
	 */
	public function filter_bp_docs_create_button( $button ) {
		ob_start();
		?>
		<div id="bp-create-doc-button-menu" class="bp-create-doc-button-nav-container">
			<ul class="nav accessible-menu no-bullets">
			<li class="alignright menu-item menu-item-level-0 menu-item-has-children">
				<?php echo $button; ?>
				<div id="mrad_create_doc_options_panel" class="sub-nav">
					<ul class="sub-nav-group mrad_create_doc_options_list no-bullets">
						<li class="menu-item">
							<a href="<?php bp_docs_create_link(); ?>" title="Create a collaborative document"><span class="collaborationx24 icon"></span>Create a Collaborative Doc</a>
						</li>
						<li class="menu-item">
							<a href="<?php echo mrad_map_create_link_url(); ?>" title="Create a map"><span class="mapx24 icon"></span>Create a Map</a>
						</li>
						<li class="menu-item">
							<a href="<?php echo mrad_report_create_link_url(); ?>" title="Create a report"><span class="reportx24 icon"></span>Create a Report</a>
						</li>
						<?php /* ?>
						<li class="menu-item">
							<a href="<?php echo mrad_area_create_link_url(); ?>" title="Create an area"><span class="mapx24 icon"></span>Create an Area</a>
						</li>
						<?php */ ?>
					</ul>
				</div>
			</li>
			</ul>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Filter the doc edit link for a single doc.
	 *
	 * @since 1.0.0
	 *
 	 * @param string $link Edit link for the doc.
	 * @return string Edit link for the type of doc that it is.
	 */
	public function filter_bp_docs_get_doc_edit_link( $link ) {

		// What Doc are we talking about?
		if ( is_singular( bp_docs_get_post_type_name() ) && $q = get_queried_object() ) {
			$doc_id = isset( $q->ID ) ? $q->ID : 0;
		} else if ( get_the_ID() ) {
			$doc_id = get_the_ID();
		}

		$main_class = CC_MRAD::get_instance();
		$terms = wp_get_post_terms( $doc_id, $main_class->get_taxonomy_name() );

		// We're assuming that there's only one "type" associated with a doc.
		$slug = '';
		if ( ! empty( $terms ) ) {
			$slug = current( $terms )->slug;
		}

		switch ( $slug ) {
			case 'map':
				$link = mrad_map_open_link_url( $doc_id );
				break;
			case 'report':
				$link = mrad_report_open_link_url( $doc_id );
				break;
			case 'area':
				$link = mrad_area_open_link_url( $doc_id );
				break;
			default:
				// Do nothing.
				break;
		}

		return $link;
	}

	/**
	 * Shows a doc's channels on the single doc view.
	 *
	 * @since 1.0.0
	 *
 	 * @param string $html Calculated markup for tag links.
	 * @param string $tag_array Tags for the doc.
 	 * @return string html markup
	 */
	function add_channels_single_doc( $html, $tag_array ) {
		$categories = wp_get_post_terms( get_the_ID(), 'category' );
		$cat_array = array();
		$output = '';

	    foreach ( $categories as $cat ) {
	    	$cat_array[] = $this->get_taxonomy_filter_link( $taxonomy_arg = 'bpd_channel', $cat );
	    }

		if ( ! empty( $cat_array ) ) {
			$categories_list = implode( ' ', $cat_array );
			$output .= 'Channels <span class="category-links">'. $categories_list . '</span> <br />';
		}
		if ( ! empty( $tag_array ) ) {
			$tags_list = implode( ' ', $tag_array );
			$output .= 'Tags <span class="tag-links">'. $tags_list . '</span> <br />';
		}

		return '<footer class="entry-meta">' . $output . '</footer>';
	}

	/**
	 * Shows a doc's channels on the docs table view.
	 *
	 * @since 1.0.0
	 *
 	 * @return string html markup
	 */
	function add_channels_docs_loop() {
		$categories = wp_get_post_terms( get_the_ID(), 'category' );
		$cat_array = array();
		$output = '';

	    foreach ( $categories as $cat ) {
	    	$cat_array[] = $this->get_taxonomy_filter_link( $taxonomy_arg = 'bpd_channel', $cat );
	    }

		if ( ! empty( $cat_array ) ) {
			$categories_list = implode( ' ', $cat_array );
			$output .= '<span class="category-links">'. $categories_list . '</span> <br />';
		}

		echo '<footer class="entry-meta">' . $output . '</footer>';
	}

	/**
	 * Shows a doc's channels on the docs table view.
	 *
	 * @since 1.0.0
	 *
	 * @param string $html Calculated markup for tag links.
	 * @param string $tag_array Tags for the doc.
	 * @return string html markup
	 */
	function change_tags_output( $html, $tag_array ) {

		if ( ! empty( $tag_array ) ) {
			$tags_list = implode( ' ', $tag_array );
			$html = '<div class="entry-meta"><span class="tag-links">'. $tags_list . '</span></div>';
		}

		return $html;
	}

	/**
	 * Markup for the Channels meta box on the docs edit screen
	 *
	 * @since 1.0.0
	 *
	 * @param int ID of the doc
	 *
	 * @return string html markup
	 */
	public function docs_edit_channels_metabox( $doc_id ) {
		// require_once(ABSPATH . '/wp-admin/includes/template.php');
		?>
		<div id="doc-channel" class="doc-meta-box">
			<div class="toggleable <?php bp_docs_toggleable_open_or_closed_class(); ?>">
				<p id="channel-toggle-edit" class="toggle-switch">
					<span class="hide-if-js toggle-link-no-js"><?php _e( 'Channels', $this->plugin_name ); ?></span>
					<a class="hide-if-no-js toggle-link" id="channel-toggle-link" href="#"><span class="show-pane plus-or-minus"></span><?php _e( 'Channels', $this->plugin_name ); ?></a>
				</p>

				<div class="toggle-content">
					<table class="toggle-table" id="toggle-table-channel">
						<tr>
							<td class="desc-column">
								<label for="bp_docs_channel"><?php _e( 'Select the channels that describe your item.', $this->plugin_name ) ?></label>
							</td>

							<td>
								<?php
								//wp_category_checklist( $doc_id );
								$categories = get_terms( 'category' );
								$selected_cats = wp_list_pluck( get_the_terms( $doc_id, 'category' ), 'term_id' );
								// var_dump( $selected_cats );
								if ( ! empty( $categories ) ) :
								?>
									<ul class="no-bullets horizontal">
										<?php
											foreach ( $categories as $category ) {
												$selected_cat = in_array( $category->term_id, $selected_cats) ? true : false;
											?>
											<li id="category-<?php echo $category->term_id; ?>"><label class="selectit"><input value="<?php echo $category->term_id; ?>" type="checkbox" name="post_category[]" id="in-category-<?php echo $category->term_id; ?>" <?php checked( $selected_cat ); ?>> <?php echo $category->name; ?></label></li>
											<?php
										}
										?>
									</ul>
								<?php
								endif; //if ( ! empty( $categories ) )
								?>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Save channels selected from the front end.
	 *
	 * @since 1.0.0
	 *
	 * @param $query Docs_Query instance.
	 */
	public function save_channel_selection( $query ) {
		// Separate out the terms
		$terms = ! empty( $_POST['post_category'] ) ? array_map( 'intval', $_POST['post_category'] ) : array();

		if ( ! empty( $terms ) ) {
			wp_set_post_terms( $query->doc_id, $terms, 'category' );
		}

	}

	/**
	 * Apply the "standard" type to non-report and non-map docs.
	 * At the point in the save cycle of the `bp_docs_doc_saved` action, we don't
	 * know what kind of doc we're working with. We'll set "doc" for all docs,
	 * and unset it if the doc is a map or report.
	 *
	 * @since 1.0.0
	 *
	 * @param $query Docs_Query instance.
	 */
	public function save_doc_type( $query ) {

		wp_set_object_terms( $query->doc_id, 'doc', $this->type_taxonomy_name );

	}

	/**
	 * If type is a map or report, we show the map or report with the description below.
	 * Add a target div for the map to be built in.
	 *
	 * @since 1.0.0
	 *
	 * @return string The filtered content string.
	 */
	public function filter_bp_docs_get_the_content( $content ) {
		$doc = get_queried_object();

		if ( ! empty( $doc ) ) {
			// $mrad_class = CC_MRAD::get_instance();
			$terms = wp_get_object_terms( $doc->ID, $this->type_taxonomy_name );
			$item_type = '';
			$meta_query_key = '';

			if ( ! empty( $terms ) ) {
				$item_type = current( $terms )->slug;
				switch ( $item_type ) {
					case 'map':
						// $meta_query_key = 'map_table_ID';
						// $item_id = get_post_meta( $doc->ID, 'map_table_ID', true );

						// We add a map widget container above the post content.
						$widget = '<div id="map-widget-container" ></div>';
						$content = $widget . '<br /> ' . $content;
						break;
					case 'report':
						// $meta_query_key = 'report_table_ID';

						// We'll need to fetch the item details to generate the "open report" link.
						$item_id = get_post_meta( $doc->ID, 'report_table_ID', true );
						$item = $this->get_single_map_report( $item_id, $item_type );

						if ( ! empty( $item ) ) {
							if ( $item['reportType'] == 'PDF' ) {
								$button_text = __( 'View PDF', $this->plugin_name );
							} elseif ( $item['reportType'] == 'Word' ) {
								// @TODO: I'm not sure about this, since I can't save a Word doc
								$button_text = __( 'Download Word Doc', $this->plugin_name );
							} else {
								$button_text = __( 'Open Report', $this->plugin_name );
							}

							$report_link = '<a href="' . $item['link'] . '" title="Link to report" class="button report-link"><span class="icon reportx24"></span>' . $button_text . '</a>';
							$content = $report_link . '<br /> ' . $content;

						}
					case 'area':
						// We add a map widget container above the post content.
						$widget = '<div id="map-widget-container" ></div>';
						$content = $widget . '<br /> ' . $content;

						// We'll need to fetch the item details to generate the "open report" link.
						$item_id = get_post_meta( $doc->ID, 'area_table_ID', true );
						$item = $this->get_single_map_report( $item_id, $item_type );

						if ( ! empty( $item ) ) {
							$button_text = __( 'Open Report Using This Area', $this->plugin_name );

							$report_link = '<a href="' . $item['link'] . '" title="Link to area" class="button report-link"><span class="icon reportx24"></span>' . $button_text . '</a>';
							$content = $report_link . '<br /> ' . $content;

						}
					default:
						$meta_query_key = '';
						break;
				}
			}
		}

		// $towrite = PHP_EOL . 'in filter_bp_docs_get_the_content.';
		// $towrite .= PHP_EOL . '$doc object: ' . print_r( $doc, TRUE );
		// $towrite .= PHP_EOL . '$terms object: ' . print_r( $terms, TRUE );
		// $fp = fopen('bp-docs-templating.txt', 'a');
		// fwrite($fp, $towrite);
		// fclose($fp);
		return $content;
	}

	/**
	 * Create the map target url and add it to the page as a js variable for use by other js.
	 *
	 * @since 1.0.0
	 *
	 * @return string <script> block containing the needed js var.
	 */
	public function add_map_widget_injector() {
		$doc_id = get_the_ID();
		$output = '';

		if ( ! empty( $doc_id ) ) {
			$terms = wp_get_object_terms( $doc_id, $this->type_taxonomy_name );
			$item_type = '';
			$meta_query_key = '';

			if ( ! empty( $terms ) ) {
				$item_type = current( $terms )->slug;
				switch ( $item_type ) {
					case 'map':
						$item_id = get_post_meta( $doc_id, 'map_table_ID', true );
						// We add a map widget above the post content.
						$output = '<script type="text/javascript">
							var base_map_widget_src = "' . mrad_map_base_url() . 'jscripts/mapWidget.js?mapid='. $item_id . '&style=responsive";
							</script>';
						break;
					case 'report':
						$meta_query_key = 'report_table_ID';
						break;
					case 'area':
						$item_id = get_post_meta( $doc_id, 'area_table_ID', true );
						$area = $this->get_single_map_report( $item_id, 'area' );
						// We add a map widget above the post content.
						$output = '<script type="text/javascript">
							var base_map_widget_src = "' . $area['mapthumb'] .  '";
							</script>';
						break;
					default:
						$meta_query_key = '';
						break;
				}
			}
		}
		if ( ! empty( $output ) ) {
			echo $output;
		}
	}

	/**
	 * Insert maps and reports on channel pages.
	 *
	 * @since 1.0.0
	 *
	 * @param int $category_id ID of the category term of the displayed archive.
	 * @param int $category_name Name of the category term of the displayed archive.
	 */
	public function add_featured_map_to_channel_page( $category_id, $category_name ) {
		$properties = $this->get_channel_block_properties( $category_id, $category_name );

		$items = array();
		if ( $properties['maps_use_keyword'] ) {
			$items = $this->get_featured_items( 'map', 1, $properties['search_terms'] );
		}

		if ( empty( $items ) ) {
			// Fallback to most recent featured if no results
			$items = $this->get_featured_items( 'map', 1 );
		}

		$item = ( $items ) ? $items[0] : array();
		if ( ! empty( $item ) ) :
			?>
			<div class="half-block card flex">
				<span class="corner-ribbon">
					<span class="mapx24-white" style="display:block;"></span>
				</span>
				<a href="<?php echo $item['link'] ?>"></a>
				<script src='http://maps.communitycommons.org/jscripts/mapWidget.js?mapid=<?php echo $item['id']; ?>&w=600&h=300&bbox=<?php echo $item['mapbbox']; ?>&style=responsive'></script>
				<div class="entry-content">
					<h3 class="entry-title small"><a href="<?php echo $item['link'] ?>"><?php echo $item['title']; ?></a></h3>
					<p class="meta"><em>Created by</em> <?php echo bp_core_get_userlink( $item['owner'] ); ?></p>

					<a href="<?php echo $properties['more_maps_url']; ?>"><?php echo $properties['more_maps_label']; ?></a>
				</div>
			</div>
			<?php
		endif;
	}

	/**
	 * Insert maps and reports on channel pages.
	 *
	 * @since 1.0.0
	 *
	 * @param int $category_id ID of the category term of the displayed archive.
	 * @param int $category_name Name of the category term of the displayed archive.
	 */
	function add_featured_report_to_channel_page( $category_id, $category_name ) {
		$properties = $this->get_channel_block_properties( $category_id, $category_name );
		?>
		<div class="half-block card flex">
			<span class="corner-ribbon">
				<span class="reportx24-white" style="display:block;"></span>
			</span>
			<a href="http://assessment.communitycommons.org/CHNA/SelectArea.aspx?reporttype=<?php echo $properties['report_topic']; ?>"><img src="<?php echo $this->get_report_block_header_image_url( $category_id ); ?>"></a>
			<div class="entry-content">
				<p>Use our reporting tools to better understand the area and people you serve.</p>
				<p class="meta"><a href="http://assessment.communitycommons.org/CHNA/SelectArea.aspx?reporttype=<?php echo $properties['report_topic']; ?>">Start a new <?php echo $properties['report_label']; ?>.</a></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Helper function to determine widget characteristics based on category.
	 *
	 * @since 1.0.0
	 *
	 * @param int $category_id ID of the category term of the displayed archive.
	 * @param int $category_name Name of the category term of the displayed archive.
	 */
	function get_channel_block_properties( $category_id, $category_name ) {
		$retval = array();

		// REPORTS *****
		// There are no featured reports, but there are topic-based reports that we will direct users to.
		// From Erin:
		// Cat -> Topic-based report.
		// Health → Health Indicator Report
		// Education -->Education Indicator Report
		// Economy-->Economic Indicator Report
		// Food -->Food Environment Report
		// Environment → Physical Environment Report
		// Equity → Health Equity Assessment Report

		// The default is the “Full Report” here:
		// http://assessment.communitycommons.org/CHNA/SelectArea.aspx?reporttype=libraryCHNA
		// Topic-based reports:
		// Base URL: http://assessment.communitycommons.org/CHNA/SelectArea.aspx?reporttype=
		// Community Context: COMM
		// Economics: ECON
		// Education: EDU
		// Equity: HE
		// Food: FOOD
		// Health: HEALTH
		// Physical Environment: ENVIRO
		switch ( $category_id ) {
			case '888': // Economy
				$retval['report_topic'] = 'ECON';
				$retval['report_label'] = 'economic indicator report';
				break;
			case '891': // Food
				$retval['report_topic'] = 'FOOD';
				$retval['report_label'] = 'food environment report';
				break;
			case '889': // Education
				$retval['report_topic'] = 'EDU';
				$retval['report_label'] = 'education indicator report';
				break;
			case '897': // Environment
				$retval['report_topic'] = 'ENVIRO';
				$retval['report_label'] = 'physical environment report';
				break;
			case '58624': // Equity
				$retval['report_topic'] = 'HE';
				$retval['report_label'] = 'health equity assessment report';
				break;
			case '1308': // Health
				$retval['report_topic'] = 'HEALTH';
				$retval['report_label'] = 'health indicator report';
				break;
			case '1': // General
			case '57528': // Guest Voice
			default:
				$retval['report_topic'] = 'COMM';
				$retval['report_label'] = 'community context indicator report';
				break;
		}

		// MAPS *****
		switch ( $category_id ) {
			case '888': // Economy
				$retval['maps_use_keyword'] = true;
				$retval['search_terms'] = urlencode( 'poverty,income,economic,economy,wages,housing,SNAP' );
				$retval['more_maps_url'] = 'http://maps.communitycommons.org/gallery.aspx?search=' . $retval['search_terms'];
				$retval['more_maps_label'] = 'Browse maps on ' . strtolower( $category_name ) . '.';
				break;
			case '889': // Education
				$retval['maps_use_keyword'] = true;
				$retval['search_terms'] = urlencode( 'school,education,children,attainment,reading,math,learning,kids,youth' );
				$retval['more_maps_url'] = 'http://maps.communitycommons.org/gallery.aspx?search=' . $retval['search_terms'];
				$retval['more_maps_label'] = 'Browse maps on ' . strtolower( $category_name ) . '.';
				break;
			case '1308': // Health
				$retval['maps_use_keyword'] = true;
				$retval['search_terms'] = urlencode( 'health,access,insurance,clinical_care,medical,behaviors,outcomes,rankings,facilities,walking,mental,tobacco,dental,disease' );
				$retval['more_maps_url'] = 'http://maps.communitycommons.org/gallery.aspx?search=' . $retval['search_terms'];
				$retval['more_maps_label'] = 'Browse maps on ' . strtolower( $category_name ) . '.';
				break;
			case '891': // Food
				$retval['maps_use_keyword'] = true;
				$retval['search_terms'] = urlencode('poverty,food,access,food_environment,facilities,food_desert,farm,school_lunch,school,marketing,agriculture' );
				$retval['more_maps_url'] = 'http://maps.communitycommons.org/gallery.aspx?search=' . $retval['search_terms'];
				$retval['more_maps_label'] = 'Browse maps on ' . strtolower( $category_name ) . '.';
				break;
			case '897': // Environment
				$retval['maps_use_keyword'] = true;
				$retval['search_terms'] = urlencode( 'physical environment,environment,built environment,environmental,natural environment,natural,climate,drought,water,soil' );
				$retval['more_maps_url'] = 'http://maps.communitycommons.org/gallery.aspx?search=' . $retval['search_terms'];
				$retval['more_maps_label'] = 'Browse maps on ' . strtolower( $category_name ) . '.';
				break;
			case '58624': // Equity
				$retval['maps_use_keyword'] = true;
				$retval['search_terms'] = urlencode( 'race,diversity,social justice,ethnicity,inequality,equity,equality,rural,housing' );
				$retval['more_maps_url'] = 'http://maps.communitycommons.org/gallery.aspx?search=' . $retval['search_terms'];
				$retval['more_maps_label'] = 'Browse maps on ' . strtolower( $category_name ) . '.';
				break;
			case '1': // General
			case '57528': // Guest Voice
			default:
				$retval['maps_use_keyword'] = false;
				$retval['more_maps_url'] = 'http://maps.communitycommons.org/gallery.aspx';
				$retval['more_maps_label'] = 'Browse maps.';
				break;
		}

		return $retval;
	}

	/**
	 * Helper function to get the right image for the reports block, based on category.
	 *
	 * @since 1.0.0
	 *
	 * @param int $category_id ID of the category term of the displayed archive.
	 * @return string URL of associated image.
	 */
	public function get_report_block_header_image_url( $category_id ) {
		switch ( $category_id ) {
			case '888': // Economy
				$filename = 'economy.png';
				break;
			case '891': // Food
				$filename = 'food.png';
				break;
			case '897': // Environment
				$filename = 'environment.png';
				break;
			case '889': // Education
				$filename = 'education.png';
				break;
			case '1308': // Health
				$filename = 'health.png';
				break;
			case '58624': // Equity
			case '1': // General
			case '57528': // Guest Voice
			default:
				$filename = 'general.png';
				break;
			}

		return plugin_dir_url( __FILE__ ) . 'img/' . $filename;
	}

	/**
	 * We use a subset of the complete list of categories for maps
	 * This function excludes the unnecessary options.
	 *
	 * @since    1.0.0
	 *
	 * @return array of category objects
	 */
	function get_possible_map_categories() {
		// 1 is "uncategorized", 57528 is "guest voice"
		$exclude_cats = '1,57528';
	    $args = array(
				'hide_empty'	=> 0,
				'exclude'		=> $exclude_cats,
				);
		return get_categories( $args );
	}

	/**
	 * Add a way to get the possible map categories via JSON.
	 *
	 * @since    1.0.0
	 *
	 * @return JSON response containing info about site categories.
	 */
	public function json_get_map_categories(){
		$categories = $this->get_possible_map_categories();
		$retval = array();
		foreach ( $categories as $cat ) {
			$retval[] = array(
				'name'		=> $cat->name,
				'slug'		=> $cat->slug,
				'term_id' 	=> (int) $cat->term_id,
				);
		}

		// Send response
		header("content-type: text/javascript; charset=utf-8");
	    header("Access-Control-Allow-Origin: *");
		echo htmlspecialchars($_GET['callback']) . '(' . json_encode( $retval ) . ')';

	    exit;
	}

} // End class