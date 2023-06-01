<?php
/**
 * Plugin Name: Directorist - Business Hour
 * Plugin URI: http://directorist.com/plugins/directorist-business-hours
 * Description: This is an add-on for Directorist Plugin. You can display business hour by this extension.
 * Version: 2.7.7
 * Author: wpWax
 * Author URI: http://wpwax.com
 * License: GPLv2 or later
 * Text Domain: directorist-business-hours
 * Domain Path: /languages
 */

// prevent direct access to the file
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );
if ( ! class_exists( 'BD_Business_Hour' ) ) {
    final class BD_Business_Hour {

        /** Singleton *************************************************************/

        /**
         * @var BD_Business_Hour The one true BD_Business_Hour
         * @since 1.0
         */
        private static $instance;

        /**
         * Main BD_Business_Hour Instance.
         *
         * Insures that only one instance of BD_Business_Hour exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @return object|BD_Business_Hour The one true BD_Business_Hour
         * @uses BD_Business_Hour::setup_constants() Setup the constants needed.
         * @uses BD_Business_Hour::includes() Include the required files.
         * @uses BD_Business_Hour::load_textdomain() load the language files.
         * @see  BD_Business_Hour()
         * @since 1.0
         * @static
         * @static_var array $instance
         */
        public static function instance() {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof BD_Business_Hour ) ) {
                self::$instance = new BD_Business_Hour;
                self::$instance->setup_constants();

                add_action( 'plugins_loaded', [self::$instance, 'load_textdomain'] );
                add_action( 'admin_enqueue_scripts', [self::$instance, 'load_needed_scripts'] );
                add_action( 'wp_enqueue_scripts', [self::$instance, 'load_needed_scripts'] );

                self::$instance->includes();

                // push license settings
                add_filter( 'atbdp_license_settings_controls', [self::$instance, 'businessHours_license_settings_controls'] );
                //register business hour widgets
                add_action( 'widgets_init', [self::$instance, 'register_widget'] );
                add_shortcode( 'directorist_business_hours', [self::$instance, 'display_listings_business_hours'] );
                add_action( 'edit_post', [self::$instance, 'dbh_save_meta_data'] );
                add_filter( 'atbdp_listing_search_query_argument', [self::$instance, 'businessHours_listing_search_query_argument'] );
                // settings
                add_filter( 'atbdp_listing_type_settings_field_list', [self::$instance, 'atbdp_listing_type_settings_field_list'] );
                add_filter( 'atbdp_extension_fields', [self::$instance, 'atbdp_extension_fields'] );
                add_filter( 'atbdp_extension_settings_submenu', [self::$instance, 'atbdp_extension_settings_submenus'] );
                
                add_action( 'wp_ajax_atbdp_business_hours_time', [self::$instance, 'atbdp_business_hours_time'] );
                add_action( 'wp_ajax_nopriv_atbdp_business_hours_time', [self::$instance, 'atbdp_business_hours_time'] );

                add_action( 'wp_ajax_atbdp_business_hours_badge', [self::$instance, 'atbdp_business_hours_badge'] );
                add_action( 'wp_ajax_nopriv_atbdp_business_hours_badge', [self::$instance, 'atbdp_business_hours_badge'] );
            }
            return self::$instance;
        }


        public function atbdp_business_hours_time() {
            
            $listing_id = !empty( $_POST['listing_id'] ) ? sanitize_key( $_POST['listing_id'] ) : '';
            $enable247hour = get_post_meta($listing_id, '_enable247hour', true);

            ob_start();
            directorist_show_open_close_badge( $listing_id );
            $badge = ob_get_clean();

            ob_start();
            if ($enable247hour) {
                $text = get_directorist_option('text247', __("Open 24/7 in a week", 'directorist-business-hours'));
                echo $text;
            } else {
                show_business_hours( $listing_id );
            }

            $hours = ob_get_clean();
            wp_send_json( [
                'hours' => $hours,
                'badge' => $badge,
            ] );
        }


        public function atbdp_business_hours_badge(){

            $listing_ids = !empty( $_POST['listing_ids'] ) ? directorist_clean( wp_unslash( $_POST['listing_ids'] ) ) : [];
            $listing_ids = explode( ',', $listing_ids );

            $response = [];
            if( ! empty( $listing_ids ) ) {
                foreach( $listing_ids as $listing_id ) {
                    ob_start();
                    directorist_show_open_close_badge( $listing_id );
                    $badge = ob_get_clean();

                    $listing_data = [
                        'listing_id' => $listing_id,
                        'badge' => $badge,
                    ];

                    array_push( $response, $listing_data );
                }
            }
            
            wp_send_json( $response );
        }

        public function atbdp_extension_fields( $fields ) {
            $fields[] = ['enable_business_hour'];
            return $fields;
        }

        public function atbdp_listing_type_settings_field_list( $business_fields ) {
            $timezones = DateTimeZone::listIdentifiers( DateTimeZone::ALL );
            $items     = [];
            foreach ( $timezones as $key => $timezone ) {
                $items[] = [
                    'value' => $timezone,
                    'label' => $timezone,
                ];
            }
            $business_fields['enable_business_hour'] = [
                'label'       => __( 'Business Hour', 'directorist-business-hours' ),
                'description' => __( 'Allow users add and display business hour for a listing.', 'directorist-business-hours' ),
                'type'        => 'toggle',
                'value'       => true,
            ];
            $business_fields['open_badge_text'] = [
                'type'  => 'text',
                'label' => __( 'Open Badge Text', 'directorist-business-hours' ),
                'value' => __( 'Open', 'directorist-business-hours' ),
            ];
            $business_fields['close_badge_text'] = [
                'type'  => 'text',
                'label' => __( 'Closed Badge Text', 'directorist-business-hours' ),
                'value' => __( 'Closed', 'directorist-business-hours' ),
            ];
            $business_fields['business_hour_title'] = [
                'type'  => 'text',
                'label' => __( 'Title for Business Hour', 'directorist-business-hours' ),
                'value' => __( 'Business Hour', 'directorist-business-hours' ),
            ];
            $business_fields['text247'] = [
                'type'        => 'text',
                'label'       => __( '24/7 Type Listing Description', 'directorist-business-hours' ),
                'description' => __( 'You can set the text for listing that is open 24 hours a day and 7 days a week here.', 'directorist-business-hours' ),
                'value'       => __( 'Open 24/7', 'directorist-business-hours' ),
            ];
            $business_fields['atbh_time_format'] = [
                'label'   => __( 'Time Format', 'directorist-business-hours' ),
                'type'    => 'select',
                'value'   => '12',
                'options' => [
                    [
                        'value' => '12',
                        'label' => __( '12 Hours', 'directorist-business-hours' ),
                    ],
                    [
                        'value' => '24',
                        'label' => __( '24 Hours', 'directorist-business-hours' ),
                    ],
                ],
            ];
            $business_fields['timezone'] = [
                'label'   => __( 'Default Timezone', 'directorist-business-hours' ),
                'type'    => 'select',
                'value'   => __( 'America/New_York', 'directorist-business-hours' ),
                'options' => $items,
            ];
            $business_fields['atbh_display_single_listing'] = [
                'label' => __( 'Display Business Hours on Single Listing', 'directorist-business-hours' ),
                'type'  => 'toggle',
                'value' => true,
            ];
            $business_fields['cache_plugin_compatibility'] = [
                'label' => __( 'Cache Plugin Compatibility', 'directorist-business-hours' ),
                'description' => __( 'If enabled Business hours are served using Ajax and prevents caching.', 'directorist-business-hours' ),
                'type'  => 'toggle',
                'value' => false,
            ];

            return $business_fields;
        }

        public function atbdp_extension_settings_submenus( $submenu ) {
            $submenu['business_hours'] = [
                'label'    => __( 'Business Hour', 'directorist-business-hours' ),
                'icon'     => '<i class="far fa-clock"></i>',
                'sections' => apply_filters( 'atbdp_business_hours_settings_controls', [
                    'general_section' => [
                        'title'       => __( 'Business Hour Settings', 'directorist-business-hours' ),
                        'description' => __( 'You can Customize all the settings of Business Hour Extension here', 'directorist-business-hours' ),
                        'fields'      => ['open_badge_text', 'close_badge_text', 'business_hour_title', 'text247', 'atbh_time_format', 'timezone', 'atbh_display_single_listing', 'cache_plugin_compatibility' ],
                    ],
                ] ),
            ];

            return $submenu;
        }

        public function businessHours_listing_search_query_argument( $args ) {
            // filter by open now business
            if (  ( ! empty( $_GET['open_now'] ) && ( 'open_now' == $_GET['open_now'] ) ) || ! empty( $_POST['open_now'] ) ) {

                // @todo performence
                $query_args = array(
                    'post_type'      => 'at_biz_dir',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'fields'         => 'ids'
                );
        
                $listings = new \WP_Query( $query_args );

                if ( $listings->have_posts() ) {
                    
                    $opend = [];

                    foreach( $listings->posts as $listing_id ) {

                        $is_open = directorist_business_open_close_status( $listing_id );
                        
                        if ( $is_open ) {
                            $opend[] = $listing_id;
                        }
                    }

                    $new_args = [
                        'post__in' => ! empty( $opend ) ? $opend : [0],
                    ];

                    $args = array_merge( $args, $new_args );

                    return $args;

                }

            } else {
                return $args;
            }

        }

        private function __construct() {
            /*making it private prevents constructing the object*/

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
        public function __clone() {
            // Cloning instances of the class is forbidden.
            _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'directorist-business-hours' ), '1.0' );
        }

        /**
         * Disable unserializing of the class.
         *
         * @return void
         * @since 1.0
         * @access protected
         */
        public function __wakeup() {
            // Unserializing instances of the class is forbidden.
            _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'directorist-business-hours' ), '1.0' );
        }

        public function dbh_save_meta_data( $post_id ) {

            if ( is_admin() ) {
                $timezone = ! empty( $_POST['timezone'] ) ? sanitize_text_field( $_POST['timezone'] ) : '';
                if ( ! empty( $timezone ) ) {
                    update_post_meta( $post_id, '_timezone', $timezone );
                }
            }

        }

        /**
         * It displays settings for the
         * @param $screen  string  get the current screen
         * @since 2.0.0
         */
        public function load_needed_scripts( $screen ) {
            if ( is_rtl() ) {
                wp_enqueue_style( 'bdbh_main_style_rtl', plugin_dir_url( __FILE__ ) . '/assets/css/bh-main-rtl.css' );
            } else {
                wp_enqueue_style( 'bdbh_main_style2', plugin_dir_url( __FILE__ ) . '/assets/css/bh-main.css' );
            }
            wp_enqueue_style( 'bdbh_main_style', plugin_dir_url( __FILE__ ) . '/assets/css/bh-main.css' );
            wp_enqueue_script( 'bdbh_main_script', plugin_dir_url( __FILE__ ) . '/assets/js/main.js', ['jquery'], false, true );
            $data = [
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'cache_plugin_compatibility' => get_directorist_option( 'cache_plugin_compatibility', false ),
            ];
            wp_localize_script( 'bdbh_main_script', 'atbdp_business_hours', $data );
        }

        /**
         *It registers business hours widget
         */
        public function register_widget() {

            register_widget( 'BD_Business_Hour_Widget' );
        }

        /**
         * It  loads a template file from the Default template directory.
         * @param string $name Name of the file that should be loaded from the template directory.
         * @param array $args Additional arguments that should be passed to the template file for rendering dynamic  data.
         */
        public function load_template( $template, $args = [] ) {
            directorist_business_hours_get_template( $template, $args );
        }

        /**
         * It register the text domain to the WordPress
         */
        public function load_textdomain() {
            load_plugin_textdomain( 'directorist-business-hours', false, BDBH_LANG_DIR );
        }

        /**
         * It Includes and requires necessary files.
         *
         * @access private
         * @return void
         * @since 1.0
         */
        private function includes() {
            require_once BDBH_INC_DIR . 'helper-functions.php';
            require_once BDBH_INC_DIR . 'directory_types_manager.php';
            require_once BDBH_DIR . 'widgets/class-widget.php';

            new DBH_Directory_Type();

        }

        /**
         * Setup plugin constants.
         *
         * @access private
         * @return void
         * @since 1.0
         */
        private function setup_constants() {
            if ( ! defined( 'BDBH_FILE' ) ) {define( 'BDBH_FILE', __FILE__ );}

            require_once plugin_dir_path( __FILE__ ) . '/config-helper.php'; // loads constant from a file so that it can be available on all files.
            require_once plugin_dir_path( __FILE__ ) . '/config.php'; // loads constant from a file so that it can be available on all files.
        }

        /*
         * display listings business hours via shortocde
         * */
        public function display_listings_business_hours() {
            global $post;
            $listing_id = $post->ID;
            ob_start();
            if ( is_singular( ATBDP_POST_TYPE ) ) {
                do_action( 'atbdp_business_hours', $listing_id );
            }
            return ob_get_clean();
        }
    }

    if ( ! function_exists( 'directorist_is_plugin_active' ) ) {
        function directorist_is_plugin_active( $plugin ) {
            return in_array( $plugin, (array) get_option( 'active_plugins', [] ), true ) || directorist_is_plugin_active_for_network( $plugin );
        }
    }

    if ( ! function_exists( 'directorist_is_plugin_active_for_network' ) ) {
        function directorist_is_plugin_active_for_network( $plugin ) {
            if ( ! is_multisite() ) {
                return false;
            }

            $plugins = get_site_option( 'active_sitewide_plugins' );
            if ( isset( $plugins[$plugin] ) ) {
                return true;
            }

            return false;
        }
    }

    /**
     * The main function for that returns BD_Business_Hour
     *
     * The main function responsible for returning the one true BD_Business_Hour
     * Instance to functions everywhere.
     *
     * Use this function like you would a global variable, except without needing
     * to declare the global.
     *
     *
     * @return object|BD_Business_Hour The one true BD_Business_Hour Instance.
     * @since 1.0
     */
    function BD_Business_Hour() {
        return BD_Business_Hour::instance();
    }

    if ( directorist_is_plugin_active( 'directorist/directorist-base.php' ) ) {
        BD_Business_Hour(); // get the plugin running
    }
}