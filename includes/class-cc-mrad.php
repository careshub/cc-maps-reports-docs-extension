<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CC_BuddyPress_Docs_Maps_Reports_Extension
 * @subpackage CCC_BuddyPress_Docs_Maps_Reports_Extension/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    CC_BuddyPress_Docs_Maps_Reports_Extension
 * @subpackage CC_BuddyPress_Docs_Maps_Reports_Extension/includes
 * @author     Your Name <email@example.com>
 */
class CC_MRAD {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Plugin_Name_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

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
	 * The plugin's slug.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_slug    The string that is the plugin's slug.
	 */
	protected $plugin_slug;

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * The taxonomy name that we'll use.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $taxonomy_name    The identifier we'll use for our new taxonomy.
	 */
	private $taxonomy_name;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'cc-mrad';
		$this->plugin_slug = 'cc-mrad';
		$this->version = '1.0.0';
		$this->taxonomy_name = 'bp_docs_type';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
	 * - Plugin_Name_i18n. Defines internationalization functionality.
	 * - Plugin_Name_Admin. Defines all hooks for the dashboard.
	 * - Plugin_Name_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for setting up the custom post type and taxonomy.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cc-mrad-cpt-tax.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cc-group-pages-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cc-mrad-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the Dashboard.
		 */
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cc-mrad-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cc-mrad-public.php';

		/**
		 * The method for actually saving the BP Doc, based closely on BP Docs, and maybe replaceable later.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/bpdocs-save-methods.php';
		/**
		 * Random functions.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cc-mrad-functions.php';
		/**
		 * The templates file.
		 */
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/cc-mrad-public-display.php';


		// $this->loader = new CC_Group_Pages_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new CC_MRAD_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );

	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		// $plugin_admin = new CC_MRAD_Admin( $this->get_plugin_name(), $this->get_version() );
		// add_action( 'admin_menu', array( $plugin_admin, 'setup_menus' ) );
		// add_action( 'admin_menu', array( $plugin_admin, 'setup_settings' ) );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		// Register the custom taxonomy
		$cpt_tax_class = new CC_MRAD_CPT_Tax( $this->get_plugin_name(), $this->get_version(), $this->get_taxonomy_name() );
		add_filter( 'bp_init', array( $cpt_tax_class, 'register_taxonomy') );
		// Apply the "category" (we call them "channels") taxonomy to bp_docs.
		add_filter( 'bp_init', array( $cpt_tax_class, 'apply_channels_to_bp_docs'), 12 );

		$plugin_public = new CC_MRAD_Public( $this->get_plugin_name(), $this->get_version() );
		// Add wp_ajax listeners to accept incoming information about map activity.
		// 'nopriv' is used when the user isn't logged in, so we shouldn't use it.
		// add_action( 'wp_ajax_nopriv_cc-update-maps-reports', array( $plugin_public, 'json_update_maps_reports' ) );
		add_action( 'wp_ajax_cc-update-maps-reports', array( $plugin_public, 'json_update_maps_reports' ) );

		// Maps and reports don't have a "trash" analog, so when one is deleted, we really delete it here, too.
		add_action( 'bp_docs_doc_deleted', array( $plugin_public, 'permanently_delete_maps_reports') );

		// When a doc is deleted, if it's a map or report, we have to ping the map/report environment, too.
		// We're not deleting items here. Deletes should happen on map/report env.
		// add_action( 'delete_post', array( $plugin_public, 'ping_map_env_on_doc_delete') );

		// When a doc is removed from a group, ping the maps/reports environment
		add_action( 'bp_docs_doc_unlinked_from_group', array( $plugin_public, 'ping_map_env_on_doc_unlink_from_group'), 10, 2 );

		// If type is a map or report, we show the map or report with the description below.
		// Add a target div for the map to be built in.
		add_filter( 'bp_docs_get_the_content', array( $plugin_public, 'filter_bp_docs_get_the_content' ) );
		// Create the map target url and add it to the page as a js variable for use by other js.
		add_action( 'bp_docs_after_doc_content', array( $plugin_public, 'add_map_widget_injector' ) );

		// Add the "types" filter markup
		add_filter( 'bp_docs_filter_types', array( $plugin_public, 'add_filter_toggle' ) );
		add_filter( 'bp_docs_filter_sections', array( $plugin_public, 'filter_markup' ) );

		// Set the "types" filter toggle to current if no filter is selected.
		add_filter( 'bp_docs_filter_title_class', array( $plugin_public, 'filter_title_class' ), 10, 2 );

		// Add "type" and "channel" to the current filters when viewing the docs directory.
		add_filter( 'bp_docs_get_current_filters', array( $plugin_public, 'add_tax_filters' ) );
		// Add some header info when a filter is selected
		add_filter( 'bp_docs_info_header_message', array( $plugin_public, 'info_header_message' ), 11, 2 );
		// Modify the main tax_query in the doc loop
		add_filter( 'bp_docs_tax_query', array( $plugin_public, 'types_query_filter' ), 10, 2 );

		// Prefix the title with "map" or "report" if applicable.
		add_filter( 'the_title', array( $plugin_public, 'add_doc_type_to_title' ), 10, 2 );
		// Add a submenu to the docs create button
		add_filter( 'bp_docs_create_button', array( $plugin_public, 'filter_bp_docs_create_button' ), 10, 1 );

		// Add "Channels" output to the docs-loop title cell
		add_action( 'bp_docs_loop_after_doc_excerpt', array( $plugin_public, 'add_channels_docs_loop' ) );
		// Modify tags output on docs-loop
		add_filter( 'bp_docs_taxonomy_tags_td_content', array( $plugin_public, 'change_tags_output' ), 10, 2 );

		// Add a "Channels" meta box to the docs edit screen
		add_action( 'bp_docs_after_tags_meta_box', array( $plugin_public, 'docs_edit_channels_metabox' ), 10, 1 );
		// Save channel selections from doc edit screen.
		add_action( 'bp_docs_doc_saved', array( $plugin_public, 'save_channel_selection' ) );

		// Display a doc's channels on its single doc page
		add_filter( 'bp_docs_taxonomy_show_terms', 	array( $plugin_public, 'add_channels_single_doc' ), 10, 2 );

		// If a doc is a "standard" doc, save the appropriate type term.
		add_action( 'bp_docs_doc_saved', array( $plugin_public, 'save_doc_type' ) );

		// @TODO: Scope these
		add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_styles') );
		add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_scripts') );

		add_filter( 'bp_docs_get_doc_edit_link', array( $plugin_public, 'filter_bp_docs_get_doc_edit_link') );

		// insert maps and reports on channel pages.
		add_action( 'channel_page_after_featured_story', array( $plugin_public, 'add_featured_map_to_channel_page' ), 10, 2 );
		add_action( 'channel_page_after_featured_story', array( $plugin_public, 'add_featured_report_to_channel_page' ), 12, 2 );

		// Add a way to get the possible map categories via JSON.
		add_action( 'wp_ajax_nopriv_cc-json-get-map-categories', array( $plugin_public, 'json_get_map_categories' ) );
		add_action( 'wp_ajax_cc-json-get-map-categories', array( $plugin_public, 'json_get_map_categories' ) );

		// Modify the displayed genericon when the doc is a map or report.
		add_filter( 'bp_docs_get_genericon', array( $plugin_public, 'filter_bp_docs_get_genericon' ), 10, 3 );

		// For the short term, use the table view of docs.
		add_filter( 'bp_docs_use_legacy_directory_template', array( $this, 'use_table_layout' ) );

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The slug of the plugin is the portion of the uri after the group name.
	 *
	 * @since     1.0.0
	 * @return    string    The slug used.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the taxonomy name we're using.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the taxonomy.
	 */
	public function get_taxonomy_name() {
		return $this->taxonomy_name;
	}

	/**
	 * Retrieve the taxonomy term for "map" in our new taxonomy.
	 *
	 * @since     1.0.0
	 * @return    int    The term ID.
	 */
	public function get_taxonomy_term_id_map() {
		$term = get_term_by( 'slug', 'map', $this->get_taxonomy_name() );
		return (int) $term->term_id;
	}

	/**
	 * Retrieve the taxonomy term for "map" in our new taxonomy.
	 *
	 * @since     1.0.0
	 * @return    int    The term ID.
	 */
	public function get_taxonomy_term_id_report() {
		$term = get_term_by( 'slug', 'report', $this->get_taxonomy_name() );
		return (int) $term->term_id;
	}

	/**
	 * Retrieve the taxonomy term for "map" in our new taxonomy.
	 *
	 * @since     1.1.0
	 * @return    int    The term ID.
	 */
	public function get_taxonomy_term_id_area() {
		$term = get_term_by( 'slug', 'area', $this->get_taxonomy_name() );
		return (int) $term->term_id;
	}

	/**
	 * BP Docs is moving to a non-table layout. It's not quite ready yet...
	 *
	 * @since     1.0.0
	 * @return    int    The term ID.
	 */
	public function use_table_layout() {
		return true;
	}

}