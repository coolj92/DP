<?php
/**
 * Plugin Name: Directorist Listings with Map
 * Plugin URI: http://directorist.com/plugins/directorist-listings-with-map
 * Description: This is an add-on for Directorist Plugin. You can display listings with map by this extension.
 * Version: 1.6.5
 * Author: wpWax
 * Author URI: https://wpwax.com
 * License: GPLv2 or later
 * Text Domain: directorist-listings-with-map
 * Domain Path: /languages
 */

// prevent direct access to the file
defined('ABSPATH') || die('No direct script access allowed!');
if (!class_exists('BD_Map_View')){
    final class BD_Map_View
    {
        /** Singleton *************************************************************/

        /**
         * @var BD_Map_View The one true BD_Map_View
         * @since 1.0
         */
        private static $instance;
        /**
         * BDMV_Settings Object.
         *
         * @var object|BDMV_Settings
         * @since 1.0
         */
        public $BDMV_Settings;
        /**
         * BDMV_Ajax Object.
         *
         * @var object|BDMV_Ajax
         * @since 1.0
         */
        public $BDMV_Ajax;
        /**
         * BDMV_Hooks Object.
         *
         * @var object|BDMV_Hooks
         * @since 1.0
         */
        public $BDMV_Hooks;

        public $data = [];
        /**
         * Main BD_Map_View Instance.
         *
         * Insures that only one instance of BD_Map_View exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @since 1.0
         * @static
         * @static_var array $instance
         * @uses BD_Map_View::setup_constants() Setup the constants needed.
         * @uses BD_Map_View::includes() Include the required files.
         * @uses BD_Map_View::load_textdomain() load the language files.
         * @see  BD_Map_View()
         * @return object|BD_Map_View The one true BD_Map_View
         */
        public static function instance()
        {
            if (!isset(self::$instance) && !(self::$instance instanceof BD_Map_View)) {
                self::$instance = new BD_Map_View;
                self::$instance->setup_constants();
                add_action('plugins_loaded', array(self::$instance, 'load_textdomain'));
                add_action('plugins_loaded', array(self::$instance, 'setup_template_files'));
                add_action('wp_enqueue_scripts', array(self::$instance, 'load_needed_scripts'));
                add_action('admin_enqueue_scripts', array(self::$instance, 'load_needed_scripts_for_admin'));
                add_filter('atbdp_license_settings_controls', array(self::$instance, 'license_settings_controls'));
                // // license and auto update handler
                add_action('wp_ajax_atbdp_listings_map_license_activation', array(self::$instance, 'atbdp_listings_map_license_activation'));
                // // license deactivation
                add_action('wp_ajax_atbdp_listings_map_license_deactivation', array(self::$instance, 'atbdp_listings_map_license_deactivation'));

                self::$instance->includes();
                self::$instance->BDMV_Settings  = new BDMV_Settings;
                self::$instance->BDMV_Ajax      = new BDMV_Ajax;
                self::$instance->BDMV_Hooks     = new BDMV_Hooks;

            }
            return self::$instance;
        }

        // setup_template_files
        public function setup_template_files() {

            add_filter( 'atbdp_ext_template_path_listings_archive', function( $default, $args ) {

                if ( 'listings_with_map' === $args['listings']->view ) {
                    $default['template_directory'] = BDM_TEMPLATES_DIR;
                    $default['file_path']          = 'map-view';
                    $default['base_directory']     = BDM_BASE;
                }

                return $default;
            }, 20, 2);
        }

        public function atbdp_listings_map_license_deactivation()
        {
            $license = !empty($_POST['listings_map_license']) ? trim($_POST['listings_map_license']) : '';
            $options = get_option('atbdp_option');
            $options['listings_map_license'] = $license;
            update_option('atbdp_option', $options);
            update_option('directorist_listings_map_license', $license);
            $data = array();
            if (!empty($license)) {
                // data to send in our API request
                $api_params = array(
                    'edd_action' => 'deactivate_license',
                    'license' => $license,
                    'item_id' => ATBDP_LISTINGS_MAP_POST_ID, // The ID of the item in EDD
                    'url' => home_url()
                );
                // Call the custom API.
                $response = wp_remote_post(ATBDP_AUTHOR_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));
                // make sure the response came back okay
                if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

                    $data['msg'] = (is_wp_error($response) && !empty($response->get_error_message())) ? $response->get_error_message() : __('An error occurred, please try again.', 'directorist-listings-map');
                    $data['status'] = false;

                } else {

                    $license_data = json_decode(wp_remote_retrieve_body($response));
                    if (!$license_data) {
                        $data['status'] = false;
                        $data['msg'] = __('Response not found!', 'directorist-listings-map');
                        wp_send_json($data);
                        die();
                    }
                    update_option('directorist_listings_map_license_status', $license_data->license);
                    if (false === $license_data->success) {
                        switch ($license_data->error) {
                            case 'expired' :
                                $data['msg'] = sprintf(
                                    __('Your license key expired on %s.', 'directorist-listings-map'),
                                    date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                                );
                                $data['status'] = false;
                                break;

                            case 'revoked' :
                                $data['status'] = false;
                                $data['msg'] = __('Your license key has been disabled.', 'directorist-listings-map');
                                break;

                            case 'missing' :

                                $data['msg'] = __('Invalid license.', 'directorist-listings-map');
                                $data['status'] = false;
                                break;

                            case 'invalid' :
                            case 'site_inactive' :

                                $data['msg'] = __('Your license is not active for this URL.', 'directorist-listings-map');
                                $data['status'] = false;
                                break;

                            case 'item_name_mismatch' :

                                $data['msg'] = sprintf(__('This appears to be an invalid license key for %s.', 'directorist-listings-map'), 'Directorist - Listings with Map');
                                $data['status'] = false;
                                break;

                            case 'no_activations_left':

                                $data['msg'] = __('Your license key has reached its activation limit.', 'directorist-listings-map');
                                $data['status'] = false;
                                break;

                            default :
                                $data['msg'] = __('An error occurred, please try again.', 'directorist-listings-map');
                                $data['status'] = false;
                                break;
                        }

                    } else {
                        $data['status'] = true;
                        $data['msg'] = __('License deactivated successfully!', 'directorist-listings-map');
                    }

                }
            } else {
                $data['status'] = false;
                $data['msg'] = __('License not found!', 'directorist-listings-map');
            }
            wp_send_json($data);
            die();
        }

        public function atbdp_listings_map_license_activation()
        {
            $license = !empty($_POST['listings_map_license']) ? trim($_POST['listings_map_license']) : '';
            $options = get_option('atbdp_option');
            $options['listings_map_license'] = $license;
            update_option('atbdp_option', $options);
            update_option('directorist_listings_map_license', $license);
            $data = array();
            if (!empty($license)) {
                // data to send in our API request
                $api_params = array(
                    'edd_action' => 'activate_license',
                    'license' => $license,
                    'item_id' => ATBDP_LISTINGS_MAP_POST_ID, // The ID of the item in EDD
                    'url' => home_url()
                );
                // Call the custom API.
                $response = wp_remote_post(ATBDP_AUTHOR_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));
                // make sure the response came back okay
                if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

                    $data['msg'] = (is_wp_error($response) && !empty($response->get_error_message())) ? $response->get_error_message() : __('An error occurred, please try again.', 'directorist-listings-map');
                    $data['status'] = false;

                } else {

                    $license_data = json_decode(wp_remote_retrieve_body($response));
                    if (!$license_data) {
                        $data['status'] = false;
                        $data['msg'] = __('Response not found!', 'directorist-listings-map');
                        wp_send_json($data);
                        die();
                    }
                    update_option('directorist_listings_map_license_status', $license_data->license);
                    if (false === $license_data->success) {
                        switch ($license_data->error) {
                            case 'expired' :
                                $data['msg'] = sprintf(
                                    __('Your license key expired on %s.', 'directorist-listings-map'),
                                    date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                                );
                                $data['status'] = false;
                                break;

                            case 'revoked' :
                                $data['status'] = false;
                                $data['msg'] = __('Your license key has been disabled.', 'directorist-listings-map');
                                break;

                            case 'missing' :

                                $data['msg'] = __('Invalid license.', 'directorist-listings-map');
                                $data['status'] = false;
                                break;

                            case 'invalid' :
                            case 'site_inactive' :

                                $data['msg'] = __('Your license is not active for this URL.', 'directorist-listings-map');
                                $data['status'] = false;
                                break;

                            case 'item_name_mismatch' :

                                $data['msg'] = sprintf(__('This appears to be an invalid license key for %s.', 'directorist-listings-map'), 'Directorist - Listings with Map');
                                $data['status'] = false;
                                break;

                            case 'no_activations_left':

                                $data['msg'] = __('Your license key has reached its activation limit.', 'directorist-listings-map');
                                $data['status'] = false;
                                break;

                            default :
                                $data['msg'] = __('An error occurred, please try again.', 'directorist-listings-map');
                                $data['status'] = false;
                                break;
                        }

                    } else {
                        $data['status'] = true;
                        $data['msg'] = __('License activated successfully!', 'directorist-listings-map');
                    }

                }
            } else {
                $data['status'] = false;
                $data['msg'] = __('License not found!', 'directorist-listings-map');
            }
            wp_send_json($data);
            die();
        }


        public function license_settings_controls($default)
        {
            $status = get_option('directorist_listings_map_license_status');
            if (!empty($status) && ($status !== false && $status == 'valid')) {
                $action = array(
                    'type' => 'toggle',
                    'name' => 'listings_map_deactivated',
                    'label' => __('Action', 'directorist-listings-map'),
                    'validation' => 'numeric',
                );
            } else {
                $action = array(
                    'type' => 'toggle',
                    'name' => 'listings_map_activated',
                    'label' => __('Action', 'directorist-listings-map'),
                    'validation' => 'numeric',
                );
            }
            $new = apply_filters('atbdp_listings_map_license_controls', array(
                'type' => 'section',
                'title' => __('Listings with Map', 'directorist-listings-map'),
                'description' => __('You can active your Listings with Map extension here.', 'directorist-listings-map'),
                'fields' => apply_filters('atbdp_listings_map_license_settings_field', array(
                    array(
                        'type' => 'textbox',
                        'name' => 'listings_map_license',
                        'label' => __('License', 'directorist-listings-map'),
                        'description' => __('Enter your Listings with Map extension license', 'directorist-listings-map'),
                        'default' => '',
                    ),
                    $action,
                )),
            ));
            $settings = apply_filters('atbdp_licence_menu_for_listings_map', true);
            if($settings){
                array_push($default, $new);
            }
            return $default;
        }

        public function load_needed_scripts()
        {
            wp_enqueue_script('bdm-main-js', plugin_dir_url(__FILE__) . '/public/assets/js/main.js', ['directorist-range-slider'], false, true);
            wp_enqueue_script('bdm-view-js', plugin_dir_url(__FILE__) . '/public/assets/js/view-as.js', ['directorist-range-slider'], false, true);
            wp_register_script('bdm-current-js', plugin_dir_url(__FILE__) . '/public/assets/js/current-location.js');
            if(is_rtl()) {
                wp_enqueue_style('bdm-main-css-rtl', plugin_dir_url(__FILE__) . '/public/assets/css/style-rtl.css');
            } else {
                wp_enqueue_style('bdm-main-css', plugin_dir_url(__FILE__) . '/public/assets/css/style.css');
            }
            wp_localize_script('bdm-main-js','bdrr_submit',array(
                'ajaxnonce'             => wp_create_nonce( 'bdas_ajax_nonce' ),
                'ajax_url'              => admin_url( 'admin-ajax.php' ),
                'nothing_found_text'    => __( 'Nothing Found', 'directorist' ),
                'search_changing_text'  => __( 'Please try to change your search settings', 'directorist' )
            ));
            wp_localize_script('bdm-view-js','bdrr_submit',array(
                'ajaxnonce'         => wp_create_nonce( 'bdas_ajax_nonce' ),
                'ajax_url'           => admin_url( 'admin-ajax.php' ),
                'nothing_found_text' => __( 'Nothing Found', 'directorist' ),
                'search_changing_text'  => __( 'Please try to change your search settings', 'directorist' )
            ));
        }

        public function load_needed_scripts_for_admin() {
            wp_enqueue_script('bdm-admin-js', plugin_dir_url(__FILE__) . '/admin/assets/js/main.js');
            wp_localize_script('bdm-admin-js', 'listings_map_js_obj', array('ajaxurl' => admin_url('admin-ajax.php')));
            wp_enqueue_style('bdm-admin-main-css', plugin_dir_url(__FILE__) . '/admin/assets/css/main.css');

        }
        private function __construct()
        {
            /*making it private prevents constructing the object*/
        }
        /**
         * Throw error on object clone.
         *
         * The whole idea of the singleton design pattern is that there is a single
         * object therefore, we don't want the object to be cloned.
         *
         * @since 1.0
         * @access protected
         * @return void
         */
        public function __clone()
        {
            // Cloning instances of the class is forbidden.
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', BDM_TEXTDOMAIN), '1.0');
        }
        /**
         * Disable unserializing of the class.
         *
         * @since 1.0
         * @access protected
         * @return void
         */
        public function __wakeup()
        {
            // Unserializing instances of the class is forbidden.
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', BDM_TEXTDOMAIN), '1.0');
        }
        /**
         * It register the text domain to the WordPress
         */
        public function load_textdomain()
        {
            load_plugin_textdomain(BDM_TEXTDOMAIN, false, BDM_LANG_DIR);
        }

        /**
         * It Includes and requires necessary files.
         *
         * @access private
         * @since 1.0
         * @return void
         */
        private function includes()
        {
            require_once BDM_DIR . 'inc/settings.php';
            require_once BDM_DIR . 'inc/ajax.php';
            require_once BDM_DIR . 'inc/hooks.php';
            require_once BDM_DIR . 'inc/helper.php';
            require_once BDM_DIR . 'inc/Listings_With_Map_Model.php';

            // setup the updater
            if (!class_exists('EDD_SL_Plugin_Updater')) {
                // load our custom updater if it doesn't already exist
                include(dirname(__FILE__) . '/inc/EDD_SL_Plugin_Updater.php');
            }
            $license_key = trim(get_option('directorist_listings_map_license'));
            new EDD_SL_Plugin_Updater(ATBDP_AUTHOR_URL, __FILE__, array(
                'version' => BDM_VERSION,        // current version number
                'license' => $license_key,    // license key (used get_option above to retrieve from DB)
                'item_id' => ATBDP_LISTINGS_MAP_POST_ID,    // id of this plugin
                'author' => 'AazzTech',    // author of this plugin
                'url' => home_url(),
                'beta' => false // set to true if you wish customers to receive update notifications of beta releases
            ));
        }
        /**
         * It  loads a template file from the Default template directory.
         * @param string $name Name of the file that should be loaded from the template directory.
         * @param array $args Additional arguments that should be passed to the template file for rendering dynamic  data.
         */
        public function load_template($name, $args = array())
        {
            global $post;
            include(BDM_TEMPLATES_DIR . $name . '.php');
        }

        /**
         * Get version from file content
         *
         * @return string
         */
        public static function get_version_from_file_content( $file_path = '' ) {
            $version = '';

            if ( ! file_exists( $file_path ) ) {
                return $version;
            }

            $content = file_get_contents( $file_path );
            $version = self::get_version_from_content( $content );

            return $version;
        }

        /**
         * Get version from content
         *
         * @return string
         */
        public static function get_version_from_content( $content = '' ) {
            $version = '';

            if ( preg_match('/\*[\s\t]+?version:[\s\t]+?([0-9.]+)/i', $content, $v) ) {
                $version = $v[1];
            }

            return $version;
        }

        /**
         * Setup plugin constants.
         *
         * @access private
         * @since 1.0
         * @return void
         */
        private function setup_constants()
        {
            // Plugin version
            if ( ! defined( 'BDM_VERSION' ) ) { define( 'BDM_VERSION', self::get_version_from_file_content( __FILE__ ) ); }
            // Plugin Folder Path.
            if ( ! defined( 'BDM_DIR' ) ) { define( 'BDM_DIR', plugin_dir_path( __FILE__ ) ); }
            // Plugin Folder URL.
            if ( ! defined( 'BDM_URL' ) ) { define( 'BDM_URL', plugin_dir_url( __FILE__ ) ); }
            // Plugin Root File.
            if ( ! defined( 'BDM_FILE' ) ) { define( 'BDM_FILE', __FILE__ ); }
            if ( ! defined( 'BDM_BASE' ) ) { define( 'BDM_BASE', plugin_basename( __FILE__ ) ); }
            if ( ! defined( 'BDM_BASE' ) ) { define( 'BDM_BASE', plugin_basename( __FILE__ ) ); }
            // Plugin Text domain File.
            if ( ! defined( 'BDM_TEXTDOMAIN' ) ) { define( 'BDM_TEXTDOMAIN', 'directorist-listings-map' ); }
            // Plugin Language File Path
            if ( !defined('BDM_LANG_DIR') ) { define('BDM_LANG_DIR', dirname(plugin_basename( __FILE__ ) ) . '/languages'); }
            // Plugin Template Path
            if ( !defined('BDM_TEMPLATES_DIR') ) { define('BDM_TEMPLATES_DIR', BDM_DIR.'templates/'); }
            // plugin author url
            if (!defined('ATBDP_AUTHOR_URL')) {
                define('ATBDP_AUTHOR_URL', 'https://directorist.com');
            }
            // post id from download post type (edd)
            if (!defined('ATBDP_LISTINGS_MAP_POST_ID')) {
                define('ATBDP_LISTINGS_MAP_POST_ID', 13794 );
            }

        }
    }

    if ( ! function_exists( 'directorist_is_plugin_active' ) ) {
        function directorist_is_plugin_active( $plugin ) {
            return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) || directorist_is_plugin_active_for_network( $plugin );
        }
    }

    if ( ! function_exists( 'directorist_is_plugin_active_for_network' ) ) {
        function directorist_is_plugin_active_for_network( $plugin ) {
            if ( ! is_multisite() ) {
                return false;
            }

            $plugins = get_site_option( 'active_sitewide_plugins' );
            if ( isset( $plugins[ $plugin ] ) ) {
                    return true;
            }

            return false;
        }
    }

    /**
     * The main function for that returns BD_Map_View
     *
     * The main function responsible for returning the one true BD_Map_View
     * Instance to functions everywhere.
     *
     * Use this function like you would a global variable, except without needing
     * to declare the global.
     *
     *
     * @since 1.0
     * @return object|BD_Map_View The one true BD_Map_View Instance.
     */
    function BD_Map_View()
    {
        return BD_Map_View::instance();
    }

    if ( directorist_is_plugin_active( 'directorist/directorist-base.php' ) ) {
        BD_Map_View(); // get the plugin running
    }
}
