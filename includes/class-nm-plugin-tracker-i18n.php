<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://needmoredesigns.com/
 * @since      1.0.0
 *
 * @package    Nm_Plugin_Tracker
 * @subpackage Nm_Plugin_Tracker/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Nm_Plugin_Tracker
 * @subpackage Nm_Plugin_Tracker/includes
 * @author     Raymond Brigleb <ray@needmoredesigns.com>
 */
class Nm_Plugin_Tracker_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'nm-plugin-tracker',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
