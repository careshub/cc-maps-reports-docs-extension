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
			    //@TODO: Set the channel, too.
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

	/**
	 * Adds the toggle for the doc types filter links container on the docs loop.
	 *
	 * @since 1.0.0
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
	 */
	public function filter_markup() {
		$main_class = CC_MRAD::get_instance();
		$channel_filter = ! empty( $_GET['bpd_channel'] );
		$categories = get_terms( 'category' );

		$type_filter = ! empty( $_GET['bpd_type'] );
		$existing_types = get_terms( $main_class->get_taxonomy_name() );


		?>
		<div id="docs-filter-section-channels" class="docs-filter-section<?php if ( $channel_filter ) : ?> docs-filter-section-open<?php endif; ?>">
			<ul id="channel-list" class="no-bullets">
			<?php if ( ! empty( $categories ) ) : ?>
				<?php foreach ( $categories as $cat ) : ?>
					<li>
					<a href="?bpd_channel=<?php echo $cat->slug; ?>" title="<?php echo esc_html( $cat->name ) ?>"><?php echo esc_html( $cat->name  ) ?></a>
					</li>
				<?php endforeach ?>
			<?php else: ?>
				<li><?php _e( 'No channels to show.', $this->plugin_name )  ?></li>
			<?php endif; ?>
			</ul>
		</div>

		<div id="docs-filter-section-types" class="docs-filter-section<?php if ( $type_filter ) : ?> docs-filter-section-open<?php endif; ?>">
			<ul id="types-list" class="no-bullets">
			<?php if ( ! empty( $existing_types ) ) : ?>
				<?php foreach ( $existing_types as $term ) : ?>
					<li>
					<a href="?bpd_type=<?php echo $term->slug; ?>" title="<?php echo esc_html( $term->name ) ?>"><?php echo esc_html( $term->name  ) ?></a>
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
	 * Modifies the tax_query on the doc loop to account for doc types & categories.
	 *
	 * @since 1.0.0
	 *
	 * @return array $terms The item's terms
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
	 * Prefix the title with the doc type if it's not a regular doc.
	 *
	 * @since 1.0.0
	 *
	 * @return array $terms The item's terms
	 */
	public function add_doc_type_to_title( $title, $post_id ) {

		if ( bp_docs_is_global_directory() || bp_docs_is_mygroups_directory() ) {
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
	 * Add a submenu to the docs create button.
	 *
	 * @since 1.0.0
	 *
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
				    </ul>
			    </div>
			</li>
			</ul>
		</div>
	    <?php
		return ob_get_clean();

		// return $button;
	}

	/**
	 * Filter the location of the docs header template.
	 *
	 * @since 1.0.0
	 *
	 * @return string Location of the template to use.
	 */
	public function filter_bp_docs_header_template( $template ) {
	    // $towrite = PHP_EOL . 'incoming_template' . print_r( $template, TRUE );
      	$template_directory = $this->get_template_directory();
	    $template = $template_directory . "docs/docs-header.php";

	    // $towrite .= PHP_EOL . 'filtered_template' . print_r( $template, TRUE );
	    // $fp = fopen('filter_bp_docs_header_template.txt', 'a');
	    // fwrite($fp, $towrite);
	    // fclose($fp);

		return $template;
	}

	/**
	 * Filter the doc edit link for a single doc.
	 *
	 * @since 1.0.0
	 *
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
			default:
				// Do nothing.
				break;
		}

	    $towrite = PHP_EOL . 'doc id' . print_r( $doc_id, TRUE );
	    $towrite .= PHP_EOL . 'filtered edit link' . print_r( $link, TRUE );
	    // $towrite .= PHP_EOL . 'terms' . print_r( $terms, TRUE );
	    $fp = fopen('filter_bp_docs_get_doc_edit_link.txt', 'a');
	    fwrite($fp, $towrite);
	    fclose($fp);

		return $link;
	}

	/**
	 * Markup for the Channels <th> on the docs loop
	 *
	 * @since 1.0.0
	 */

	function channels_th() {
		?>

		<th scope="column" class="channels-cell"><?php _e( 'Channels', $this->plugin_name ); ?></th>

		<?php
	}

	/**
	 * Markup for the Channels <td> on the docs loop
	 *
	 * @since 1.0.0
	 */
	function channels_td() {

		//TODO: maybe add these to the title cell instead?

		$channels = get_the_terms( get_the_ID(), 'category' );
		$output = array();

		foreach ( (array) $channels as $cat ) {
			if ( ! empty( $cat->name ) ) {
				$output[] = '<a href="?bpd_channel=' . $cat->slug . '" title="' . esc_html( $cat->name ) . '">' . esc_html( $cat->name  ) . '</a>';
			}
		}

		?>

		<td class="channels-cell">
			<?php echo implode( ', ', $output ) ?>
		</td>

		<?php
	}

	/**
	 * Markup for the Channels meta box on the docs edit screen
	 *
	 * @since 1.0.0
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
	 */

	public function save_channel_selection( $query ) {
		// Separate out the terms
		$terms = ! empty( $_POST['post_category'] ) ? array_map( 'intval', $_POST['post_category'] ) : array();

		if ( ! empty( $terms ) ) {
			wp_set_post_terms( $query->doc_id, $terms, 'category' );
		}

	    $towrite = PHP_EOL . '$query: ' . print_r( $query, TRUE );
	    $towrite .= PHP_EOL . '$_POST[post_category]: ' . print_r( $_POST['post_category'], TRUE );
	    $towrite .= PHP_EOL . '$terms: ' . print_r( $terms, TRUE );
	    $fp = fopen('save-doc-channels.txt', 'a');
	    fwrite($fp, $towrite);
	    fclose($fp);
	}

	public function filter_found_template( $template_path, $that ){
	    $towrite = PHP_EOL . 'in filter_found_template.';
	    $towrite .= PHP_EOL . '$template_path: ' . print_r( $template_path, TRUE );
	    $towrite .= PHP_EOL . '$that: ' . print_r( $that, TRUE );
	    $fp = fopen('filter_found_template.txt', 'a');
	    fwrite($fp, $towrite);
	    fclose($fp);

	    return $template_path;
	}

	public function filter_bp_docs_locate_template( $template_path, $template ) {
	    $towrite = PHP_EOL . 'in filter_bp_docs_locate_template.';
	    $towrite .= PHP_EOL . '$template_path: ' . print_r( $template_path, TRUE );
	    $towrite .= PHP_EOL . '$that: ' . print_r( $that, TRUE );
	    $fp = fopen('filter_found_template.txt', 'a');
	    fwrite($fp, $towrite);
	    fclose($fp);

	    return $template_path;
	}

	public function filter_bp_docs_template_include( $template ) {
	    $towrite = PHP_EOL . 'in filter_bp_docs_template_include.';
	    $towrite .= PHP_EOL . '$template: ' . print_r( $template, TRUE );
	    $fp = fopen('filter_found_template.txt', 'a');
	    fwrite($fp, $towrite);
	    fclose($fp);

	    return $template;
	}

	public function filter_bp_docs_get_the_content( $content ) {
		$doc = get_queried_object();

		if ( ! empty( $doc ) ) {
			$mrad_class = CC_MRAD::get_instance();
			$terms = wp_get_object_terms( $doc->ID, $mrad_class->get_taxonomy_name() );
			$item_type = '';
			$meta_query_key = '';

			if ( ! empty( $terms ) ) {
				$item_type = current( $terms )->slug;
				switch ( $item_type ) {
					case 'map':
				        // $meta_query_key = 'map_table_ID';
				        $item_id = get_post_meta( $doc->ID, 'map_table_ID', true );
				        break;
				    case 'report':
					    $meta_query_key = 'report_table_ID';
					default:
						$meta_query_key = '';
						break;
				}
			}

			if ( 'map' == $item_type ) {
				// We add a map widget above the post content.
				// $widget = '<script  id="map_widget_generator" src="http://maps.communitycommons.org/jscripts/mapWidget.js?mapid='. $item_id . '&style=responsive"></script>';
				$widget = '<div id="map-widget-container" ></div>';
				$content = $widget . '<br /> ' . $content;

			} elseif ( 'report' == $item_type ) {
				# code...
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

	public function add_map_widget_injector() {
		$doc_id = get_the_ID();
		$output = '';

		if ( ! empty( $doc_id ) ) {
			$mrad_class = CC_MRAD::get_instance();
			$terms = wp_get_object_terms( $doc_id, $mrad_class->get_taxonomy_name() );
			$item_type = '';
			$meta_query_key = '';

			if ( ! empty( $terms ) ) {
				$item_type = current( $terms )->slug;
				switch ( $item_type ) {
					case 'map':
				        // $meta_query_key = 'map_table_ID';
				        $item_id = get_post_meta( $doc_id, 'map_table_ID', true );
				        break;
				    case 'report':
					    $meta_query_key = 'report_table_ID';
					default:
						$meta_query_key = '';
						break;
				}
			}

			if ( 'map' == $item_type ) {
				// We add a map widget above the post content.
				$output = '<script type="text/javascript">
				var base_map_widget_src = "http://maps.communitycommons.org/jscripts/mapWidget.js?mapid='. $item_id . '&style=responsive";
				</script>';
			} elseif ( 'report' == $item_type ) {
				# code...
			}

		}
		if ( ! empty( $output ) ) {
			echo $output;
		}
	}

} // End class