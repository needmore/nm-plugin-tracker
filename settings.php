<?php
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

if (!class_exists("Nm_Plugin_Tracker_Settings")) :

class Nm_Plugin_Tracker_Settings {

    public static $default_settings = array(
        'allowed_id' => 0,
        'content_frozen' => 0,
    );

    var $pagehook, $page_id, $settings_field, $options;

    function __construct() {
        $this->page_id = 'nm_plugin_tracker';
        // This is the get_options slug used in the database to store our plugin option values.
        $this->settings_field = 'nm_plugin_tracker_options';
        $this->options = get_option($this->settings_field);

        if (is_admin()) {
            add_action('admin_menu', array($this, 'admin_menu'), 20);
            add_action( 'wp_ajax_update_plugin_description', array($this, 'ajax_update_plugin_description') );
            add_action( 'wp_ajax_nopriv_update_plugin_description', array($this, 'ajax_update_plugin_description') );
        }

        if ( function_exists('is_multisite') && is_multisite() && is_network_admin() ) {
            add_action('network_admin_menu', array($this, 'admin_menu'));  
        }
        
    }

    function admin_menu() {
        if ( ! is_multisite() && is_admin() ) {
            // Add a new submenu to the standard Settings panel
            $this->pagehook = $page = add_plugins_page(__('Plugin Tracker', 'nm-plugin-tracker'), __('Plugin Tracker', 'plugin-tracker'), 'administrator', $this->page_id, array($this,'render'));

            // Include js, css, or header *only* for our settings page
            add_action("admin_print_scripts-$page", array($this, 'js_includes'));

            add_action("admin_print_styles-$page", array($this, 'css_includes'));
            add_action("admin_head", array($this, 'admin_head'));
        
        }

        if ( function_exists('is_multisite') && is_multisite() && is_network_admin() ) {
            // Add a new submenu to the standard Settings panel
        $this->pagehook = $page = add_plugins_page(__('Plugin Tracker', 'nm-plugin-tracker'), __('Plugin Tracker', 'plugin-tracker'), 'administrator', $this->page_id, array($this,'render'));

        // Include js, css, or header *only* for our settings page
        add_action("admin_print_scripts-$page", array($this, 'js_includes'));

        add_action("admin_print_styles-$page", array($this, 'css_includes'));
        add_action("admin_head", array($this, 'admin_head'));
        
        }
        
    }

    /**
    *   Function to work with pa.js
    */
    public function ajax_update_plugin_description() {
        global $wpdb;

        $plugin_desc = sanitize_text_field( $_POST['plugin_desc'] );
        $log_id = absint( $_POST['log_id'] );

        if( ! empty( $log_id ) && ! empty ($plugin_desc) ) {
            $table_name = $wpdb->prefix . 'nm_plugin_tracker';
            $wpdb->update(
                    $table_name,
                    array( 'note' => $plugin_desc ),
                    array( 'id' => $log_id ),
                    array( '%s' ),
                    array( '%d' )
            );
            $ajax_status = 'success';
        } else{
            $ajax_status = 'error';
        }
        echo $ajax_status;
        wp_die();
    }

    function admin_head() {
?>
        <style>
            .settings_page_plugin_audit label { display:inline-block; width: 150px; }
        </style>
        <?php 
        // Plugin styles
        $handle = 'adminCSS';
        $src = plugins_url() . '/nm-plugin-tracker/assets/css/admin.css';
        $deps = array();
        $ver = '0.2';
        $media = 'all';

        // Enqueue CSS for the admin page
        wp_enqueue_style( $handle, $src, $deps, $ver, $media );
    }

    function css_includes() {
        // Plugin styles
        $handle = 'main';
        $src = plugins_url() . '/nm-plugin-tracker/assets/css/main.css';
        $deps = array();
        $ver = '0.2';
        $media = 'all';

        // Enqueue main CSS
        wp_enqueue_style( $handle, $src, $deps, $ver, $media );

        
    }

    function js_includes() {
        // Needed to allow metabox layout and close functionality.
        wp_enqueue_script('postbox');

        // Sort table attributes
        $handle = 'sortabble';
        $src = plugins_url() . '/nm-plugin-tracker/assets/js/sorttable.js';
        $deps = array();
        $ver = '2.0.0';
        $in_footer = true;

        // Enqueue JavaScript to sort tables
        wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );

        // Sort table attributes
        $handle = 'jquery-base64';
        $src = plugins_url() . '/nm-plugin-tracker/assets/js/jquery.base64.js';
        $deps = array();
        $ver = '1.0.0';
        $in_footer = true;

        // Enqueue JavaScript to sort tables
        wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );

        // Sort table attributes
        $handle = 'tableExport';
        $src = plugins_url() . '/nm-plugin-tracker/assets/js/tableExport.js';
        $deps = array();
        $ver = '1.0.0';
        $in_footer = true;

        // Enqueue JavaScript to sort tables
        wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );

        // Parameters to the custom JS
        $handle = 'pa-custom-js';
        $src = plugins_url() . '/nm-plugin-tracker/assets/js/pa.js';
        $deps = array('jquery');
        $ver = null;
        $in_footer = true;

        wp_register_script( $handle, $src, $deps, $ver, $in_footer );

        $translation_array = array(
            'Saved'     => __( 'Saved', 'plugin_auditor' ),
            'Not saved' => __( 'Not saved', 'plugin_auditor' ),
            'url'       => admin_url( 'admin-ajax.php'),
            'pluginUrl' => plugins_url()
            );

        wp_localize_script( 'pa-custom-js', 'myAjax', $translation_array );

        wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );

    }

    /**
    *   Sanitize our plugin settings array as needed.
    */
    function sanitize_theme_options($options) {
        $options['example_text'] = stripcslashes($options['example_text']);
        return $options;
    }

    /*
        Settings access functions.
    */
    protected function get_field_name($name) {

        return sprintf('%s[%s]', $this->settings_field, $name);
    }

    protected function get_field_id($id) {

        return sprintf('%s[%s]', $this->settings_field, $id);
    }

    protected function get_field_value($key) {

        return $this->options[$key];
    }

    /*
        Render settings page.
    */
    function render() {
        global $wp_meta_boxes, $wpdb;

        $table_name = $wpdb->prefix . 'plugin_audit';

        /* Query to select the entries that must be displayed in the table */
        $logs = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 1 AND plugin_path <> 'nm-plugin-tracker/nm-plugin-tracker.php' ORDER BY `timestamp` DESC");

        /* This query let us know if the date of the plugins are the same of the plugin auditor. If true, the user will be displayed as "Unkown" */
        $query_date = $wpdb->get_var( "SELECT `timestamp` FROM $table_name WHERE plugin_path = 'nm-plugin-tracker/nm-plugin-tracker.php'" );

        if(isset($_POST['add_note'])) {
            if(!empty($_POST['note'])) {
                $wpdb->update(
                    $table_name,
                    array('note' => sanitize_text_field($_POST['note'])),
                    array('id' => intval($_POST['log_id'])),
                    array('%s'),
                    array('%d')
                );
            }
        } ?>
        <h1><?php _e( 'Plugin Tracker', 'plugin-tracker' ); ?></h1>
        <p><?php _e( 'Below is a record of all installed plugins, including details of who installed them, when they were installed and why. This information should make it easier to maintain your WordPress website.', 'plugin-tracker' ); ?></p>
        <p><?php _e( 'If you are adding a comment to a plugin, please ensure that you state the reason for adding the plugin, not simply describe the functionality of the plugin. You will thank yourself for this later!', 'plugin-tracker' ); ?></p>
        <button class="button button-secondary"><a class="no-style" href="#" onClick ="$('#main-table').tableExport({type:'excel',escape:'false', tableName:'Plugin Tracker', ignoreColumn: [4]});"><?php _e( 'Export data to Excel', 'plugin_tracker' ); ?></a></button>
        <button class="button button-secondary"><a class="no-style" href="#" onClick ="$('#main-table').tableExport({type:'csv',escape:'false', tableName:'Plugin-Tracker', ignoreColumn: [4]});"><?php _e( 'Export data to CSV', 'plugin_tracker' ); ?></a></button>
        
        <div class="wrap">

            <table class="sortable wp-list-table widefat fixed posts" id="main-table">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column"><?php _e( 'Plugin', 'plugin-tracker' ) ?></th>
                        <th scope="col" class="manage-column"><?php _e( 'User', 'plugin-tracker' ) ?></th>
                        <th scope="col" class="manage-column sorttable_numeric"><?php _e( 'Date Installed', 'plugin-tracker' ) ?></th>
                        <th scope="col" class="manage-column"><?php _e( 'Reason for Installation', 'plugin-tracker' ) ?></th>
                        <th scope="col" class="manage-column sorttable_nosort"><?php _e( 'Manage Comments', 'plugin-tracker' ) ?></th>
                    </tr>
                </thead>
                <tbody id="the-list">
                    <?php
                    foreach($logs as $log) {
                        $plugin_data = json_decode($log->plugin_data);
                        $old_plugin_data = json_decode($log->old_plugin_data);
                    ?>
                    <tr id="log-<?php echo $log->id ?>" class="log-<?php echo $log->id ?> type-log">
                        <td>
                            <span title="<?php echo $plugin_data->Name; ?>"><?php echo $plugin_data->Name; ?>
                            <!-- (<?php echo $plugin_data->Version; ?>) -->
                            </span>
                        </td>
                        <td><?php
                            $user_info = get_userdata($log->user_id); 
                            if ( $query_date == $log->timestamp && $plugin_data->Name != 'Plugin Tracker' ) {
                                _e( 'Unkown User', 'plugin-tracker' );
                            } else if ( ! $user_info ) {
                                _e( 'Unkown User', 'plugin-tracker' );
                            } else {
                                if($user_info->first_name && $user_info->last_name):
                                    echo $user_info->first_name . ' ' . $user_info->last_name;
                                elseif ($user_info->first_name && !$user_info->last_name):
                                    echo $user_info->first_name;
                                else:
                                    echo $user_info->user_login;
                                endif;
                            } ?></td>
                        <td><?php echo $log->timestamp; ?></td>
                        <td  contenteditable="false" class="note-id-<?php echo $log->id ?>"><?php echo $log->note; ?></td>
                        <td>
                            <form class="form-edit-comment" data-clicked="false" action=""  method="post" id="<?php echo $log->id ?>" >
                                <input type="hidden" name="log_id" value="<?php echo $log->id ?>">
                                <input type="hidden" name="edit_note" value="true">
                                <input type="hidden" class="edit_note_content" name="edit_note_content" value="">

                                <button type="submit" class="button button-primary add-or-edit-comment" style="vertical-align: top;">
                                <span class="loader"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/img/tail-spin.svg'; ?>"></span>
                                <span class="button-content">
                                    <?php $log->note == NULL ? _e( 'Add comment', 'plugin-tracker' ) : _e( 'Edit comment', 'plugin-tracker' )
                                    ?>
                                </span>
                                </button>

                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            

            <?php

                if ( is_multisite() ) {

                echo '<div class="list-multisites">';
                /*
                 * Network Activated Plugins
                 */

                ?><div class="plugin-tracker-info"><?php _e( '<p><b>Note: </b>You are in a multisite installation. Below you can see plugins that are activated in each site!<p>' ); ?></div><?php
                $the_plugs = get_site_option('active_sitewide_plugins'); ?>
                <h2><?php _e( 'Network Activated Plugins', 'plugin_tracker' ) ?></h2>
                <ol><?php
                foreach($the_plugs as $key => $value) { 
                    $string = explode('/',$key);
                    if ( ( $string[0] . '/' . $string[1] ) == NULL ) {
                        $plugin_name = $string[0];
                    } else {
                        $plugin_name = $string[0] . '/' . $string[1];
                    }
                    ?>
                    
                        <li><?php 

                        $query_name = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 1 AND plugin_path = '$plugin_name'"); 

                        $plugin_data_queried = json_decode( $query_name[0]->plugin_data );
                        print_r( $plugin_data_queried->Name );

                        ?></li>
                <?php } ?>
                </ol> <?php

                /*
                 * Iterate Through All Sites
                 */
                $blogs = $wpdb->get_results("
                    SELECT blog_id
                    FROM {$wpdb->blogs}
                    WHERE site_id = '{$wpdb->siteid}'
                    AND spam = '0'
                    AND deleted = '0'
                    AND archived = '0'
                ");
                ?>
                <h2><?php _e( 'Sites', 'plugin_tracker' ) ?></h2>
                <?php
                foreach ($blogs as $blog) {

                    $the_plugs = get_blog_option($blog->blog_id, 'active_plugins'); ?>

                    <hr><h3><?php echo get_blog_option($blog->blog_id, 'blogname') ?></h3>
                    <ol>
                    
                    <?php

                    foreach($the_plugs as $key => $value) {
                        $string = explode('/',$value);
                        if ( ( $string[0] . '/' . $string[1] ) == NULL ) {
                            $plugin_name = $string[0];
                        } else {
                            $plugin_name = $string[0] . '/' . $string[1];
                        }
                        ?>
                        <?php $plugin_name = $string[0] . '/' . $string[1]; ?>
                        <li><?php 

                        $query_name = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 1 AND plugin_path = '$plugin_name'"); 

                        $plugin_data_queried = json_decode( $query_name[0]->plugin_data );
                        print_r( $plugin_data_queried->Name );

                        ?></li>

                    <?php } ?>
                    </ol><?php
                }
                echo "</div>";
                }
            ?>
            <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
            <br>
            
        
    <?php }

    function print_title($var) {
        $search = array('stdClass Object', '<', '>', '"');
        return trim(trim(str_replace($search, '', strip_tags(print_r($var, true)))), '()');
    }


} // end class
endif;
?>