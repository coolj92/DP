<?php
/**
 * Plugin Name: Directorist Gallery
 * Plugin URI: https://directorist.com/product/directorist-image-gallery
 * Description: This is an extension for Directorist plugin. You can display listing image gallery by this extension.
 * Version: 1.3.4
 * Author: wpWax
 * Author URI: https://directorist.com
 * License: GPLv2 or later
 * Text Domain: directorist-gallery
 * Domain Path: /languages
 */

// prevent direct access to the file
defined('ABSPATH') || die('No direct script access allowed!');
if (!class_exists('BD_Gallery')) {
    final class BD_Gallery
    {


        /**
         * @var BD_Gallery The one true BD_Gallery
         * @since 1.0
         */
        private static $instance;
        var $enable_multiple_image = 0;

        /**
         * Main BD_Gallery Instance.
         *
         * Insures that only one instance of BD_Gallery exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @return object|BD_Gallery The one true BD_Gallery
         * @uses BD_Gallery::setup_constants() Setup the constants needed.
         * @uses BD_Gallery::includes() Include the required files.
         * @uses BD_Gallery::load_textdomain() load the language files.
         * @see  BD_Gallery()
         * @since 1.0
         * @static
         * @static_var array $instance
         */
        public static function instance()
        {
            if (!isset(self::$instance) && !(self::$instance instanceof BD_Gallery)) {
                self::$instance = new BD_Gallery;
                self::$instance->setup_constants();
                self::$instance->includes();

                add_filter('atbdp_license_settings_controls', array(self::$instance, 'bdg_license_settings_controls'));
                add_action('plugins_loaded', array(self::$instance, 'load_textdomain'));
                add_action('admin_enqueue_scripts', array(self::$instance, 'load_needed_scripts'));
                add_action('wp_enqueue_scripts', array(self::$instance, 'bdg_scripts_front'));
                add_action('atbdp_listing_meta_admin_submission', array(self::$instance, 'atbdp_gallery_value'));
                add_action('atbdp_after_created_listing', array(self::$instance, 'atbdp_after_created_listing'));
                add_action('atbdp_after_video_gallery', array(self::$instance, 'bdg_gallery_area'));
                add_shortcode('directorist_listing_gallery', array(self::$instance, 'bdg_gellery_shortcode'));
                // add settings fields for our custom settings sections
                add_action('atbdp_before_video_gallery_backend', array(self::$instance, 'atbdp_add_gallery_image'));
                add_filter('atbdp_media_uploader', array(self::$instance, 'atbdp_media_uploader'));
                add_action('init', array(self::$instance, 'everything_has_loaded_and_ready'));
                // license and auto update handler
                add_action('wp_ajax_atbdp_gallery_license_activation', array(self::$instance, 'atbdp_gallery_license_activation'));
                // license deactivation
                add_action('wp_ajax_atbdp_gallery_license_deactivation', array(self::$instance, 'atbdp_gallery_license_deactivation'));

                add_filter( 'atbdp_listing_type_settings_field_list', array( self::$instance, 'atbdp_listing_type_settings_field_list' ) );
                add_filter( 'atbdp_extension_fields', array( self::$instance, 'atbdp_extension_fields' ) );
                add_filter( 'atbdp_extension_settings_submenu', array( self::$instance, 'atbdp_extension_settings_submenus' ) );
            }

            return self::$instance;
        }

        public function atbdp_extension_fields(  $fields ) {
            $fields[] = ['enable_gallery'];
            return $fields;
        }

        public function atbdp_listing_type_settings_field_list( $booking_fields ) {
            $booking_fields['enable_gallery'] = [
                'label'             => __('Gallery Image', BDG_TEXTDOMAIN),
                'type'              => 'toggle',
                'value'             => true,
                'description'       => __('Allow users add and display image gallery for a listing.', BDG_TEXTDOMAIN),
            ];
            $booking_fields['gallery_cropping_ex'] = [
                'label'             => __('Gallery Image Cropping', BDG_TEXTDOMAIN),
                'type'              => 'toggle',
                'value'             => false,
                'description'       => __('If the gallery images are not in the same size, it helps automatically resizing.', BDG_TEXTDOMAIN),
            ];
            $booking_fields['gallery_image_width_ex'] = [
                'label' => __('Custom Width', 'directorist'),
                'type'  => 'number',
                'value' => 251,
                'placeholder' => '251',
                'rules' => [
                    'required' => true,
                    'min' => 1,
                    'max' => 1200,
                ],
            ];
            $booking_fields['gallery_image_height_ex'] = [
                'label' => __('Custom Height', 'directorist'),
                'type'  => 'number',
                'value' => 200,
                'placeholder' => '200',
                'rules' => [
                    'required' => true,
                    'min' => 1,
                    'max' => 1200,
                ],
            ];
            $booking_fields['select_column'] = [
                'label' => __('Select Columns', 'directorist'),
                'type'  => 'select',
                'value' => 'directorist-col-md-4',
                'options' => [
                    [
                        'value' => 'directorist-col-md-6',
                        'label' => __('Column - Two', BDG_TEXTDOMAIN),
                    ],
                    [
                        'value' => 'directorist-col-md-4',
                        'label' => __('Column - Three', BDG_TEXTDOMAIN),
                    ],
                    [
                        'value' => 'directorist-col-md-3',
                        'label' => __('Column - Four', BDG_TEXTDOMAIN),
                    ],
                ],
            ];
            
            return $booking_fields;
        }

        public function atbdp_extension_settings_submenus( $submenu ) {
            $submenu['gallery_submenu'] = [
                'label' => __('Listing Gallery', BDG_TEXTDOMAIN),
                        'icon' => '<i class="fa fa-th"></i>',
                        'sections' => apply_filters( 'atbdp_gallery_settings_controls', [
                            'general_section' => [
                                'title'       => '',
                                'description' => __('You can Customize the form of Gallery Extension here', BDG_TEXTDOMAIN),
                                'fields'      =>  [ 'gallery_cropping_ex', 'gallery_image_width_ex', 'gallery_image_height_ex', 'select_column' ],
                            ],
                        
                        ] ),
            ];
    
            return $submenu;
        }

        public function atbdp_gallery_license_deactivation()
        {
            $license = !empty($_POST['gallery_license']) ? trim($_POST['gallery_license']) : '';
            $options = get_option('atbdp_option');
            $options['gallery_license'] = $license;
            update_option('atbdp_option', $options);
            update_option('directorist_gallery_license', $license);
            $data = array();
            if (!empty($license)) {
                // data to send in our API request
                $api_params = array(
                    'edd_action' => 'deactivate_license',
                    'license' => $license,
                    'item_id' => ATBDP_GALLERY_POST_ID, // The ID of the item in EDD
                    'url' => home_url()
                );
                // Call the custom API.
                $response = wp_remote_post(ATBDP_AUTHOR_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));
                // make sure the response came back okay
                if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

                    $data['msg'] = (is_wp_error($response) && !empty($response->get_error_message())) ? $response->get_error_message() : __('An error occurred, please try again.', BDG_TEXTDOMAIN);
                    $data['status'] = false;

                } else {

                    $license_data = json_decode(wp_remote_retrieve_body($response));
                    if (!$license_data) {
                        $data['status'] = false;
                        $data['msg'] = __('Response not found!', BDG_TEXTDOMAIN);
                        wp_send_json($data);
                        die();
                    }
                    update_option('directorist_gallery_license_status', $license_data->license);
                    if (false === $license_data->success) {
                        switch ($license_data->error) {
                            case 'expired' :
                                $data['msg'] = sprintf(
                                    __('Your license key expired on %s.', BDG_TEXTDOMAIN),
                                    date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                                );
                                $data['status'] = false;
                                break;

                            case 'revoked' :
                                $data['status'] = false;
                                $data['msg'] = __('Your license key has been disabled.', BDG_TEXTDOMAIN);
                                break;

                            case 'missing' :

                                $data['msg'] = __('Invalid license.', BDG_TEXTDOMAIN);
                                $data['status'] = false;
                                break;

                            case 'invalid' :
                            case 'site_inactive' :

                                $data['msg'] = __('Your license is not active for this URL.', BDG_TEXTDOMAIN);
                                $data['status'] = false;
                                break;

                            case 'item_name_mismatch' :

                                $data['msg'] = sprintf(__('This appears to be an invalid license key for %s.', BDG_TEXTDOMAIN), 'Directorist - Image Gallery');
                                $data['status'] = false;
                                break;

                            case 'no_activations_left':

                                $data['msg'] = __('Your license key has reached its activation limit.', BDG_TEXTDOMAIN);
                                $data['status'] = false;
                                break;

                            default :
                                $data['msg'] = __('An error occurred, please try again.', BDG_TEXTDOMAIN);
                                $data['status'] = false;
                                break;
                        }

                    } else {
                        $data['status'] = true;
                        $data['msg'] = __('License deactivated successfully!', BDG_TEXTDOMAIN);
                    }

                }
            } else {
                $data['status'] = false;
                $data['msg'] = __('License not found!', BDG_TEXTDOMAIN);
            }
            wp_send_json($data);
            die();
        }

        public function atbdp_gallery_license_activation()
        {
            $license = !empty($_POST['gallery_license']) ? trim($_POST['gallery_license']) : '';
            $options = get_option('atbdp_option');
            $options['gallery_license'] = $license;
            update_option('atbdp_option', $options);
            update_option('directorist_gallery_license', $license);
            $data = array();
            if (!empty($license)) {
                // data to send in our API request
                $api_params = array(
                    'edd_action' => 'activate_license',
                    'license' => $license,
                    'item_id' => ATBDP_GALLERY_POST_ID, // The ID of the item in EDD
                    'url' => home_url()
                );
                // Call the custom API.
                $response = wp_remote_post(ATBDP_AUTHOR_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));
                // make sure the response came back okay
                if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

                    $data['msg'] = (is_wp_error($response) && !empty($response->get_error_message())) ? $response->get_error_message() : __('An error occurred, please try again.', BDG_TEXTDOMAIN);
                    $data['status'] = false;

                } else {

                    $license_data = json_decode(wp_remote_retrieve_body($response));
                    if (!$license_data) {
                        $data['status'] = false;
                        $data['msg'] = __('Response not found!', BDG_TEXTDOMAIN);
                        wp_send_json($data);
                        die();
                    }
                    update_option('directorist_gallery_license_status', $license_data->license);
                    if (false === $license_data->success) {
                        switch ($license_data->error) {
                            case 'expired' :
                                $data['msg'] = sprintf(
                                    __('Your license key expired on %s.', BDG_TEXTDOMAIN),
                                    date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                                );
                                $data['status'] = false;
                                break;

                            case 'revoked' :
                                $data['status'] = false;
                                $data['msg'] = __('Your license key has been disabled.', BDG_TEXTDOMAIN);
                                break;

                            case 'missing' :

                                $data['msg'] = __('Invalid license.', BDG_TEXTDOMAIN);
                                $data['status'] = false;
                                break;

                            case 'invalid' :
                            case 'site_inactive' :

                                $data['msg'] = __('Your license is not active for this URL.', BDG_TEXTDOMAIN);
                                $data['status'] = false;
                                break;

                            case 'item_name_mismatch' :

                                $data['msg'] = sprintf(__('This appears to be an invalid license key for %s.', BDG_TEXTDOMAIN), 'Directorist - Image Gallery');
                                $data['status'] = false;
                                break;

                            case 'no_activations_left':

                                $data['msg'] = __('Your license key has reached its activation limit.', BDG_TEXTDOMAIN);
                                $data['status'] = false;
                                break;

                            default :
                                $data['msg'] = __('An error occurred, please try again.', BDG_TEXTDOMAIN);
                                $data['status'] = false;
                                break;
                        }

                    } else {
                        $data['status'] = true;
                        $data['msg'] = __('License activated successfully!', BDG_TEXTDOMAIN);
                    }

                }
            } else {
                $data['status'] = false;
                $data['msg'] = __('License not found!', BDG_TEXTDOMAIN);
            }
            wp_send_json($data);
            die();
        }

        public function everything_has_loaded_and_ready()
        {
            $this->enable_multiple_image = is_multiple_images_active() ? 1 : 0; // is the MI Extension is installed???
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
         * @return void
         * @since 1.0
         * @access protected
         */
        public function __clone()
        {
            // Cloning instances of the class is forbidden.
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', BDG_TEXTDOMAIN), '1.0');
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
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', BDG_TEXTDOMAIN), '1.0');
        }

        public function bdg_gallery_area()
        {
            $enable_gallery = get_directorist_option('enable_gallery', 1);
            $gallery_cropping = get_directorist_option('gallery_cropping_ex', 1);
            $gallery_image_width = get_directorist_option('gallery_image_width_ex', 251);
            $gallery_image_height = get_directorist_option('gallery_image_height_ex', 200);
            $gallery_img = get_post_meta(get_the_ID(), '_gallery_img', true);
            $gallery_imgs = (!empty($gallery_img)) ? $gallery_img : array();
            $image_links = array(); // define a link placeholder variable
            $select_columns = get_directorist_option('select_column', 'directorist-col-md-4');
            foreach ($gallery_imgs as $id) {

                if (!empty($gallery_cropping)) {
                    $image_links[$id] = atbdp_image_cropping($id, $gallery_image_width, $gallery_image_height, true, 100)['url'];
                } else {
                    $image_links[$id] = wp_get_attachment_image_src($id, 'large')[0];
                }

            }
            if ($enable_gallery && $image_links) {
                ?>
                <div class="gallery-wrapper">
                    <div class="atbd_content_module__tittle_area">
                        <div class="atbd_area_title">
                            <h4>
                                <span class="fa fa-picture-o atbd_area_icon"></span>
                                <?php esc_attr_e('Gallery', BDG_TEXTDOMAIN); ?>
                            </h4>
                        </div>
                    </div>
                    <div class="gallery-content">
                        <div class="directorist-gallery-grid-two row">
                            <?php if ($image_links) {
                                foreach ($image_links as $image_link) {
                                    ?>
                                    <div class="directorist-grid-item <?php echo $select_columns; ?>">
                                        <figure>
                                            <img src="<?php echo !empty($image_link) ? esc_url($image_link) : ''; ?>"
                                                 alt="<?php esc_attr_e('Details Image', BDG_TEXTDOMAIN); ?>"
                                                 class="img-flusid">
                                            <figcaption><a
                                                        href="<?php echo !empty($image_link) ? esc_url($image_link) : ''; ?>"><span
                                                            class="fa fa-search-plus"></span></a>
                                            </figcaption>
                                        </figure>
                                    </div><!-- ends: .directorist-grid-item -->
                                <?php }
                            } ?>
                        </div>
                    </div><!-- ends: .gallery-content -->
                </div><!-- ends: .gallery-wrapper -->

                <?php
            }
        }

        //gallery shortocode
        public function bdg_gellery_shortcode()
        {
            ob_start();
            if (is_singular(ATBDP_POST_TYPE)) {
                do_action('atbdp_after_video_gallery');
            }
            return ob_get_clean();
        }

        /**
         * It displays settings for the
         * @param $screen  string  get the current screen
         * @since 2.0.0
         */
        public function load_needed_scripts($screen)
        {
            global $typenow;
            if (ATBDP_POST_TYPE == $typenow) {
                $admin_scripts_dependency = array(
                    'jquery',
                );
                wp_enqueue_script('bdg-main-script', plugin_dir_url(__FILE__) . 'admin/assets/js/main.js', $admin_scripts_dependency);
                $i18n_text = array(
                    'confirmation_text' => __('Are you sure', ATBDP_TEXTDOMAIN),
                    'ask_conf_sl_lnk_del_txt' => __('Do you really want to remove this Social Link!', ATBDP_TEXTDOMAIN),
                    'confirm_delete' => __('Yes, Delete it!', ATBDP_TEXTDOMAIN),
                    'deleted' => __('Deleted!', ATBDP_TEXTDOMAIN),
                    'icon_choose_text' => __('Select an icon', ATBDP_TEXTDOMAIN),
                    'upload_image' => __('Select or Upload Slider Image', ATBDP_TEXTDOMAIN),
                    'choose_image' => __('Use this Image', ATBDP_TEXTDOMAIN),
                );
                // is MI extension enabled and active?
                $data = array(
                    'nonce' => wp_create_nonce('atbdp_nonce_action_js'),
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonceName' => 'atbdp_nonce_js',
                    'AdminAssetPath' => BDG_ADMIN_ASSETS,
                    'i18n_text' => $i18n_text,
                    'active_mi_ext' => $this->enable_multiple_image, // 1 or 0
                );
                wp_localize_script('bdg-main-script', 'atbdp_admin_data', $data);
                wp_enqueue_media();
                wp_enqueue_style('admin_main_css', plugin_dir_url(__FILE__) . 'admin/assets/css/main.css');
                wp_enqueue_script('bdg_main_js', plugin_dir_url(__FILE__) . 'admin/assets/js/updater.js', array('jquery'));
                wp_localize_script('bdg_main_js', 'gallery_js_obj', array('ajaxurl' => admin_url('admin-ajax.php')));
            }
            

        }

        /**
         * It displays settings for the
         *
         * @since 1.0.0
         */
        public function bdg_scripts_front()
        {

            $dependency = array(
                'jquery'
            );
            wp_enqueue_script('magnific-popup', plugin_dir_url(__FILE__) . 'public/assets/js/jquery.magnific-popup.min.js', $dependency);
            wp_enqueue_script('gallery-proper', plugin_dir_url(__FILE__) . 'public/assets/js/popper.js', $dependency);
            //wp_enqueue_script('gallery-bootstrap',plugin_dir_url(__FILE__).'public/assets/js/bootstrap.min.js',$dependency);
            wp_enqueue_style('magnific-popup-css', plugin_dir_url(__FILE__) . 'public/assets/css/magnific-popup.css');
            wp_enqueue_style('gallery-style', plugin_dir_url(__FILE__) . 'public/assets/css/style.css');
            //wp_enqueue_style('gallery-bootstrap',plugin_dir_url(__FILE__).'public/assets/css/bootstrap.min.css');
            wp_enqueue_script('bdg-main-font', plugin_dir_url(__FILE__) . 'public/assets/js/main.js', $dependency);
            $i18n_text = array(
                'confirmation_text' => __('Are you sure', ATBDP_TEXTDOMAIN),
                'ask_conf_sl_lnk_del_txt' => __('Do you really want to remove this Social Link!', ATBDP_TEXTDOMAIN),
                'confirm_delete' => __('Yes, Delete it!', ATBDP_TEXTDOMAIN),
                'deleted' => __('Deleted!', ATBDP_TEXTDOMAIN),
                'icon_choose_text' => __('Select an icon', ATBDP_TEXTDOMAIN),
                'upload_image' => __('Select or Upload Slider Image', ATBDP_TEXTDOMAIN),
                'choose_image' => __('Use this Image', ATBDP_TEXTDOMAIN),
            );
            // is MI extension enabled and active?
            $data = array(
                'nonce' => wp_create_nonce('atbdp_nonce_action_js'),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonceName' => 'atbdp_nonce_js',
                'AdminAssetPath' => BDG_FONT_ASSETS,
                'i18n_text' => $i18n_text,
                'active_mi_ext' => $this->enable_multiple_image, // 1 or 0
            );
            wp_localize_script('bdg-main-font', 'atbdp_font_data', $data);
            wp_enqueue_media();

        }

        /**
         * @param $uploders
         * @return array
         */
        public function atbdp_media_uploader($uploders)
        {
            $enable_gallery = get_directorist_option('enable_gallery', 1);
            if ($enable_gallery) {
                // lets push our settings to the end of the other settings field and return it.
                array_push($uploders, [
                    'element_id'        => 'listing_gallery_ext',
                    'meta_name'         => 'gallery_img',
                    'files_meta_name'   => 'files_gallery_meta',
                    'error_msg'         => __('Listing gallery has invalid files', BDG_TEXTDOMAIN),
                ]);
            }
            
            return $uploders;
        }
        
        public function atbdp_gallery_value($metas)
        {
            $metas['_gallery_img'] = !empty($_POST['gallery_img']) ? $_POST['gallery_img'] : array();
            return $metas;
        }

        public function atbdp_after_created_listing($post_id)
        {
            // handling media files
            $listing_images = get_post_meta($post_id, '_gallery_img', true);

            $files = !empty($_FILES["gallery_img"]) ? $_FILES["gallery_img"] : array();
            $files_meta = !empty($_POST['files_gallery_meta']) ? $_POST['files_gallery_meta'] : array();
            if (!empty($listing_images)) {
                foreach ($listing_images as $__old_id) {
                    $match_found = false;
                    if (!empty($files_meta)) {
                        foreach ($files_meta as $__new_id) {
                            $new_id = (int)$__new_id['attachmentID'];
                            if ($new_id === (int)$__old_id) {
                                $match_found = true;
                                break;
                            }
                        }
                    }
                    if (!$match_found) {
                        wp_delete_attachment((int)$__old_id, true);
                    }
                }
            }

            $attach_data = array();
            if ($files) {
                foreach ($files['name'] as $key => $value) {
                    if ($files['name'][$key]) {
                        $file = array(
                            'name' => $files['name'][$key],
                            'type' => $files['type'][$key],
                            'tmp_name' => $files['tmp_name'][$key],
                            'error' => $files['error'][$key],
                            'size' => $files['size'][$key]
                        );
                        $_FILES["my_file_upload"] = $file;
                        $meta_data = [];
                        $meta_data['name'] = $files['name'][$key];
                        $meta_data['id'] = atbdp_handle_attachment("my_file_upload", $post_id);

                        array_push($attach_data, $meta_data);
                    }
                }

            }

            $new_files_meta = [];
            foreach ($files_meta as $key => $value) {

                if ($value['oldFile'] === 'true') {
                    array_push($new_files_meta, $value['attachmentID']);
                }
                if ($value['oldFile'] !== 'true') {
                    foreach ($attach_data as $item) {
                        if ($item['name'] === $value['name']) {
                            $id = $item['id'];
                            array_push($new_files_meta, $id);
                        }
                    }
                }
            }
            update_post_meta($post_id, '_gallery_img', $new_files_meta);
        }

        /**
         * It gets the business hours settings of the given listing/post
         * @param int $post_id The ID of the listing
         * @return array It returns the business hours settings if found, else an empty array.
         */
        public function get_business_hours_settings($post_id)
        {
            $lf = get_post_meta($post_id, '_listing_info', true);
            $listing_info = (!empty($lf)) ? aazztech_enc_unserialize($lf) : array();
            return !empty($listing_info['bdbh_settings']) ? atbdp_sanitize_array($listing_info['bdbh_settings']) : array(); // arrays of settings

        }

        /**
         * It adds the business hour input fields to the add listing page
         *
         */
        public function atbdp_add_gallery_image()
        {
            add_meta_box('atbdp_gallery',
                __('Gallery', ATBDP_TEXTDOMAIN),
                array($this, 'atbdp_gallery'),
                ATBDP_POST_TYPE,
                'normal', 'high');
        }

        public function atbdp_gallery($post)
        {
            $enable_gallery = get_directorist_option('enable_gallery', 1);
            $gallery_image = get_post_meta($post->ID, '_gallery_img', true);
            if ($enable_gallery) {
                ?>
                <div id="directorist" class="directorist atbd_wrapper">
                    <?php self::$instance->load_template('gallery-img-field', compact('gallery_image')); ?>
                </div>
                <style>
                    .directorist-listing-gallery-container .directorist-listing-gallery-single {
                        width: 208px;
                        display: inline-block;
                        position: relative;
                    }

                    .directorist-listing-gallery-container .directorist-listing-gallery-single .directorist-listing-gallery-single__remove {
                        position: absolute;
                        top: -5px;
                        right: -5px;
                        background: #d3d1ec;
                        line-height: 26px;
                        width: 26px;
                        height: 26px;
                        border-radius: 50%;
                        -webkit-transition: 0.2s;
                        -moz-transition: 0.2s;
                        -ms-transition: 0.2s;
                        -o-transition: 0.2s;
                        transition: 0.2s;
                        cursor: pointer;
                        color: #ffffff;
                    }
                </style>
                <?php
            }
        }


        /**
         * It  loads a template file from the Default template directory.
         * @param string $name Name of the file that should be loaded from the template directory.
         * @param array $args Additional arguments that should be passed to the template file for rendering dynamic  data.
         */
        public function load_template($template, $args = array())
        {
            $this->gallery_get_template( $template, $args );
        }

        public function gallery_get_template( $template_file, $args = array() ) {
            if ( is_array( $args ) ) {
                extract( $args );
            }
        
            $theme_template  = '/directorist-gallery/' . $template_file . '.php';
            $plugin_template = BDG_TEMPLATES_DIR . $template_file . '.php';
        
            if ( file_exists( get_stylesheet_directory() . $theme_template ) ) {
                $file = get_stylesheet_directory() . $theme_template;
            } elseif ( file_exists( get_template_directory() . $theme_template ) ) {
                $file = get_template_directory() . $theme_template;
            } else {
                $file = $plugin_template;
            }
            if ( file_exists( $file ) ) {
                include $file;
            }
        }

        public function bdg_license_settings_controls($default)
        {
            $status = get_option('directorist_gallery_license_status');
            if (!empty($status) && ($status !== false && $status == 'valid')) {
                $action = array(
                    'type' => 'toggle',
                    'name' => 'gallery_deactivated',
                    'label' => __('Action', BDG_TEXTDOMAIN),
                    'validation' => 'numeric',
                );
            } else {
                $action = array(
                    'type' => 'toggle',
                    'name' => 'gallery_activated',
                    'label' => __('Action', BDG_TEXTDOMAIN),
                    'validation' => 'numeric',
                );
            }
            $new = apply_filters('atbdp_gallery_license_controls', array(
                'type' => 'section',
                'title' => __('Gallery', BDG_TEXTDOMAIN),
                'description' => __('You can active your Gallery extension here.', BDG_TEXTDOMAIN),
                'fields' => apply_filters('atbdp_gallery_license_settings_field', array(
                    array(
                        'type' => 'textbox',
                        'name' => 'gallery_license',
                        'label' => __('License', BDG_TEXTDOMAIN),
                        'description' => __('Enter your Gallery extension license', BDG_TEXTDOMAIN),
                        'default' => '',
                    ),
                    $action,
                )),
            ));
            $settings = apply_filters('atbdp_licence_menu_for_gallery', true);
            if($settings){
                array_push($default, $new);
            }
            return $default;
        }

        /**
         * It register the text domain to the WordPress
         */
        public function load_textdomain()
        {
            load_plugin_textdomain(BDG_TEXTDOMAIN, false, BDG_LANG_DIR);
        }

        private function includes()
        {
            // setup the updater
            if (!class_exists('EDD_SL_Plugin_Updater')) {
                // load our custom updater if it doesn't already exist
                include(dirname(__FILE__) . '/inc/EDD_SL_Plugin_Updater.php');
            }
            $license_key = trim(get_option('directorist_gallery_license'));
            include(dirname(__FILE__) . '/inc/directory_type.php');
            new Gallery_Post_Type_Manager();
            new EDD_SL_Plugin_Updater(ATBDP_AUTHOR_URL, __FILE__, array(
                'version' => BDG_VERSION,        // current version number
                'license' => $license_key,    // license key (used get_option above to retrieve from DB)
                'item_id' => ATBDP_GALLERY_POST_ID,    // id of this plugin
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
            if ( ! defined( 'BDG_FILE' ) ) { 
                define( 'BDG_FILE', __FILE__ );
            }

            $version = self::get_version_from_file_content( __FILE__ );

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
     * The main function for that returns BD_Gallery
     *
     * The main function responsible for returning the one true BD_Gallery
     * Instance to functions everywhere.
     *
     * Use this function like you would a global variable, except without needing
     * to declare the global.
     *
     *
     * @return object|BD_Gallery The one true BD_Gallery Instance.
     * @since 1.0
     */
    function BD_Gallery()
    {
        return BD_Gallery::instance();
    }

    // Instantiate Directorist Stripe gateway only if our directorist plugin is active
    if ( directorist_is_plugin_active( 'directorist/directorist-base.php' ) ) {
        BD_Gallery(); // get the plugin running
    }
}
