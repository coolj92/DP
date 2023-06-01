<?php
/**
 * Plugin Name: Directorist - Listings FAQs
 * Plugin URI: https://directorist.com/product/directorist-listing-faqs/
 * Description: This is an extension for Directorist Plugin. You can display frequently asked questions of a listing easily by this extension.
 * Version: 1.3.6
 * Author: wpWax
 * Author URI: https://wpwax.com
 * License: GPLv2 or later
 * Text Domain: directorist-faqs
 * Domain Path: /languages
 */


// prevent direct access to the file
defined('ABSPATH') || die('No direct script access allowed!');
if (!class_exists('Listings_fAQs')){
    final class Listings_fAQs
    {


        /** Singleton *************************************************************/

        /**
         * @var Listings_fAQs The one true Listings_fAQs
         * @since 1.0
         */
        private static $instance;

        /**
         * Main Listings_fAQs Instance.
         *
         * Insures that only one instance of Listings_fAQs exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @return object|Listings_fAQs The one true Listings_fAQs
         * @uses Listings_fAQs::setup_constants() Setup the constants needed.
         * @uses Listings_fAQs::includes() Include the required files.
         * @uses Listings_fAQs::load_textdomain() load the language files.
         * @see  Listings_fAQs()
         * @since 1.0
         * @static
         * @static_var array $instance
         */
        public static function instance()
        {
            if (!isset(self::$instance) && !(self::$instance instanceof Listings_fAQs)) {
                self::$instance = new Listings_fAQs;
                self::$instance->setup_constants();

                add_action('plugins_loaded', array(self::$instance, 'load_textdomain'));
                add_action('admin_enqueue_scripts', array(self::$instance, 'load_needed_scripts_admin'));
                add_action('wp_enqueue_scripts', array(self::$instance, 'load_needed_scripts'));

                self::$instance->includes();
                add_filter('atbdp_license_settings_controls', array(self::$instance, 'license_settings_controls'));
                //register business hour widgets
                /*
                 * @todo later need to active the FAQs widget
                 */
                add_action('widgets_init', array(self::$instance, 'register_widget'));

                //settings 
                add_filter( 'atbdp_listing_type_settings_field_list', array( self::$instance, 'atbdp_listing_type_settings_field_list' ) );
                add_filter( 'atbdp_extension_fields', array( self::$instance, 'atbdp_extension_fields' ) );
                add_filter( 'atbdp_extension_settings_submenu', array( self::$instance, 'atbdp_extension_settings_submenus' ) );

                add_action('atbdp_after_video_metabox_backend_add_listing', array(self::$instance, 'atbdp_new_metabox'));
                add_action('wp_ajax_atbdp_faqs_handler', array(self::$instance, 'atbdp_faqs_ajax_handler'));
                add_action('wp_ajax_nopriv_atbdp_faqs_handler', array(self::$instance, 'atbdp_faqs_ajax_handler'));
                add_action('atbdp_listing_faqs', array(self::$instance, 'atbdp_show_faqs'), 10, 2);
                add_shortcode('directorist_listing_faqs', array(self::$instance, 'atbdp_shortcode_faqa'));
            }

            // license and auto update handler
            add_action('wp_ajax_atbdp_faqs_license_activation', array(self::$instance, 'atbdp_faqs_license_activation'));
            // license deactivation
            add_action('wp_ajax_atbdp_faqs_license_deactivation', array(self::$instance, 'atbdp_faqs_license_deactivation'));

            return self::$instance;
        }

        private function __construct()
        {
            /*making it private prevents constructing the object*/

        }

        public function atbdp_extension_fields(  $fields ) {
            $fields[] = ['enable_faqs'];
            return $fields;
        }
    
        public function atbdp_listing_type_settings_field_list( $faq_fields ) {
            $faq_fields['enable_faqs'] = [
                'label'             => __('Enable FAQs', 'directorist-faqs'),
                'description'       => __('Allow users add FAQs for a listing.', 'directorist-faqs'),
                'type'              => 'toggle',
                'value'             => true,
            ];
            $faq_fields['faqs_ans_box'] = [
                'label'     => __('Answer Field Type', 'directorist-faqs'),
                'type'      => 'select',
                'value'     => 'normal',
                'options'   => [
                    [
                        'value' => 'wpeditor',
                        'label' => __('WP Editor', 'directorist-faqs'),
                    ], 
                    [
                        'value' => 'normal',
                        'label' => __('Textarea', 'directorist-faqs'),
                    ]
                ],
            ];
            
            return $faq_fields;
        }
    
        public function atbdp_extension_settings_submenus( $submenu ) {
            $submenu['faq'] = [
                'label' => __('FAQs', 'directorist-faqs'),
                        'icon'       => '<i class="fa fa-question"></i>',
                        'sections'   => apply_filters( 'atbdp_faq_settings_controls', [
                            'general_section' => [
                                'title'       => __('FAQs Settings', 'directorist-faqs'),
                                'fields'      =>  [ 'faqs_ans_box' ],
                            ],
                        ] ),
            ];
    
            return $submenu;
        }

        /**
         * Throw error on object clone.
         *
         * The whole idea of the singleton design pattern is that there is a single
         * object therefore, we don't want the object to be cloned.
         *
         * @return void
         * @since 1.0
         * @access protected
         */
        public function __clone()
        {
            // Cloning instances of the class is forbidden.
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'directorist-faqs'), '1.0');
        }

        /**
         * Disable unserializing of the class.
         *
         * @return void
         * @since 1.0
         * @access protected
         */
        public function __wakeup()
        {
            // Unserializing instances of the class is forbidden.
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'directorist-faqs'), '1.0');
        }

        public function atbdp_faqs_license_deactivation()
        {
            $license = !empty($_POST['faqs_license']) ? trim($_POST['faqs_license']) : '';
            $options = get_option('atbdp_option');
            $options['faqs_license'] = $license;
            update_option('atbdp_option', $options);
            update_option('directorist_faqs_license', $license);
            $data = array();
            if (!empty($license)) {
                // data to send in our API request
                $api_params = array(
                    'edd_action' => 'deactivate_license',
                    'license' => $license,
                    'item_id' => ATBDP_FAQS_POST_ID, // The ID of the item in EDD
                    'url' => home_url()
                );
                // Call the custom API.
                $response = wp_remote_post(ATBDP_AUTHOR_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));
                // make sure the response came back okay
                if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

                    $data['msg'] = (is_wp_error($response) && !empty($response->get_error_message())) ? $response->get_error_message() : __('An error occurred, please try again.', 'directorist-faqs');
                    $data['status'] = false;

                } else {

                    $license_data = json_decode(wp_remote_retrieve_body($response));
                    if (!$license_data) {
                        $data['status'] = false;
                        $data['msg'] = __('Response not found!', 'directorist-faqs');
                        wp_send_json($data);
                        die();
                    }
                    update_option('directorist_faqs_license_status', $license_data->license);
                    if (false === $license_data->success) {
                        switch ($license_data->error) {
                            case 'expired' :
                                $data['msg'] = sprintf(
                                    __('Your license key expired on %s.', 'directorist-faqs'),
                                    date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                                );
                                $data['status'] = false;
                                break;

                            case 'revoked' :
                                $data['status'] = false;
                                $data['msg'] = __('Your license key has been disabled.', 'directorist-faqs');
                                break;

                            case 'missing' :

                                $data['msg'] = __('Invalid license.', 'directorist-faqs');
                                $data['status'] = false;
                                break;

                            case 'invalid' :
                            case 'site_inactive' :

                                $data['msg'] = __('Your license is not active for this URL.', 'directorist-faqs');
                                $data['status'] = false;
                                break;

                            case 'item_name_mismatch' :

                                $data['msg'] = sprintf(__('This appears to be an invalid license key for %s.', 'directorist-faqs'), 'Directorist - Listing FAQs');
                                $data['status'] = false;
                                break;

                            case 'no_activations_left':

                                $data['msg'] = __('Your license key has reached its activation limit.', 'directorist-faqs');
                                $data['status'] = false;
                                break;

                            default :
                                $data['msg'] = __('An error occurred, please try again.', 'directorist-faqs');
                                $data['status'] = false;
                                break;
                        }

                    } else {
                        $data['status'] = true;
                        $data['msg'] = __('License deactivated successfully!', 'directorist-faqs');
                    }

                }
            } else {
                $data['status'] = false;
                $data['msg'] = __('License not found!', 'directorist-faqs');
            }
            wp_send_json($data);
            die();
        }

        public function atbdp_faqs_license_activation()
        {
            $license = !empty($_POST['faqs_license']) ? trim($_POST['faqs_license']) : '';
            $options = get_option('atbdp_option');
            $options['faqs_license'] = $license;
            update_option('atbdp_option', $options);
            update_option('directorist_faqs_license', $license);
            $data = array();
            if (!empty($license)) {
                // data to send in our API request
                $api_params = array(
                    'edd_action' => 'activate_license',
                    'license' => $license,
                    'item_id' => ATBDP_FAQS_POST_ID, // The ID of the item in EDD
                    'url' => home_url()
                );
                // Call the custom API.
                $response = wp_remote_post(ATBDP_AUTHOR_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));
                // make sure the response came back okay
                if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

                    $data['msg'] = (is_wp_error($response) && !empty($response->get_error_message())) ? $response->get_error_message() : __('An error occurred, please try again.', 'directorist-faqs');
                    $data['status'] = false;

                } else {

                    $license_data = json_decode(wp_remote_retrieve_body($response));
                    if (!$license_data) {
                        $data['status'] = false;
                        $data['msg'] = __('Response not found!', 'directorist-faqs');
                        wp_send_json($data);
                        die();
                    }
                    update_option('directorist_faqs_license_status', $license_data->license);
                    if (false === $license_data->success) {
                        switch ($license_data->error) {
                            case 'expired' :
                                $data['msg'] = sprintf(
                                    __('Your license key expired on %s.', 'directorist-faqs'),
                                    date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                                );
                                $data['status'] = false;
                                break;

                            case 'revoked' :
                                $data['status'] = false;
                                $data['msg'] = __('Your license key has been disabled.', 'directorist-faqs');
                                break;

                            case 'missing' :

                                $data['msg'] = __('Invalid license.', 'directorist-faqs');
                                $data['status'] = false;
                                break;

                            case 'invalid' :
                            case 'site_inactive' :

                                $data['msg'] = __('Your license is not active for this URL.', 'directorist-faqs');
                                $data['status'] = false;
                                break;

                            case 'item_name_mismatch' :

                                $data['msg'] = sprintf(__('This appears to be an invalid license key for %s.', 'directorist-faqs'), 'Directorist - Listing FAQs');
                                $data['status'] = false;
                                break;

                            case 'no_activations_left':

                                $data['msg'] = __('Your license key has reached its activation limit.', 'directorist-faqs');
                                $data['status'] = false;
                                break;

                            default :
                                $data['msg'] = __('An error occurred, please try again.', 'directorist-faqs');
                                $data['status'] = false;
                                break;
                        }

                    } else {
                        $data['status'] = true;
                        $data['msg'] = __('License activated successfully!', 'directorist-faqs');
                    }

                }
            } else {
                $data['status'] = false;
                $data['msg'] = __('License not found!', 'directorist-faqs');
            }
            wp_send_json($data);
            die();
        }

        public function license_settings_controls($default)
        {
            $status = get_option('directorist_faqs_license_status');
            if (!empty($status) && ($status !== false && $status == 'valid')) {
                $action = array(
                    'type' => 'toggle',
                    'name' => 'faqs_deactivated',
                    'label' => __('Action', 'directorist-faqs'),
                    'validation' => 'numeric',
                );
            } else {
                $action = array(
                    'type' => 'toggle',
                    'name' => 'faqs_activated',
                    'label' => __('Action', 'directorist-faqs'),
                    'validation' => 'numeric',
                );
            }
            $new = apply_filters('atbdp_faqs_license_controls', array(
                'type' => 'section',
                'title' => __('FAQs', 'directorist-faqs'),
                'description' => __('You can active your FAQs extension here.', 'directorist-faqs'),
                'fields' => apply_filters('atbdp_faqs_license_settings_field', array(
                    array(
                        'type' => 'textbox',
                        'name' => 'faqs_license',
                        'label' => __('License', 'directorist-faqs'),
                        'description' => __('Enter your FAQs extension license', 'directorist-faqs'),
                        'default' => '',
                    ),
                    $action,
                )),
            ));
            $settings = apply_filters('atbdp_licence_menu_for_faqs', true);
            if($settings){
                array_push($default, $new);
            }
            return $default;
        }

        public function atbdp_show_faqs($post, $listing_info)
        {
            $plan_faqs = true;
            if (is_fee_manager_active()) {
                $plan_faqs = is_plan_allowed_listing_faqs(get_post_meta(get_the_ID(), '_fm_plans', true));
            }
            if ($plan_faqs) {
                $faqs = !empty($listing_info['faqs']) ? $listing_info['faqs'] : array();
                if (!empty($faqs)) {
                    self::$instance->load_template('view-faqs', array('listing_faq' => $faqs,));
                }
            }

        }

        public function atbdp_shortcode_faqa()
        {
            global $post;
            $listing_info['faqs'] = get_post_meta($post->ID, '_faqs', true);
            extract($listing_info);
            ob_start();
            if (is_singular(ATBDP_POST_TYPE)) {
                $plan_faqs = true;
                if (is_fee_manager_active()) {
                    $plan = get_post_meta($post->ID, '_fm_plans', true);
                    $plan_faqs = is_plan_allowed_listing_faqs($plan);
                }
                if ($plan_faqs) {
                    do_action('atbdp_listing_faqs', $post, $listing_info);
                }
            }
            return ob_get_clean();
        }


        public function atbdp_new_metabox()
        {
            add_meta_box('_listing_faqs',
                __('Add FAQs for the Listing', 'directorist-faqs'),
                array($this, 'add_new_faq_admin'),
                ATBDP_POST_TYPE,
                'normal', 'high');
        }


        public function add_new_faq_admin($post)
        {
            if (!get_directorist_option('enable_faqs', 1)) return; // vail if the business hour is not enabled
            ?>
            <div id="directorist" class="directorist atbd_wrapper">
                <?php
                $listing_info = get_post_meta($post->ID, '_faqs', true);
                $faqs = !empty($listing_info) ? $listing_info : array();
                self::$instance->load_template('add-faq', array('listing_faq' => $faqs));
                ?>
            </div>

            <?php
        }

        public function atbdp_faqs_ajax_handler()
        {
            $id = (!empty($_POST['id'])) ? absint($_POST['id']) : 0;
            self::$instance->load_template('ajax/faqs-ajax', array('id' => $id,));
            die();
        }

        public function load_needed_scripts($screen)
        {
            wp_enqueue_script('listing_faqs', plugin_dir_url(__FILE__) . '/assets/js/main.js', array( 'jquery', 'jquery-ui-draggable', 'jquery-ui-sortable' ), true);
            if (is_rtl()) {
                wp_enqueue_style('faqs_main_style_rtl', plugin_dir_url(__FILE__) . '/assets/css/main-rtl.css');
            } else {
                wp_enqueue_style('faqs_main_style', plugin_dir_url(__FILE__) . '/assets/css/main.css');
            }
            $faqs_ans_box = get_directorist_option('faqs_ans_box', 'normal');
            $l10n = array(
                'ans_field' => $faqs_ans_box,
                'nonceName' => 'nonceName',
                'ajaxurl' => admin_url('admin-ajax.php'),
            );
            wp_localize_script('listing_faqs', 'listing_faqs', $l10n);
        }

        public function load_needed_scripts_admin($screen)
        {
            $post_type = get_post_type(get_the_ID());
            if (('post-new.php' == $screen) || ('post.php' == $screen) || ($post_type == 'at_biz_dir')) {

                wp_enqueue_script('listing_faqs_admin', plugin_dir_url(__FILE__) . '/assets/js/admin-main.js', array( 'jquery', 'jquery-ui-draggable', 'jquery-ui-sortable' ), true);
                wp_enqueue_style('faqs_main_style', plugin_dir_url(__FILE__) . '/assets/css/main.css');

            }
            if (isset($_GET['page']) && ('aazztech_settings' === $_GET['page'])) {
                wp_enqueue_style('faqs_main_style', plugin_dir_url(__FILE__) . '/assets/css/main.css');
                wp_enqueue_script('listing_faqs_admin', plugin_dir_url(__FILE__) . '/assets/js/admin-main.js', array( 'jquery', 'jquery-ui-draggable', 'jquery-ui-sortable' ), true);

            }
            $i18n_text = array(
                'confirmation_text' => __('Are you sure', 'directorist-faqs'),
                'ask_conf_sl_lnk_del_txt' => __('Do you really want to remove this FAQ!', 'directorist-faqs'),
                'confirm_delete' => __('Yes, Delete it!', 'directorist-faqs'),
                'deleted' => __('Deleted!', 'directorist-faqs'),
                'icon_choose_text' => __('Select an icon', 'directorist-faqs'),
                'upload_image' => __('Select or Upload Slider Image', 'directorist-faqs'),
                'upload_cat_image' => __('Select Category Image', 'directorist-faqs'),
                'choose_image' => __('Use this Image', 'directorist-faqs'),
                'select_prv_img' => __('Select Preview Image', 'directorist-faqs'),
                'insert_prv_img' => __('Insert Preview Image', 'directorist-faqs'),
            );

            // is MI extension enabled and active?
            $data = array(
                'nonce'             => wp_create_nonce('atbdp_nonce_action_js'),
                'ajaxurl'           => admin_url('admin-ajax.php'),
                'nonceName'         => 'atbdp_nonce_js',
                'AdminAssetPath'    => ATBDP_ADMIN_ASSETS,
                'i18n_text'         => $i18n_text,
                'ans_field'         => get_directorist_option('faqs_ans_box', 'normal'),
            );

            wp_localize_script('listing_faqs_admin', 'listing_faqs_js_obj', $data);
        }

        /**
         *It registers business hours widget
         */
        public function register_widget()
        {

            register_widget('FAQs_Widget');
        }

        /**
         * It  loads a template file from the Default template directory.
         * @param string $name Name of the file that should be loaded from the template directory.
         * @param array $args Additional arguments that should be passed to the template file for rendering dynamic  data.
         */
        public function load_template($template, $args = array())
        {
            dfaqs_get_template( $template, $args );
        }

        /**
         * It register the text domain to the WordPress
         */
        public function load_textdomain()
        {
            load_plugin_textdomain('directorist-faqs', false, FAQS_LANG_DIR);
        }

        /**
         * It Includes and requires necessary files.
         *
         * @access private
         * @return void
         * @since 1.0
         */
        private function includes()
        {
            require_once FAQS_INC_DIR . 'helper-functions.php';
            require_once FAQS_INC_DIR . 'directory_type.php';
            require_once FAQS_DIR . 'widgets/class-widget.php';
            new FAQS_Post_Type_Manager();
            // setup the updater
            if (!class_exists('EDD_SL_Plugin_Updater')) {
                // load our custom updater if it doesn't already exist
                include(dirname(__FILE__) . '/inc/EDD_SL_Plugin_Updater.php');
            }
            $license_key = trim(get_option('directorist_faqs_license'));
            new EDD_SL_Plugin_Updater(ATBDP_AUTHOR_URL, __FILE__, array(
                'version' => FAQS_VERSION,        // current version number
                'license' => $license_key,    // license key (used get_option above to retrieve from DB)
                'item_id' => ATBDP_FAQS_POST_ID,    // id of this plugin
                'author' => 'AazzTech',    // author of this plugin
                'url' => home_url(),
                'beta' => false // set to true if you wish customers to receive update notifications of beta releases
            ));
        }

        public static function get_version_from_file_content( $file_path = '' ) {
            $version = '';
    
            if ( ! file_exists( $file_path ) ) {
                return $version;
            }
    
            $content = file_get_contents( $file_path );
            $version = self::get_version_from_content( $content );
            
            return $version;
        }

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
         * @return void
         * @since 1.0
         */
        private function setup_constants()
        {
            if ( ! defined( 'FAQS_FILE' ) ) { define( 'FAQS_FILE', __FILE__ ); }
            $version = self::get_version_from_file_content( FAQS_FILE );

            require_once plugin_dir_path(__FILE__) . '/config.php'; // loads constant from a file so that it can be available on all files.
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
     * The main function for that returns Listings_fAQs
     *
     * The main function responsible for returning the one true Listings_fAQs
     * Instance to functions everywhere.
     *
     * Use this function like you would a global variable, except without needing
     * to declare the global.
     *
     *
     * @return object|Listings_fAQs The one true Listings_fAQs Instance.
     * @since 1.0
     */
    function Listings_fAQs()
    {
        return Listings_fAQs::instance();
    }

    // Instantiate Directorist Stripe gateway only if our directorist plugin is active
    if ( directorist_is_plugin_active( 'directorist/directorist-base.php' ) ) {
        Listings_fAQs(); // get the plugin running
    }
}