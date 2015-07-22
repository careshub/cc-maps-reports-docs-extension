<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           CC_BuddyPress_Docs_Maps_Reports_Extension
 *
 * @wordpress-plugin
 * Plugin Name:       CC Maps & Reports as BuddyPress Docs
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       Stores saved maps and reports as BuddyPress Docs.
 * Version:           1.0.0
 * Author:            David Cavins
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cc-mrad
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 */
// require_once plugin_dir_path( __FILE__ ) . 'includes/class-cc-group-pages-activator.php';

/**
 * The code that runs during plugin deactivation.
 */
// require_once plugin_dir_path( __FILE__ ) . 'includes/class-cc-group-pages-deactivator.php';

/** This action is documented in includes/class-plugin-name-activator.php */
// register_activation_hook( __FILE__, array( 'Plugin_Name_Activator', 'activate' ) );

/** This action is documented in includes/class-plugin-name-deactivator.php */
// register_deactivation_hook( __FILE__, array( 'Plugin_Name_Deactivator', 'deactivate' ) );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
/**
 * Load the main class, after bp-docs has had a chance to load.
 *
 * @since    1.0.0
 */
function cc_mrad_main_class_init() {

	// Only load this plugin if BuddyPress Docs is active.
    if ( class_exists( 'BP_Docs' ) ) {
    	require_once( plugin_dir_path( __FILE__ ) . 'includes/class-cc-mrad.php' );
    	add_action( 'bp_include', array( 'CC_MRAD', 'get_instance' ), 21 );
    }
}
add_action( 'bp_include', 'cc_mrad_main_class_init', 14 );