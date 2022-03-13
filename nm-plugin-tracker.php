<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://needmoredesigns.com/
 * @since             1.0.0
 * @package           Nm_Plugin_Tracker
 *
 * @wordpress-plugin
 * Plugin Name:       Needmore Plugin Tracker
 * Plugin URI:        https://github.com/needmore/nm-plugin-tracker
 * Description:       A simple plugin to keep track of new installations of other plugins, and allow administrators to keep tabs on things.
 * Version:           1.0.0
 * Author:            Raymond Brigleb
 * Author URI:        https://needmoredesigns.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nm-plugin-tracker
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'NM_PLUGIN_TRACKER_VERSION', '1.0.0' );
define('PLUGIN_TRACKER_DIR', plugin_dir_path(__FILE__));
define('PLUGIN_TRACKER_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-nm-plugin-tracker-activator.php
 */
function activate_nm_plugin_tracker() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-nm-plugin-tracker-activator.php';
	Nm_Plugin_Tracker_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-nm-plugin-tracker-deactivator.php
 */
function deactivate_nm_plugin_tracker() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-nm-plugin-tracker-deactivator.php';
	Nm_Plugin_Tracker_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_nm_plugin_tracker' );
register_deactivation_hook( __FILE__, 'deactivate_nm_plugin_tracker' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-nm-plugin-tracker.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_nm_plugin_tracker() {

	$plugin = new Nm_Plugin_Tracker();
	$plugin->run();

}
run_nm_plugin_tracker();
