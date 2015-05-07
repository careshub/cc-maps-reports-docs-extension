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

		// $plugin_admin = new CC_Group_Pages_Admin( $this->get_plugin_name(), $this->get_version() );

		// add_action( 'wp_enqueue_scripts', array( $plugin_admin, 'enqueue_styles') );
		// add_action( 'wp_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts') );

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

		$plugin_public = new CC_MRAD_Public( $this->get_plugin_name(), $this->get_version() );
		// add our callback to both ajax actions.
		// add_action( "wp_ajax_ccgp_get_page_details", array( $plugin_public, "ccgp_ajax_retrieve_page_details" ) );
		// add_action( "wp_ajax_nopriv_ccgp_get_page_details", array( $plugin_public, "ccgp_ajax_retrieve_page_details" ) );
		// add_action( "wp_ajax_ccgp_get_page_order", array( $plugin_public, "ccgp_ajax_retrieve_page_order" ) );
		// add_action( "wp_ajax_nopriv_ccgp_get_page_order", array( $plugin_public, "ccgp_ajax_retrieve_page_order" ) );


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
	 * @return    string    The name of the taxonomy.
	 */
	public function get_taxonomy_term_id_map() {
		$term = get_term_by( 'slug', 'map', get_taxonomy_name() );
		return (int) $term->term_id;
	}

	/**
	 * Create or update a doc that represents a saved map.
	 *
	 * @since     1.0.0
	 * @return    int Created/updated post_id or 0 if error.
	 */
	public function save_map( $incoming_args = array() ) {

		// We need to provide an array like this:
		$defaults = array(
			'doc_id' 		=> 0,
			'title'			=> '',
			'content' 		=> '',
			'permalink'		=> '',
			'author_id'		=> bp_loggedin_user_id(),
			'group_id'		=> null,
			'is_auto'		=> 0,
			'taxonomies'	=> array(),
			'settings'		=> array(   'read' => 'creator',
										'edit' => 'creator',
										'read_comments' => 'creator',
										'post_comments' => 'creator',
										'view_history' => 'creator'
									),
			'parent_id'		=> 0,
			);


		// If this was shared with a group:
		$args[ 'group_id' ] = $group_id;
		$args[ 'settings' ] = array(    'read' => 'group-members',
										'edit' => 'creator',
										'read_comments' => 'group-members',
										'post_comments' => 'group-members',
										'view_history' => 'creator'
									);

		// If this was shared publicly
		$args[ 'group_id' ] = 0;
		$args[ 'settings' ] = array(    'read' => 'anyone',
										'edit' => 'creator',
										'read_comments' => 'anyone',
										'post_comments' => 'anyone',
										'view_history' => 'creator'
									);

		// If this was created for private use only
		$args[ 'group_id' ] = 0;
		// $args[ 'settings' ] is already set for this

		$args['taxonomies'] = array( $this->get_taxonomy_name() => array( 'map' )  );

		$instance = new BP_Docs_Query;
		return $instance->save( $args );

	}
}