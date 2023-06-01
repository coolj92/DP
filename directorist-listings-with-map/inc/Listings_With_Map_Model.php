<?php
if (!class_exists('Listings_With_Map_Model')) :
    class Listings_With_Map_Model
    {
        public $options                = [];
        public $attributes             = [];
        public $data                   = [];
        public $setup_ajax_data_status = null;

        // setup_listing_data
        public function setup_listing_data()
        {
            global $atbdp_template_data;
            $this->data['listings'] = $atbdp_template_data['listings'];

            $this->set_default_options();
            $this->set_default_data();

            $this->parse_listings_attributes();
            $this->set_listing_data();

            $this->enqueue_assets();
        }

        // setup_ajax_data
        public function setup_ajax_data() {


                $this->parse_ajax_query_args();
                $this->set_default_options();
                $this->set_ajax_options();

                $this->set_default_data();
                $this->parse_listings_attributes();
                $this->set_listing_data();

                $this->render_ajax_view();


        }

        // parse_ajax_query
        public function parse_ajax_query_args() {
            $paged = atbdp_get_paged_num();

            if ( isset($_POST['pageno']) ) {
                $paged = $_POST['pageno'];
            }


            $category_slug = isset($_POST['category_slug']) ? $_POST['category_slug'] : '';
            $location_slug = isset($_POST['location_slug']) ? $_POST['location_slug'] : '';
            $tag_slug      = isset($_POST['tag_slug']) ? $_POST['tag_slug'] : '';

            $show_pagination   = isset($_POST['show_pagination']) ? esc_html($_POST['show_pagination']) : 'yes';
            $listings_per_page = isset($_POST['listings_per_page']) ? esc_html($_POST['listings_per_page']) : 6;
            $enable_multi_directory = get_directorist_option( 'enable_multi_directory', true );
            $args = array(
                'post_type' => ATBDP_POST_TYPE,
                'post_status' => 'publish',
                'posts_per_page' => $listings_per_page,
            );

            if ('yes' == $show_pagination) {
                $args['paged'] = $paged;
            }
            if (isset($_POST['key']) && !empty($_POST['key'])) {
                $args['s'] = $_POST['key'];
            }

            if (!empty($tag_slug)) {
                $tax_queries[] = array(
                    'taxonomy' => ATBDP_TAGS,
                    'field' => 'slug',
                    'terms' => $tag_slug,
                    'include_children' => true,
                );
                $args['tax_query'] = $tax_queries;
            }
            if (!empty($category_slug)) {
                $tax_queries[] = array(
                    'taxonomy' => ATBDP_CATEGORY,
                    'field' => 'slug',
                    'terms' => $category_slug,
                    'include_children' => true,
                );
                $args['tax_query'] = $tax_queries;
            }
            if (!empty($location_slug) && empty($tag_slug)) {
                $tax_queries[] = array(
                    'taxonomy' => ATBDP_LOCATION,
                    'field' => 'slug',
                    'terms' => $location_slug,
                    'include_children' => true,
                );
                $args['tax_query'] = $tax_queries;
            }

            $tax_queries = array(); // initiate the tax query var to append to it different tax query

            if( ! empty( $enable_multi_directory ) ) {
                if ( ! empty( $_POST['directory_type'] ) ) {
                    $tax_queries[] = array(
                        'taxonomy' => ATBDP_TYPE,
                        'field' => 'term_id',
                        'terms' => $_POST['directory_type'],
                    );
                }
            }
            if (isset($_POST['location']) && (int)$_POST['location'] > 0) {

                $tax_queries[] = array(
                    'taxonomy' => ATBDP_LOCATION,
                    'field' => 'term_id',
                    'terms' => (int)$_POST['location'],
                    'include_children' => true,
                );
            }
            if (isset($_POST['category']) && (int)$_POST['category'] > 0) {
                $tax_queries[] = array(
                    'taxonomy' => ATBDP_CATEGORY,
                    'field' => 'term_id',
                    'terms' => (int)$_POST['category'],
                    'include_children' => true,
                );
            }
            if (isset($_POST['tag']) && (int)$_POST['tag'] > 0) {
                $tax_queries[] = array(
                    'taxonomy' => ATBDP_TAGS,
                    'field' => 'term_id',
                    'terms' => (int)$_POST['tag'],
                );
            }
            $count_tax_queries = count($tax_queries);
            if ($count_tax_queries) {
                $args['tax_query'] = ($count_tax_queries > 1) ? array_merge(array('relation' => 'AND'), $tax_queries) : $tax_queries;
            }
            $meta_queries = array();
            if ( ! empty( $_POST['price'] ) ) {
                $price = array_filter($_POST['price']);

                if ($n = count($price)) {

                    if (2 == $n) {
                        $meta_queries[] = array(
                            'key' => '_price',
                            'value' => array_map('intval', $price),
                            'type' => 'NUMERIC',
                            'compare' => 'BETWEEN'
                        );
                    } else {
                        if (empty($price[0])) {
                            $meta_queries[] = array(
                                'key' => '_price',
                                'value' => (int)$price[1],
                                'type' => 'NUMERIC',
                                'compare' => '<='
                            );
                        } else {
                            $meta_queries[] = array(
                                'key' => '_price',
                                'value' => (int)$price[0],
                                'type' => 'NUMERIC',
                                'compare' => '>='
                            );
                        }
                    }
                }
            } // end price
            // for d-service
            $meta_queries[] = array(
                'relation' => 'OR',
                array(
                    'key' => '_need_post',
                    'value' => 'no',
                    'compare' => '=',
                ),
                array(
                    'key' => '_need_post',
                    'compare' => 'NOT EXISTS',
                )
            );
            if (isset($_POST['price_range']) && 'none' != $_POST['price_range']) {
                $price_range = $_POST['price_range'];
                $meta_queries[] = array(
                    'key' => '_price_range',
                    'value' => $price_range,
                    'compare' => 'LIKE'
                );
            }
            if ( isset( $_POST['custom_field'] ) ) {
                $cf = array_filter($_POST['custom_field']);

                foreach ( $cf as $key => $values ) {
                    if ( is_array( $values ) ) {
                        if ( count( $values ) > 1 ) {
                            $sub_meta_queries = array();
                            foreach ( $values as $value ) {
                                $sub_meta_queries[] = array(
                                    'key' => '_' . $key,
                                    'value' => sanitize_text_field( $value ),
                                    'compare' => 'LIKE'
                                );
                            }
                            $meta_queries[] = array_merge( array('relation' => 'OR'), $sub_meta_queries );
                        }
                        else {

                            $meta_queries[] = array(
                                'key' => '_' . $key,
                                'value' => sanitize_text_field( $values[0] ),
                                'compare' => 'LIKE'
                            );
                        }
                    }
                    else {

                        $field_type = get_post_meta( $key, 'type', true );
                        $operator = ('text' == $field_type || 'textarea' == $field_type || 'url' == $field_type) ? 'LIKE' : '=';
                        $meta_queries[] = array(
                            'key' => '_' . $key,
                            'value' => sanitize_text_field( $values ),
                            'compare' => $operator
                        );

                    }
                }
            } // end post['cf']


            // search by rating
            /* if (isset($_POST['search_by_rating'])) {
                $q_rating = sanitize_text_field( $_POST['search_by_rating'] );
                $listings_ids = ATBDP_Listings_Data_Store::get_listings_ids();
                $rated = array();
                if ( ! empty( $listings_ids ) ) {
                    foreach ( $listings_ids as $listings_id ) {
                        // TODO: remove the following line
                        // $average = ATBDP()->review->get_average($listings_id);
                        $average = directorist_get_listing_rating($listings_id);
                        if ($q_rating === '5') {
                            if (($average == '5')) {
                                $rated[] = $listings_id;
                            }
                            else {
                                $rated[] = array();
                            }
                        }
                        elseif ($q_rating === '4') {
                            if ($average >= '4') {
                                $rated[] = $listings_id;
                            }
                            else {
                                $rated[] = array();
                            }
                        }
                        elseif ($q_rating === '3') {
                            if ($average >= '3') {
                                $rated[] = $listings_id;
                            }
                            else {
                                $rated[] = array();
                            }
                        }
                        elseif ($q_rating === '2') {
                            if ($average >= '2') {
                                $rated[] = $listings_id;
                            }
                            else {
                                $rated[] = array();
                            }
                        }
                        elseif ($q_rating === '1') {
                            if ($average >= '1') {
                                $rated[] = $listings_id;
                            }
                            else {
                                $rated[] = array();
                            }
                        }
                        elseif ('' === $q_rating) {
                            if ($average === '') {
                                $rated[] = $listings_id;
                            }
                        }
                    }
                    $rating_id = array(
                        'post__in' => !empty($rated) ? $rated : array()
                    );
                    $args = array_merge($args, $rating_id);
                }
            } */

            if (isset($_POST['search_by_rating'])) {
                $q_rating = sanitize_text_field( $_POST['search_by_rating'] );
                $listings_ids = ATBDP_Listings_Data_Store::get_listings_ids();
                $rated = array();
                if ( ! empty( $listings_ids ) ) {
                    foreach ( $listings_ids as $listings_id ) {
                        $average = ATBDP()->review->get_average( $listings_id );
                        if ($q_rating === '5') {
                            if (($average == '5')) {
                                $rated[] = $listings_id;
                            }
                            else {
                                $rated[] = array();
                            }
                        }
                        elseif ($q_rating === '4') {
                            if ($average >= '4') {
                                $rated[] = $listings_id;
                            }
                            else {
                                $rated[] = array();
                            }
                        }
                        elseif ($q_rating === '3') {
                            if ($average >= '3') {
                                $rated[] = $listings_id;
                            }
                            else {
                                $rated[] = array();
                            }
                        }
                        elseif ($q_rating === '2') {
                            if ($average >= '2') {
                                $rated[] = $listings_id;
                            }
                            else {
                                $rated[] = array();
                            }
                        }
                        elseif ($q_rating === '1') {
                            if ($average >= '1') {
                                $rated[] = $listings_id;
                            }
                            else {
                                $rated[] = array();
                            }
                        }
                        elseif ('' === $q_rating) {
                            if ($average === '') {
                                $rated[] = $listings_id;
                            }
                        }
                    }
                    $rating_id = array(
                        'post__in' => !empty($rated) ? $rated : array()
                    );
                    $args = array_merge($args, $rating_id);
                }
            }

            if ( ! empty( $_POST['website'] ) ) {
                $website = $_POST['website'];
                $meta_queries[] = array(
                    'key' => '_website',
                    'value' => $website,
                    'compare' => 'LIKE'
                );
            }

            if ( ! empty( $_POST['email'] ) ) {
                $email = $_POST['email'];
                $meta_queries[] = array(
                    'key' => '_email',
                    'value' => $email,
                    'compare' => 'LIKE'
                );
            }

            if ( ! empty( $_POST['phone'] ) ) {
                $phone = $_POST['phone'];
                $meta_queries[] = array(
                    'relation' => 'OR',
                    array(
                        'key' => '_phone2',
                        'value' => $phone,
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => '_phone',
                        'value' => $phone,
                        'compare' => 'LIKE'
                    )
                );
            }

            if (!empty($_POST['fax'])) {
                $fax = $_POST['fax'];
                $meta_queries[] = array(
                    'key' => '_fax',
                    'value' => $fax,
                    'compare' => 'LIKE'
                );
            }
            if (!empty($_POST['miles']) && $_POST['miles'] > 0 && !empty($_POST['cityLat']) && !empty($_POST['cityLng'])) {
                $args['atbdp_geo_query'] = array(
                    'lat_field' => '_manual_lat',  // this is the name of the meta field storing latitude
                    'lng_field' => '_manual_lng', // this is the name of the meta field storing longitude
                    'latitude' => $_POST['cityLat'],    // this is the latitude of the point we are getting distance from
                    'longitude' => $_POST['cityLng'],   // this is the longitude of the point we are getting distance from
                    'distance' => $_POST['miles'],           // this is the maximum distance to search
                    'units' => 'miles'       // this supports options: miles, mi, kilometers, km
                );
            } elseif ( ! empty( $_POST['address'] ) ) {
                $address = $_POST['address'];
                $meta_queries[] = array(
                    'key' => '_address',
                    'value' => $address,
                    'compare' => 'LIKE'
                );
            }

            if ( ! empty( $_POST['zip_code'] ) ) {
                $zip_code = $_POST['zip_code'];
                $meta_queries[] = array(
                    'key' => '_zip',
                    'value' => $zip_code,
                    'compare' => 'LIKE'
                );
            }
            $args['meta_query'] = $meta_queries;
            $current_order = isset($_POST['sort_by']) ? $_POST['sort_by'] : '';
            $listing_orderby = get_directorist_option('order_listing_by');
            $listing_order = get_directorist_option('sort_listing_by');
            if (empty($current_order)) {
                if ('rand' == $listing_orderby) {
                    $current_order = atbdp_get_listings_current_order($listing_orderby);
                } else {
                    $current_order = atbdp_get_listings_current_order($listing_orderby . '-' . $listing_order);
                }
            }

            $rated = array();
            $listing_popular_by = get_directorist_option('listing_popular_by');
            $average_review_for_popular = get_directorist_option('average_review_for_popular', 4);
            $view_to_popular = get_directorist_option('views_for_popular');
            $has_featured = get_directorist_option('enable_featured_listing');
            if ($has_featured || is_fee_manager_active()) {
                $has_featured = "yes";
            }

            switch ($current_order) {
                case 'title-asc':
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'title' => 'ASC',
                        );
                    } else {
                        $args['orderby'] = 'title';
                        $args['order'] = 'ASC';
                    };
                    break;
                case 'title-desc':
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'title' => 'DESC',
                        );
                    } else {
                        $args['orderby'] = 'title';
                        $args['order'] = 'DESC';
                    };
                    break;
                case 'date-asc':
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'date' => 'ASC',
                        );
                    } else {
                        $args['orderby'] = 'date';
                        $args['order'] = 'ASC';
                    };
                    break;
                case 'date-desc':
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = array(
                            'meta_value_num' => 'DESC',
                            'date' => 'DESC',
                        );
                    } else {
                        $args['orderby'] = 'date';
                        $args['order'] = 'DESC';
                    };
                    break;
                case 'price-asc':
                    if ($has_featured) {
                        $meta_queries['price'] = array(
                            'key' => '_price',
                            'type' => 'NUMERIC',
                            'compare' => 'EXISTS',
                        );

                        $args['orderby'] = array(
                            '_featured' => 'DESC',
                            'price' => 'ASC',
                        );
                    } else {
                        $args['meta_key'] = '_price';
                        $args['orderby'] = 'meta_value_num';
                        $args['order'] = 'ASC';
                    };
                    break;
                case 'price-desc':
                    if ($has_featured) {
                        $meta_queries['price'] = array(
                            'key' => '_price',
                            'type' => 'NUMERIC',
                            'compare' => 'EXISTS',
                        );

                        $args['orderby'] = array(
                            '_featured' => 'DESC',
                            'price' => 'DESC',
                        );
                    } else {
                        $args['meta_key'] = '_price';
                        $args['orderby'] = 'meta_value_num';
                        $args['order'] = 'DESC';
                    };
                    break;
                case 'rand':
                    if ($has_featured) {
                        $args['meta_key'] = '_featured';
                        $args['orderby'] = 'meta_value_num rand';
                    } else {
                        $args['orderby'] = 'rand';
                    };
                    break;
            }
            $meta_queries = apply_filters('atbdp_all_listings_meta_queries', $meta_queries);
            $count_meta_queries = count($meta_queries);
            if ($count_meta_queries) {
                $args['meta_query'] = ($count_meta_queries > 1) ? array_merge(array('relation' => 'AND'), $meta_queries) : $meta_queries;
            }
            $arguments = apply_filters('atbdp_all_listings_query_arguments', $args);
            $arguments = apply_filters('atbdp_listing_search_query_argument', $arguments);

            $display_header = isset($_POST['display_header']) ? $_POST['display_header'] : '';

            $attr = [
                'header'                    => ! empty( $display_header ) ? $display_header : 'yes',
                'listings_with_map_columns' => isset($_POST['view_columns']) ? $_POST['view_columns'] : '',
                'show_pagination'           => $show_pagination,
                'map_height'                => ( isset( $_POST['map_height'] ) ) ? $_POST['map_height'] : 800,
            ];

            $this->data['listings'] = new Directorist\Directorist_Listings( $attr, 'search', $arguments );
            $this->data['listings']->paged = $paged;
        }

        // render_ajax_view
        public function render_ajax_view() {
            ob_start();

            $bdmv_old_listings = ! empty( $GLOBALS['bdmv_listings'] ) ? $GLOBALS['bdmv_listings'] : null;
            $GLOBALS['bdmv_old_listings'] = $bdmv_old_listings;
            $GLOBALS['bdmv_listings'] = $this;

            // If doesn't have any post
            if ( empty( $this->data['query_results']->ids ) ) { ?>
                <?php bdmv_get_template( 'view/map' ); ?>
                <?php
                $listings       = ob_get_clean();
                $no_listing     = 'no_listing';
            } else {

                if ( "1" == $this->data['view_columns'] ) {
                    bdmv_get_template( 'all-listings/columns-one/map-listing' );
                } elseif ("2-style-2" == $this->data['view_columns']) {
                    bdmv_get_template( 'all-listings/columns-two-(style-two)/map-listing' );
                } else {
                    bdmv_get_template( 'all-listings/columns-three/map-listing' );
                }

                $GLOBALS['bdmv_listings'] = $bdmv_old_listings;


                $listings =  ob_get_clean();
            }

             ob_start();
             $bdmv_old_listings = ! empty( $GLOBALS['bdmv_listings'] ) ? $GLOBALS['bdmv_listings'] : null;
             //$GLOBALS['bdmv_old_listings'] = $bdmv_old_listings;
             $view_columns = get_directorist_option( 'bdmv_listings_with_map_columns', 3 );

             if ( isset( $_POST['listings_with_map_columns'] ) ) {
                $view_columns = $_POST['listings_with_map_columns'];
             }

             $GLOBALS['bdmv_listings'] = $this;
             if ( "1" == $view_columns ) {
                bdmv_get_template( 'all-listings/columns-one/map-listing' );
            } elseif ("2" == $view_columns) {
                bdmv_get_template( 'all-listings/columns-two/search' );
            } else {
                bdmv_get_template( 'all-listings/columns-three/search' );
            }
             $search = ob_get_clean();

             wp_send_json( array(
                'listings' => $listings,
                'search' => $search,
                'no_listing' => ! empty( $no_listing ) ? $no_listing : null
            ) );

        }

        // parse_listings_attributes
        public function parse_listings_attributes()
        {
            $listings = $this->data['listings'];

            $attributes = [
                'listings_with_map_columns'       => '2',
                'listings_with_map_filter_fields' => '',
                'action_before_after_loop'        => 'yes',

                'listings_per_page' => 6,
                'show_pagination'   => 'yes',
                'filter_buttons'    => '',
                'map_zoom_level'    => get_directorist_option( 'listing_map_zoom_level', 2 ),

                'map_height' => '800',
                'location'   => '',
                'category'   => '',
                'tag'        => '',
                'header'     => 'yes',
            ];

            foreach ($attributes as $attribute => $default_value) {
                $this->data[$attribute] = isset($listings->params[$attribute]) ? $listings->params[$attribute] : $default_value;
            }
        }


        // set_default_options
        public function set_default_options()
        {
            add_filter('atbdp_map_options', function ($options) {
                $options['zoom'] = get_directorist_option('map_view_zoom_level', 1);

                return $options;
            }, 20, 1);


            $this->options['visible_fields'] = get_directorist_option('listing_map_visible_fields', array('search_text', 'search_category', 'search_location'));
            $this->options['search_filters'] = get_directorist_option('bdmv_search_filters', array('search_reset_filters', 'search_apply_filters'));

            $this->options['listing_default_radius_distance'] = get_directorist_option('listing_default_radius_distance', 'google');
            $this->options['listing_map_location_address']    = get_directorist_option('listing_map_location_address', 'map_api');

            $this->options['select_listing_map']  = get_directorist_option('select_listing_map', 'google');
            $this->options['map_view_zoom_level'] = get_directorist_option('map_view_zoom_level', 1);
            $this->options['map_zoom_level']      = get_directorist_option('listing_map_zoom_level', 2);
            $this->options['listing_map_view']    = get_directorist_option('listing_map_view', 'grid');

            $this->options['disable_list_price'] = get_directorist_option('disable_list_price');
            $this->options['display_sort_by']    = get_directorist_option('display_sort_by', 1);
            $this->options['display_view_as']    = get_directorist_option('display_view_as', 1);
            $this->options['grid_view_as']       = get_directorist_option('grid_view_as', 'normal_grid');

            $this->options['address_label']        = get_directorist_option('address_label', __('Address', 'directorist-listings-map'));
            $this->options['fax_label']            = get_directorist_option('fax_label', __('Fax', 'directorist-listings-map'));
            $this->options['email_label']          = get_directorist_option('email_label', __('Email', 'directorist-listings-map'));
            $this->options['website_label']        = get_directorist_option('website_label', __('Website', 'directorist-listings-map'));
            $this->options['tag_label']            = get_directorist_option('tag_label', __('Tag', 'directorist-listings-map'));
            $this->options['zip_label']            = get_directorist_option('zip_label', __('Zip', 'directorist-listings-map'));
            $this->options['listing_filters_icon'] = get_directorist_option('listing_filters_icon', 1);
            $this->options['listings_map_viewas']  = get_directorist_option('listings_map_viewas', 1);
            $this->options['listings_map_sortby']  = get_directorist_option('listings_map_sortby', 1);
            $this->options['sort_by_text']         = get_directorist_option('sort_by_text', __('Sort By', 'directorist'));
            $this->options['view_as_text']         = get_directorist_option('view_as_text', __('View As', 'directorist'));

            $this->options['is_disable_price']        = get_directorist_option('disable_list_price');
            $this->options['display_sortby_dropdown'] = get_directorist_option('display_sort_by', 1);
            $this->options['display_viewas_dropdown'] = get_directorist_option('grid_view_as', 'normal_grid');
            $this->options['view_as']                 = get_directorist_option('display_view_as', 1);

            $this->options['view_as_items'] = get_directorist_option('listings_view_as_items', array('listings_grid', 'listings_list', 'listings_map'));
            $this->options['sort_by_items'] = get_directorist_option('listings_sort_by_items', array('a_z', 'z_a', 'latest', 'oldest', 'popular', 'price_low_high', 'price_high_low', 'random'));
        }

        // set_ajax_options
        public function set_ajax_options() {
            $this->options['sort_by_text'] = get_directorist_option('sort_by_text', __('Sort By', 'directorist-listings-map'));
            $this->options['view_as_text'] = get_directorist_option('view_as_text', __('View As', 'directorist-listings-map'));
        }

        // set_default_data
        public function set_default_data()
        {
            $taxonomy_query_args = [
                'parent'             => 0,
                'term_id'            => 0,
                'hide_empty'         => 0,
                'orderby'            => 'name',
                'order'              => 'asc',
                'show_count'         => 0,
                'single_only'        => 0,
                'pad_counts'         => true,
                'immediate_category' => 0,
                'active_term_id'     => 0,
                'ancestors'          => [],
                'listing_type'       => ''
            ];

            $this->data['fields']['categories'] = search_category_location_filter($taxonomy_query_args, ATBDP_CATEGORY);
            $this->data['fields']['locations']  = search_category_location_filter($taxonomy_query_args, ATBDP_LOCATION);

            $this->data['field_data']['tag_label']      = !empty($this->options['tag_label']) ? $this->options['tag_label'] : __('Tag', 'directorist-listings-map');
            $this->data['field_data']['address_label']  = !empty($this->options['address_label']) ? $this->options['address_label'] : __('Address', 'directorist-listings-map');
            $this->data['field_data']['fax_label']      = !empty($this->options['fax_label']) ? $this->options['fax_label'] : __('Fax', 'directorist-listings-map');
            $this->data['field_data']['email_label']    = !empty($this->options['email_label']) ? $this->options['email_label'] : __('Email', 'directorist-listings-map');
            $this->data['field_data']['website_label']  = !empty($this->options['website_label']) ? $this->options['website_label'] : __('Website', 'directorist-listings-map');
            $this->data['field_data']['zip_label']      = !empty($this->options['zip_label']) ? $this->options['zip_label'] : __('Zip', 'directorist-listings-map');

            $this->data['column_width']  = (100 / 2) . '%';
            $this->data['map_container'] = apply_filters('atbdp_map_container', 'directorist-container-fluid');

            $this->data['query_args'] = array(
                'parent'             => 0,
                'term_id'            => 0,
                'hide_empty'         => 0,
                'orderby'            => 'name',
                'order'              => 'asc',
                'show_count'         => 0,
                'single_only'        => 0,
                'pad_counts'         => true,
                'immediate_category' => 0,
                'active_term_id'     => 0,
                'ancestors'          => array(),
                'listing_type'      => ''
            );

            $this->data['categories_fields'] = search_category_location_filter($this->data['query_args'], ATBDP_CATEGORY);
            $this->data['locations_fields']  = search_category_location_filter($this->data['query_args'], ATBDP_LOCATION);
            $this->data['defSquery']         = isset( $_POST['skeyword'] ) ?  $_POST['skeyword'] : '';
        }

        // set_listing_data
        public function set_listing_data() {
            // Remap Data
            $this->data['view_columns']   = $this->data['listings_with_map_columns'];
            $this->data['display_header'] = $this->data['header'];
            $this->data['location_slug']  = $this->data['location'];
            $this->data['category_slug']  = $this->data['category'];
            $this->data['tag_slug']       = $this->data['tag'];

            if ( ! is_array( $this->data['listings_with_map_filter_fields'] ) ) {
                $this->data['listings_with_map_filter_fields'] = explode(',', $this->data['listings_with_map_filter_fields']);
            }

            if ( ! is_array( $this->data['filter_buttons']) ) {
                $this->data['filter_buttons'] = explode(',', $this->data['filter_buttons']);
            }

            $listings = $this->data['listings'];

            if ( ! empty( $_GET ) && ( ! isset( $_GET['directory_type'] ) ) ) {
                $this->data['listings']->update_search_options();
                $listings->query_args    = $listings->parse_search_query_args();
                $listings->query_results = $listings->get_query_results();
            }

            if( ! empty( $_POST['directory_type'] ) ) {
                $this->data['listings']->current_listing_type = $_POST['directory_type'];
            }

            $this->data['query_results'] = $listings->query_results;
            $this->data['paged'] = $listings->paged;

            $this->data['header_title'] = $listings->item_found_title();

            $text_field          = !empty($_POST['text_field']) ? $_POST['text_field'] : '';
            $category_field      = !empty($_POST['category_field']) ? $_POST['category_field'] : '';
            $location_field      = !empty($_POST['location_field']) ? $_POST['location_field'] : '';
            $address_field       = !empty($_POST['address_field']) ? $_POST['address_field'] : '';
            $price_field         = !empty($_POST['price_field']) ? $_POST['price_field'] : '';
            $price_range_field   = !empty($_POST['price_range_field']) ? $_POST['price_range_field'] : '';
            $rating_field        = !empty($_POST['rating_field']) ? $_POST['rating_field'] : '';
            $radius_field        = !empty($_POST['radius_field']) ? $_POST['radius_field'] : '';
            $open_field          = !empty($_POST['open_field']) ? $_POST['open_field'] : '';
            $tag_field           = !empty($_POST['tag_field']) ? $_POST['tag_field'] : '';
            $custom_search_field = !empty($_POST['custom_search_field']) ? $_POST['custom_search_field'] : '';
            $website_field       = !empty($_POST['website_field']) ? $_POST['website_field'] : '';
            $email_field         = !empty($_POST['email_field']) ? $_POST['email_field'] : '';
            $phone_field         = !empty($_POST['phone_field']) ? $_POST['phone_field'] : '';
            $fax_field           = !empty($_POST['fax_field']) ? $_POST['fax_field'] : '';
            $zip_field           = !empty($_POST['zip_field']) ? $_POST['zip_field'] : '';
            $reset_filters       = !empty($_POST['reset_filters']) ? $_POST['reset_filters'] : '';
            $apply_filter        = !empty($_POST['apply_filter']) ? $_POST['apply_filter'] : '';

            $listings_with_map_filter_fields = $this->data['listings_with_map_filter_fields'];
            $this->data['search_fields'] = array(
                "search_text"          => in_array('search_text', $listings_with_map_filter_fields) ? "yes" : $text_field,
                "search_category"      => in_array('search_category', $listings_with_map_filter_fields) ? "yes" : $category_field,
                "search_location"      => in_array('search_location', $listings_with_map_filter_fields) ? "yes" : $location_field,
                "search_price"         => in_array('search_price', $listings_with_map_filter_fields) ? "yes" : $price_field,
                "search_price_range"   => in_array('search_price_range', $listings_with_map_filter_fields) ? "yes" : $price_range_field,
                "search_rating"        => in_array('search_rating', $listings_with_map_filter_fields) ? "yes" : $rating_field,
                "radius_search"        => in_array('radius_search', $listings_with_map_filter_fields) ? "yes" : $radius_field,
                "search_open_now"      => in_array('search_open_now', $listings_with_map_filter_fields) ? "yes" : $open_field,
                "search_tag"           => in_array('search_tag', $listings_with_map_filter_fields) ? "yes" : $tag_field,
                "search_custom_fields" => in_array('search_custom_fields', $listings_with_map_filter_fields) ? "yes" : $custom_search_field,
                "search_website"       => in_array('search_website', $listings_with_map_filter_fields) ? "yes" : $website_field,
                "search_email"         => in_array('search_email', $listings_with_map_filter_fields) ? "yes" : $email_field,
                "search_phone"         => in_array('search_phone', $listings_with_map_filter_fields) ? "yes" : $phone_field,
                "search_fax"           => in_array('search_fax', $listings_with_map_filter_fields) ? "yes" : $fax_field,
                "search_zip_code"      => in_array('search_zip_code', $listings_with_map_filter_fields) ? "yes" : $zip_field,
            );

            $this->data['filters_button'] = array(
                "search_reset_filters" => in_array('search_reset_filters', $this->options['search_filters']) ? "yes" : $reset_filters,
                "search_apply_filters" => in_array('search_apply_filters', $this->options['search_filters']) ? "yes" : $apply_filter,
            );
        }


        #==========================
        # Helpers
        #==========================
        // get_the_attr
        public function get_the_attr($attribute = '')
        {
            if (isset($this->attributes[$attribute])) {
                echo $this->attributes[$attribute];
                return;
            }

            echo '';
        }

        // get_attr
        public function get_attr($attribute = '')
        {
            if (isset($this->attributes[$attribute])) {
                return $this->attributes[$attribute];
            }

            return null;
        }

        // get_the_option
        public function get_the_option($option = '')
        {
            if (isset($this->options[$option])) {
                echo $this->options[$option];
                return;
            }

            echo '';
        }

        // get_option
        public function get_option($option = '')
        {
            if (isset($this->options[$option])) {
                return $this->options[$option];
            }

            return null;
        }

        // get_the_data
        public function get_the_data($data = '')
        {
            if ( isset( $this->data[$data] ) ) {
                echo $this->data[$data];
            }

            echo '';
        }

        // get_data
        public function get_data($data = '')
        {
            if (isset($this->data[$data])) {
                return $this->data[$data];
            }

            return null;
        }

        // get_boolean
        public function get_boolean( string $data_key = '', string $data_source = 'data' ) {
            if ( isset( $this->data[ $data_key ] ) ) {
                $the_data = $this->data[ $data_key ];
                $positive_values = [ true, 1, '1', 'yes', ];

                foreach ( $positive_values as $positive_value ) {
                    if ( $positive_value ===  $the_data) {
                        return true;
                    }
                }

                return false;
            }

            return null;
        }

        // get_the_nonce
        public function get_the_nonce()
        {
            echo wp_create_nonce('bdlm_ajax_nonce');
        }

        // is_colimn
        public function column_is($column = 3)
        {
            if (is_int($column) && $column === (int) $this->data['view_columns'] ) {
                return true;
            }

            if (is_string($column) && $column === $this->data['view_columns'] ) {
                return true;
            }

            return false;
        }

        // has_any_fields_in_array
        public function has_any_fields_in_array(array $fields = [], array $the_array = [])
        {
            if (empty($fields)) {
                return false;
            }

            foreach ($fields as $field) {
                if (in_array($field, $the_array)) {
                    return true;
                }
            }

            return false;
        }

        // get_view_as_status
        public function get_view_as_status( string $veiw = '' ) {
            $view_as = isset($_POST['view_as']) ? $_POST['view_as'] : '';

            return ($veiw == $view_as) ? "active" : '';
        }

        // header_container_class
        public function header_container_class() {
            $listing_header_container_fluid = is_directoria_active() ? 'container' : 'directorist-container-fluid';
            echo apply_filters('atbdp_search_result_header_container_fluid', $listing_header_container_fluid);
        }

        // grid_container_class
        public function grid_container_class() {
            $listing_grid_container_fluid = is_directoria_active() ? 'container' : 'directorist-container-fluid';
            echo apply_filters('atbdp_search_result_grid_container_fluid', $listing_grid_container_fluid);
        }

        // has_any_in_search_filters
        public function has_any_in_search_filters(array $fields = [])
        {
            $search_filters = $this->options['search_filters'];
            return $this->has_any_fields_in_array($fields, $search_filters);
        }

        // has_any_in_search_filters
        public function has_any_in_visible_fields(array $fields = [])
        {
            $visible_fields = $this->options['visible_fields'];
            return $this->has_any_fields_in_array($fields, $visible_fields);
        }

        // header_is_enabled
        public function header_is_enabled() {
            var_dump( $this->get_boolean( 'display_header' ) );

            return $this->get_boolean( 'display_header' );
        }

        // has_header_title
        public function has_header_title() {
            if ( ! empty( $this->data['header_title'] ) ) {
                return true;
            }

            return false;
        }

        // has_any_hidden_fields
        public function has_any_hidden_fields()
        {
            $fields = [
                'search_text',
                'search_category',
                'search_location',
                'search_price',
                'search_price_range',
                'search_rating',
                'search_tag',
                'search_open_now',
                'search_custom_fields',
                'search_website',
                'search_email',
                'search_phone',
                'search_fax',
                'search_zip_code',
                'radius_search',
            ];

            $visible_fields = $this->options['visible_fields'];
            $filter_fields = $this->data['listings_with_map_filter_fields'];

            foreach ( $fields as $field ) {
                if ( ! in_array( $field, $visible_fields ) && in_array( $field, $filter_fields ) ) {
                    return true;
                }
            }

            return false;
        }

        // has_any_in_filter_fields
        public function has_any_in_filter_fields(array $fields = [])
        {
            $filter_fields = $this->data['listings_with_map_filter_fields'];
            return $this->has_any_fields_in_array($fields, $filter_fields);
        }

        // has_any_in_the_filter_and_visible_fields
        public function has_any_in_the_filter_and_visible_fields(array $fields = [])
        {
            return $this->has_any_in_the_sources($fields, ['filter_fields', 'visible_fields']);
        }

        // hidden_fields_has_any
        public function hidden_fields_has_any(array $fields = [])
        {
            $has_in_visible_area = $this->has_any_in_visible_fields( $fields );
            $has_in_hiddden_area = $this->has_any_in_filter_fields( $fields );

            if ( ! $has_in_visible_area && $has_in_hiddden_area ) {
                return true;
            }

            return false;
        }

        // has_any_in_the_sources
        public function has_any_in_the_sources(array $fields = [], array $sources = [])
        {
            if (empty($fields) || empty($sources)) {
                return false;
            }

            $sources_tree = [
                'filter_fields'  => 'has_any_in_filter_fields',
                'search_filters' => 'has_any_in_search_filters',
                'visible_fields' => 'has_any_in_visible_fields',
            ];

            $error = 0;

            foreach ($sources as $source) {
                if (!empty($sources_tree[$source])) {
                    $method = $sources_tree[$source];
                    $has_in_the_source = call_user_func_array(array($this, $method), array($fields));
                    if (!$has_in_the_source) {
                        $error++;
                    }
                }
            }

            return ($error) ? false : true;
        }

        // has_search_section
        public function has_search_section()
        {
            $search_fields = [
                'search_text',
                'search_category',
                'search_location',
                'search_price',
                'search_price_range',
                'search_rating',
                'search_tag',
                'search_open_now',
                'search_custom_fields',
                'search_website',
                'search_email',
                'search_phone',
                'search_fax',
                'search_zip_code',
                'radius_search',
            ];

            $filter_fields = $this->data['listings_with_map_filter_fields'];
            return $this->has_any_fields_in_array($search_fields, $filter_fields);
        }

        // get_custom_field_count
        public function get_custom_field_count()
        {
            if (isset($this->data['custom_field_count'])) {
                return $this->data['custom_field_count'];
            }

            $this->data['custom_field_count'] = bdmv_custom_field_post()->post_count;

            return $this->data['custom_field_count'];
        }

        // has_open_now_field
        public function has_open_now_field()
        {
            if ($this->has_any_in_filter_fields(['search_open_now']) && in_array('directorist-business-hours/bd-business-hour.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                return true;
            }

            return false;
        }

        // has_custom_fields
        public function has_custom_fields()
        {
            if ($this->has_any_in_filter_fields(['search_custom_fields']) && !empty($this->get_custom_field_count())) {
                return true;
            }

            return false;
        }

        // location_source_is
        public function location_source_is(string $location_source = '')
        {
            if ($location_source === $this->options['listing_map_location_address']) {
                return true;
            }

            return false;
        }

        // get_the_map_height
        public function get_the_map_height()
        {
            if (!empty($this->data['map_height'])) {
                echo $this->data['map_height'] . 'px';
            } else {
                echo '800px';
            }
        }

        // get_view
        public function get_view()
        {
            $view_as = (isset($_POST['view_as']) && !empty($_POST['view_as'])) ? $_POST['view_as'] : $this->options['listing_map_view'];

            return !empty($view_as) ? $view_as : 'grid';
        }

        // get_the_grid_row_class
        public function get_the_grid_row_class() {
            if ( 'masonry_grid' === $this->options['grid_view_as'] ) {
                echo 'data-uk-grid';
            }

            echo '';
        }

        // get_the_map_radius
        public function get_the_map_radius()
        {
            echo !empty($_GET['miles']) ? $_GET['miles'] : $this->options['listing_default_radius_distance'];
        }

        // load_map_scripts
        public function load_map_scripts()
        {
            wp_enqueue_script('bdm-current-js');
            wp_localize_script('bdm-current-js', 'adbdp_geolocation', array('select_listing_map' => $this->options['select_listing_map']));
        }

        // get_the_geo_location_icon
        public function get_the_geo_location_icon()
        {
            directorist_icon( 'las la-crosshairs', true, 'bdmv_get_loc' );
        }

        // map_type_is
        public function map_type_is(string $map_type = '')
        {
            if ($map_type === $this->options['select_listing_map']) {
                return true;
            }

            return false;
        }

        // map_type_is_not
        public function map_type_is_not(string $map_type = '')
        {
            if ($map_type !== $this->options['select_listing_map']) {
                return true;
            }

            return false;
        }

        // header_advance_search
        public function header_advance_search() {
            $hidden_var = array(
                "display_header"    => $this->data['display_header'],
                "header_title"      => $this->data['header_title'],
                "show_pagination"   => $this->data['show_pagination'],
                "listings_per_page" => $this->data['listings_per_page'],
                "location_slug"     => $this->data['location_slug'],
                "category_slug"     => $this->data['category_slug']
            );

            bdmv_header_advance_search( $this->data['search_fields'], $this->data['filters_button'], $hidden_var );
        }

        // get_the_field
        public function get_the_field(string $filed_name = '')
        {
            if (!empty($this->data['fields'][$filed_name])) {
                echo $this->data['fields'][$filed_name];

                return;
            }

            echo '';
        }

        // get_the_field_data
        public function get_the_field_data(string $data_name = '')
        {
            if (!empty($this->data['field_data'][$data_name])) {
                echo $this->data['field_data'][$data_name];

                return;
            }

            echo '';
        }

        // enqueue_assets
        public function enqueue_assets()
        {
            if (is_rtl()) {
                wp_enqueue_style('atbdp-search-style-rtl', ATBDP_PUBLIC_ASSETS . 'css/search-style-rtl.css');
            } else {
                wp_enqueue_style('atbdp-search-style', ATBDP_PUBLIC_ASSETS . 'css/search-style.css');
            }
        }
    }
endif;
