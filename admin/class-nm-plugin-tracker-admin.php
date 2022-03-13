<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://needmoredesigns.com/
 * @since      1.0.0
 *
 * @package    Nm_Plugin_Tracker
 * @subpackage Nm_Plugin_Tracker/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Nm_Plugin_Tracker
 * @subpackage Nm_Plugin_Tracker/admin
 * @author     Raymond Brigleb <ray@needmoredesigns.com>
 */
class Nm_Plugin_Tracker_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Load example settings page
		if (!class_exists("Nm_Plugin_Tracker_Settings")) {
			require(PLUGIN_TRACKER_DIR . 'settings.php');
		}

		$this->settings = new Nm_Plugin_Tracker_Settings();

		// add_action('init', array($this,'init'));
		add_action( 'admin_init', array($this,'admin_init') );
		add_action( 'plugins_loaded' , array( $this , 'plugin_tracker_load_textdomain' ) );

		register_activation_hook(__FILE__, array($this,'activate'));
		register_uninstall_hook(__FILE__, array('Plugin_Tracker','uninstall'));	

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Nm_Plugin_Tracker_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nm_Plugin_Tracker_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/nm-plugin-tracker-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Nm_Plugin_Tracker_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nm_Plugin_Tracker_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/nm-plugin-tracker-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Load plugin textdomain.
	 *
	 */
	public function plugin_tracker_load_textdomain() {
		load_plugin_textdomain( 'plugin-tracker', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' ); 
	}

	/*
			Propagates pfunction to all blogs within our multisite setup.
			More details -
			http://shibashake.com/wordpress-theme/write-a-plugin-for-wordpress-multi-site

			If not multisite, then we just run pfunction for our single blog.
	*/
	public static function network_propagate($pfunction, $networkwide) {
			global $wpdb;

			if (function_exists('is_multisite') && is_multisite()) {
					// check if it is a network activation - if so, run the activation function
					// for each blog id
					if ($networkwide) {
							$old_blog = $wpdb->blogid;
							// Get all blog ids
							$blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
							foreach ($blogids as $blog_id) {
									switch_to_blog($blog_id);
									call_user_func($pfunction, $networkwide);
							}
							switch_to_blog($old_blog);
							return;
					}
			}
			call_user_func($pfunction, $networkwide);
	}

	function activate($networkwide) {
			$this->network_propagate(array($this, '_activate'), $networkwide);
	}

	public static function uninstall($networkwide) {
			Plugin_Tracker::network_propagate(array('Plugin_Tracker', '_uninstall'), $networkwide);
	}

	/*
			Plugin activation code here.
	*/
	protected function _activate() {
			global $wpdb;

			$table_name = $wpdb->prefix . 'plugin_tracker';

			$charset_collate = '';

			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
			}

			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE {$wpdb->collate}";
			}

			$sql = "CREATE TABLE IF NOT EXISTS $table_name (
					`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					`user_id` bigint(20) NOT NULL,
					`status` int NOT NULL,
					`note` text,
					`plugin_data` text,
					`old_plugin_data` text,
					`plugin_path` varchar(255) NOT NULL,
					`wp_version` varchar(255),
					`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`id`)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			dbDelta( $sql );

			add_option( 'pa_db_version', $this->db_version );
	}

	/**
	* Plugin deactivation code here.
	*/
	public static function _uninstall() {
			global $wpdb;
			global $pa_db_version;

			$table_name = $wpdb->prefix . 'plugin_tracker';

			$sql = "DROP TABLE $table_name;";

			$wpdb->query( $sql );

			delete_option( 'pa_db_version' );
			delete_option( 'pa_plugins' );
			delete_option( 'pa_active_plugins' );
	}

	/**
	 * Load the actions that must be done when "save" or "dismiss" are submited and get plugin's data
	 */
	public function admin_init() {
			global $wpdb, $log;

			$user_ID = get_current_user_id();

			$table_name = $wpdb->prefix . 'plugin_tracker';

			$query = "SELECT * FROM $table_name WHERE user = NULL";

			if(isset($_POST['save_note'])) {
					if(!empty($_POST['note'])) {
							$wpdb->update(
									$table_name,
									array('note' => sanitize_text_field($_POST['note'])),
									array('id' => intval($_POST['log_id'])),
									array('%s'),
									array('%d')
							);
					};
			} else {
					if (isset($_POST['not_now'])) {
							$id_log = intval($_POST['log_id']);
							$log = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id_log AND status = 1");

							if ( $log->note != NULL ) {
									 return;
							} else {
									$wpdb->update( 
											$table_name,
											array( 'note' => 'No comment provided' ), 
											array( 'id' => intval($_POST['log_id']))
									);
							}
					}
			}   

			$all_plugins = get_plugins();
			$all_plugins_keys = array_keys($all_plugins);
			$active_plugins = (array) get_option('active_plugins', array());
			$pa_plugins = (array) get_option('pa_plugins', array());
			$pa_plugins_keys = array_keys($pa_plugins);
			$pa_active_plugins = (array) get_option('pa_active_plugins', array());

			foreach($all_plugins as $plugin => $data) {
					$common_data = array(
							'user_id' => $user_ID,
							'plugin_path' => trim($plugin),
							'plugin_data' => $data,
					);
					if(isset($pa_plugins[$plugin])) {
							$common_data['old_plugin_data'] = $pa_plugins[$plugin];
					}
					if(!in_array($plugin, $pa_plugins_keys)) {
							$this->log_action(array_merge($common_data, array(
									'status'    => 1,
							)));
					} elseif(in_array($plugin, $active_plugins) && !in_array($plugin, $pa_active_plugins)) {
							$this->log_action(array_merge($common_data, array(
									'status'    => 2,
							)));
					}
					if(!in_array($plugin, $active_plugins) && in_array($plugin, $pa_active_plugins)) {
							$this->log_action(array_merge($common_data, array(
									'status'    => 3,
							)));
					}
					if(!empty($pa_plugins[$plugin]['Version']) && $data['Version'] != $pa_plugins[$plugin]['Version']) {
							$this->log_action(array_merge($common_data, array(
									'status' => 4,
							)));
					}
			}

			foreach($pa_plugins_keys as $plugin) {
					if(!in_array($plugin, $all_plugins_keys)) {
							$this->log_action(array(
									'user_id' => $user_ID,
									'status' => 5,
									'plugin_path' => trim($plugin),
									'plugin_data' => $pa_plugins[$plugin],
									'old_plugin_data' => $pa_plugins[$plugin],
							));
					}
			}

			update_option('pa_plugins', $all_plugins);
			update_option('pa_active_plugins', $active_plugins);

			if ( function_exists('is_multisite') && is_multisite() && is_network_admin() ) {
					add_action('network_admin_notices', array($this, 'add_note_nag'), 99);
			}

			if ( ! is_multisite() ) {
					add_action('admin_notices', array($this, 'add_note_nag'), 99);
			}
	}

	/**
	 * Add note nag in the top of page where the user can comment about a plugin that was installed
	 */
	public function add_note_nag() {
			global $wpdb;

			$table_name = $wpdb->prefix . 'plugin_tracker';

			$list = $wpdb->get_results("SELECT plugin_path FROM $table_name WHERE status = 5", ARRAY_A);

			foreach ( $list as $path ) 
			{
					$wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE plugin_path = %s",$path));
			}

			/* This query retrieve the fields that need to receive data - aka note */
			$log = $wpdb->get_row("SELECT * FROM $table_name WHERE note IS NULL AND status = 1 AND plugin_path <> 'plugin-tracker/nm-plugin-tracker.php'");

			/* This query let us know how many plugins we have installed in the system that do note have comment */
			$plugin_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE note IS NULL AND status = 1");

			$textarea_note = '';

			if (isset($_POST['edit_note'])) {
					$id_log = intval($_POST['log_id']);
					$log = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id_log AND status = 1");
					$textarea_note = $log->note;
			}

			if( $log && current_user_can( 'update_plugins' ) ) {
					$plugin_data = json_decode($log->plugin_data); ?>

			<div class="plugin-tracker-box">
			<h1><?php echo _e('Plugin Tracker', 'plugin-tracker'); ?></h1>
					<form method="post" action="">
							<input type="hidden" name="log_id" value="<?php echo $log->id ?>">
							<p>
							<?php
									printf(__( 'Why did you install <b>"%1$s"</b> ?', 'plugin-tracker' ), $plugin_data->Name); ?>
							</p>
							<p>
									<textarea style="width: 100%;" name="note" id="note" cols="40" rows="3" placeholder="<?php _e( 'add comments here', 'plugin-tracker' ) ?>"><?php echo $textarea_note; ?></textarea>
							</p>
							<p>
									<button type="submit" name="save_note" class="button button-primary" style="vertical-align: top;"><?php _e( 'Save', 'plugin-tracker' ); ?></button>
									<button type="submit" name="not_now" class="button button-secondary" style="vertical-align: top;"><?php _e( 'Not now', 'plugin-tracker' ); ?></button>
							</p>
					</form>
					<?php 
							$all_plugins = get_plugins();
							if ( $plugin_count > 0 ) { 
									printf(
											/* translators: 1: plugins without comments 2: total plugins */
											__( '<b>%1$s</b> of <b>%2$s</b> plugins awaiting an explanation', 'plugin-tracker' ),
											( $plugin_count - 1 ),
											( count($all_plugins) - 1 )
									);
							} ?>
							
			</div>
<?php
			}
	}

	/**
	 * Encode the plugin data and insert in the database
	 */
	protected function log_action($data) {
			global $wpdb, $wp_version;

			$table_name = $wpdb->prefix . 'plugin_tracker';

			if(isset($data['plugin_data']) && !is_string($data['plugin_data'])) {
					$data['plugin_data'] = json_encode($data['plugin_data']);
			}

			if(isset($data['old_plugin_data']) && !is_string($data['old_plugin_data'])) {
					$data['old_plugin_data'] = json_encode($data['old_plugin_data']);
			}

			$data = array_merge($data, array(
					'wp_version' => $wp_version,
			));

			$wpdb->insert($table_name, $data);
	}
}
