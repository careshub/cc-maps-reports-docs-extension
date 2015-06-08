<?php

/**
 * The file that defines the custom post type and taxonomy we'll need for this plugin.
 *
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CC_BuddyPress_Docs_Maps_Reports_Extension
 * @subpackage CC_BuddyPress_Docs_Maps_Reports_Extension/includes
 */

/**
 * Define the custom post type and taxonomy we'll need for this plugin.
 *
 *
 * @since      1.0.0
 * @package    CC_BuddyPress_Docs_Maps_Reports_Extension
 * @subpackage CC_BuddyPress_Docs_Maps_Reports_Extension/includes
 * @author     Your Name <email@example.com>
 */
class CC_MRAD_CPT_Tax {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The taxonomy name that we'll use.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $taxonomy_name    The identifier we'll use for our new taxonomy.
	 */
	private $taxonomy_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name       The name of the plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $taxonomy_name ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->taxonomy_name = $taxonomy_name;
	}

	/**
	 * Creates the "types" custom taxonomy for bp_docs.
	 *
	 * @since    1.0.0
	 */
	public function register_taxonomy() {

	    $labels = array(
	        'name' => _x( 'BP Doc Types', $this->plugin_name ),
	        'singular_name' => _x( 'BP Doc Type', $this->plugin_name ),
	        'search_items' => _x( 'Search Doc Types', $this->plugin_name ),
	        'popular_items' => _x( 'Popular Doc Types', $this->plugin_name ),
	        'all_items' => _x( 'All Doc Types', $this->plugin_name ),
	        'parent_item' => _x( 'Parent Doc Type', $this->plugin_name ),
	        'parent_item_colon' => _x( 'Parent Doc Type:', $this->plugin_name ),
	        'edit_item' => _x( 'Edit Doc Type', $this->plugin_name ),
	        'update_item' => _x( 'Update Doc Type', $this->plugin_name ),
	        'add_new_item' => _x( 'Add New Doc Type', $this->plugin_name ),
	        'new_item_name' => _x( 'New Doc Type', $this->plugin_name ),
	        'separate_items_with_commas' => _x( 'Separate doc types with commas', $this->plugin_name ),
	        'add_or_remove_items' => _x( 'Add or remove doc types', $this->plugin_name ),
	        'choose_from_most_used' => _x( 'Choose from the most used doc types', $this->plugin_name ),
	        'menu_name' => _x( 'Doc Types', $this->plugin_name ),
	    );

	    $args = array(
	        'labels' => $labels,
	        'public' => true,
	        'show_in_nav_menus' => false,
	        'show_ui' => true,
	        'show_tagcloud' => false,
	        'show_admin_column' => true,
	        'hierarchical' => true,

	        'rewrite' => true,
	        'query_var' => true
	    );

	    register_taxonomy( $this->taxonomy_name, array( bp_docs_get_post_type_name() ), $args );
	}

	/**
	 * Apply the "category" (we call them "channels") taxonomy to bp_docs.
	 *
	 * @since    1.0.0
	 */
	public function apply_channels_to_bp_docs() {
		register_taxonomy_for_object_type( 'category', bp_docs_get_post_type_name() );
	}
}