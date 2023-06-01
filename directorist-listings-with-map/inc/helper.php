<?php

if ( ! function_exists( 'bdmv_init_template_data' ) ) {
    function bdmv_init_template_data() {
        $bdmv_old_listings = ! empty( $GLOBALS['bdmv_listings'] ) ? $GLOBALS['bdmv_listings'] : null;
        $GLOBALS['bdmv_old_listings'] = $bdmv_old_listings;

        $bdmv_listings = new Listings_With_Map_Model();
        $bdmv_listings->setup_listing_data();

        $GLOBALS['bdmv_listings'] = $bdmv_listings;
    }
}

if ( ! function_exists( 'bdmv_reset_template_data' ) ) {
    function bdmv_reset_template_data() {
        $bdmv_old_listings = ! empty( $GLOBALS['bdmv_old_listings'] ) ? $GLOBALS['bdmv_old_listings'] : null;
        $GLOBALS['bdmv_listings'] = $bdmv_old_listings;
    }
}

if ( ! function_exists( 'bdmv_get_template' ) ) {
    function bdmv_get_template( string $file_path = '', $data = [] ) {
        atbdp_get_extension_template( BDM_TEMPLATES_DIR, $file_path, BDM_BASE, $data );
    }
}

if (!function_exists('bdmv_load_more_filter')) {

    function bdmv_load_more_filter($my_query, $pageno = null, $sKeyword = '')
    {
        $output = '';
        $pages = '';
        $pages = $my_query->total_pages;
        $totalpages = $pages;
        $ajax_pagin_classes = 'pagination directorist-filter-pagination-ajx';
        if (is_author()) {
            $ajax_pagin_classes = '';
        }
        if (!empty($pages) && $pages > 1) {
            $output .= '<nav class="directorist-pagination navigation ' . $ajax_pagin_classes . '">';
            $output .= '<div class="nav-links">';
            $n = 1;
            $flagAt = 4;
            $flagAt2 = 4;
            $flagOn = 0;
            while ($pages > 0) {

                if (isset($pageno) && !empty($pageno)) {

                    if (!empty($totalpages) && $totalpages < 7) {
                        if ($pageno == $n) {
                            $output .= '<a data-skeyword="' . $sKeyword . '" data-pageurl="' . $n . '"  class="page-numbers haspaglink current">' . $n . '</a>';
                        } else {
                            $output .= '<a data-skeyword="' . $sKeyword . '" data-pageurl="' . $n . '"   class="page-numbers haspaglink">' . $n . '</a>';
                        }
                    } elseif (!empty($totalpages) && $totalpages > 6) {
                        $flagOn = $pageno - 5;
                        $flagOn2 = $pageno + 4;
                        if ($pageno == $n) {
                            $output .= '<a data-skeyword="' . $sKeyword . '" data-pageurl="' . $n . '"  class="page-numbers haspaglink current">' . $n . '</a>';
                        } else {
                            if ($n <= 4) {
                                $output .= '<a data-skeyword="' . $sKeyword . '" data-pageurl="' . $n . '"  class="page-numbers haspaglink">' . $n . '</a>';
                            } elseif ($n > 4 && $flagAt2 == 7) {
                                $output .= '<a data-skeyword="' . $sKeyword . '" data-pageurl="' . $n . '" class="page-numbers haspaglink">' . $n . '</a>';
                                $output .= '<a data-skeyword="' . $sKeyword . '"  class="page-numbers">...</a>';
                                $flagAt2 = 1;

                            } elseif ($n > 4 && $n >= $flagOn && $n < $flagOn2) {
                                $output .= '<a data-skeyword="' . $sKeyword . '" data-pageurl="' . $n . '"  class="page-numbers haspaglink">' . $n . '</a>';

                            } elseif ($n == $totalpages) {
                                $output .= '<a data-skeyword="' . $sKeyword . '"  class="page-numbers">...</a>';
                                $output .= '<a data-skeyword="' . $sKeyword . '" data-pageurl="' . $n . '"  class="page-numbers haspaglink">' . $n . '</a>';

                            }

                        }

                    }


                } else {

                    if ($n == 1) {
                        $output .= '<a data-pageurl="' . $n . '"  class="page-numbers  haspaglink current">' . $n . '</a>';
                    } else if ($n < 7) {
                        $output .= '<a data-pageurl="' . $n . '"  class="page-numbers haspaglink">' . $n . '</a>';
                    } else if ($n > 7 && $pages > 7 && $flagAt == 7) {
                        $output .= '<a  class="page-numbers">...</a>';
                        $flagAt = 1;
                    } else if ($n > 7 && $pages < 7 && $flagAt == 1) {
                        $output .= '<a data-pageurl="' . $n . '"  class="page-numbers haspaglink">' . $n . '</a>';
                    }

                }

                $pages--;
                $n++;
                $output .= '';
            }
            $output .= '</div>';
            $output .= '</nav>';
        }


        return $output;
    }

}
if (!function_exists('bdmv_list_view')) {
    function bdmv_list_view($all_listings, $display_image, $show_pagination, $paged)
    {
        $class_name = 'directorist-container-fluid';
        $container = apply_filters('list_view_container', $class_name);
        ?>
        <div class="<?php echo !empty($container) ? $container : 'container'; ?>">
            <div class="directorist-row">
                <div class="<?php echo apply_filters('atbdp_listing_list_view_html_class', 'directorist-col-md-12') ?>">
                    <?php
                    // Prime caches to reduce future queries.
                    if (!empty($all_listings->ids) && is_callable('_prime_post_caches')) {
                        _prime_post_caches($all_listings->ids);
                    }
                    $original_post = $GLOBALS['post'];
                    foreach ($all_listings->ids as $id) {
                        $GLOBALS['post'] = get_post($id);
                        setup_postdata($GLOBALS['post']);

                        $cats = get_the_terms(get_the_ID(), ATBDP_CATEGORY);
                        $locs = get_the_terms(get_the_ID(), ATBDP_LOCATION);
                        $featured = get_post_meta(get_the_ID(), '_featured', true);
                        $price = get_post_meta(get_the_ID(), '_price', true);
                        $price_range = get_post_meta(get_the_ID(), '_price_range', true);
                        $listing_pricing = get_post_meta(get_the_ID(), '_atbd_listing_pricing', true);
                        $listing_img = get_post_meta(get_the_ID(), '_listing_img', true);
                        $listing_prv_img = get_post_meta(get_the_ID(), '_listing_prv_img', true);
                        $excerpt = get_post_meta(get_the_ID(), '_excerpt', true);
                        $tagline = get_post_meta(get_the_ID(), '_tagline', true);
                        $address = get_post_meta(get_the_ID(), '_address', true);
                        $phone_number = get_post_meta(get_the_Id(), '_phone', true);
                        $email = get_post_meta(get_the_Id(), '_email', true);
                        $web = get_post_meta(get_the_Id(), '_website', true);
                        $category = get_post_meta(get_the_Id(), '_admin_category_select', true);
                        $post_view = get_post_meta(get_the_Id(), '_atbdp_post_views_count', true);
                        $hide_contact_info = get_post_meta(get_the_ID(), '_hide_contact_info', true);
                        $disable_contact_info = get_directorist_option('disable_contact_info', 0);
                        $display_title = get_directorist_option('display_title', 1);
                        $display_review = get_directorist_option('enable_review', 1);
                        $display_price = get_directorist_option('display_price', 1);
                        $is_disable_price = get_directorist_option('disable_list_price');
                        $display_category = get_directorist_option('display_category', 1);
                        $display_view_count = get_directorist_option('display_view_count', 1);
                        $display_mark_as_fav = get_directorist_option('display_mark_as_fav', 1);
                        $display_author_image = get_directorist_option('display_author_image', 1);
                        $display_publish_date = get_directorist_option('display_publish_date', 1);
                        $display_email = get_directorist_option('display_email', 0);
                        $display_web_link = get_directorist_option('display_web_link', 0);
                        $display_contact_info = get_directorist_option('display_contact_info', 1);
                        $display_feature_badge_cart = get_directorist_option('display_feature_badge_cart', 1);
                        $display_popular_badge_cart = get_directorist_option('display_popular_badge_cart', 1);
                        $popular_badge_text = get_directorist_option('popular_badge_text', 'Popular');
                        $feature_badge_text = get_directorist_option('feature_badge_text', 'Featured');
                        $enable_tagline = get_directorist_option('enable_tagline');
                        $enable_excerpt = get_directorist_option('enable_excerpt');
                        $address_location = get_directorist_option('address_location', 'location');
                        /*Code for Business Hour Extensions*/
                        $bdbh = get_post_meta(get_the_ID(), '_bdbh', true);
                        $enable247hour = get_post_meta(get_the_ID(), '_enable247hour', true);
                        $disable_bz_hour_listing = get_post_meta(get_the_ID(), '_disable_bz_hour_listing', true);
                        $business_hours = !empty($bdbh) ? atbdp_sanitize_array($bdbh) : array(); // arrays of days and times if exist
                        /*Code for Business Hour Extensions*/
                        $author_id = get_the_author_meta('ID');
                        $u_pro_pic_meta = get_user_meta($author_id, 'pro_pic', true);
                        $u_pro_pic = wp_get_attachment_image_src($u_pro_pic_meta, 'thumbnail');
                        $avata_img = get_avatar($author_id, apply_filters('atbdp_avatar_size', 32));
                        $thumbnail_cropping = get_directorist_option('thumbnail_cropping', 1);
                        $crop_width = get_directorist_option('crop_width', 360);
                        $crop_height = get_directorist_option('crop_height', 300);
                        $display_tagline_field = get_directorist_option('display_tagline_field', 0);
                        $display_pricing_field = get_directorist_option('display_pricing_field', 1);
                        $display_excerpt_field = get_directorist_option('display_excerpt_field', 0);
                        $display_address_field = get_directorist_option('display_address_field', 1);
                        $display_phone_field = get_directorist_option('display_phone_field', 1);
                        if (!empty($listing_prv_img)) {

                            if ($thumbnail_cropping) {

                                $prv_image = atbdp_image_cropping($listing_prv_img, $crop_width, $crop_height, true, 100)['url'];

                            } else {
                                $prv_image = wp_get_attachment_image_src($listing_prv_img, 'large')[0];
                            }

                        }
                        if (!empty($listing_img[0])) {
                            if ($thumbnail_cropping) {
                                $gallery_img = atbdp_image_cropping($listing_img[0], $crop_width, $crop_height, true, 100)['url'];

                            } else {
                                $gallery_img = wp_get_attachment_image_src($listing_img[0], 'large')[0];
                            }

                        }
                        ?>


                        <div class="atbd_single_listing atbd_listing_list">
                            <article
                                    class="atbd_single_listing_wrapper <?php echo ($featured) ? 'directorist-featured-listings' : ''; ?>">
                                <figure class="atbd_listing_thumbnail_area"
                                        style=" <?php echo (empty(get_directorist_option('display_preview_image')) || 'no' == $display_image) ? 'display:none' : '' ?>">
                                    <?php
                                    $disable_single_listing = get_directorist_option('disable_single_listing');
                                    if (empty($disable_single_listing)){
                                    ?>
                                    <a href="<?php echo esc_url(get_post_permalink(get_the_ID())); ?>">
                                        <?php
                                        }
                                        the_thumbnail_card();
                                        if (empty($disable_single_listing)) {
                                            echo '</a>';
                                        }
                                        //Start lower badge
                                        $l_badge_html = '<span class="atbd_lower_badge">';

                                        if ($featured && !empty($display_feature_badge_cart)) {
                                            $l_badge_html .= '<span class="atbd_badge atbd_badge_featured">' . $feature_badge_text . '</span>';
                                        }
                                        $popular_listing_id = atbdp_popular_listings(get_the_ID());
                                        $badge = '<span class="atbd_badge atbd_badge_popular">' . $popular_badge_text . '</span>';
                                        if ($popular_listing_id === get_the_ID() && !empty($display_popular_badge_cart)) {
                                            $l_badge_html .= $badge;
                                        }
                                        //print the new badge
                                        $l_badge_html .= new_badge();
                                        $l_badge_html .= '</span>';

                                        /**
                                         * @since 5.0
                                         */
                                        echo apply_filters('atbdp_list_lower_badges', $l_badge_html);
                                        ?>
                                </figure>
                                <div class="atbd_listing_info">
                                    <div class="atbd_content_upper">
                                        <?php do_action('atbdp_list_view_before_title');
                                        if (!empty($display_title)) { ?>
                                            <h4 class="atbd_listing_title">
                                                <?php
                                                if (empty($disable_single_listing)) {
                                                    ?>
                                                    <a href="<?php echo esc_url(get_post_permalink(get_the_ID())); ?>"><?php echo esc_html(stripslashes(get_the_title())); ?></a>
                                                    <?php
                                                } else {
                                                    echo esc_html(stripslashes(get_the_title()));
                                                } ?>
                                            </h4>
                                            <?php
                                        }
                                        do_action('atbdp_list_view_after_title');
                                        if (!empty($tagline) && !empty($enable_tagline) && !empty($display_tagline_field)) { ?>
                                            <p class="atbd_listing_tagline"><?php echo esc_html(stripslashes($tagline)); ?></p>
                                            <?php
                                        }
                                        /**
                                         * Fires after the title and sub title of the listing is rendered
                                         *
                                         *
                                         * @since 1.0.0
                                         */
                                        do_action('atbdp_after_listing_tagline');
                                        ?>
                                        <?php
                                        $meta_html = '';
                                        if (!empty($display_review) || (!empty($display_price) && (!empty($price) || !empty($price_range)))) {
                                            $meta_html .= '<div class="atbd_listing_meta">';
                                            if (!empty($display_review)) {
                                                $average = ATBDP()->review->get_average(get_the_ID());
                                                $meta_html .= '<span class="atbd_meta atbd_listing_rating">' . $average . directorist_icon( 'las la-star', false ) . '</span>';
                                            }
                                            $listing_pricing = !empty($listing_pricing) ? $listing_pricing : '';
                                            if (!empty($display_price) && !empty($display_pricing_field)) {
                                                if (!empty($price_range) && ('range' === $listing_pricing)) {
                                                    $output = atbdp_display_price_range($price_range);
                                                    $meta_html .= $output;
                                                } else {
                                                    $meta_html .= atbdp_display_price($price, $is_disable_price, $currency = null, $symbol = null, $c_position = null, $echo = false);
                                                }
                                            }
                                            /**
                                             * Fires after the price of the listing is rendered
                                             *
                                             *
                                             * @since 3.1.0
                                             */
                                            do_action('atbdp_after_listing_price');
                                            $plan_hours = true;
                                            if (is_fee_manager_active()) {
                                                $plan_hours = is_plan_allowed_business_hours(get_post_meta(get_the_ID(), '_fm_plans', true));
                                            }
                                            if (is_business_hour_active() && $plan_hours && empty($disable_bz_hour_listing)) {
                                                //lets check is it 24/7
                                                if (!empty($enable247hour)) {
                                                    $open = get_directorist_option('open_badge_text');
                                                    $meta_html .= '<span class="atbd_badge atbd_badge_open">' . $open . '</span>';
                                                } else {
                                                    $bh_statement = BD_Business_Hour()->show_business_open_close($business_hours, false); // show the business hour in an unordered list
                                                    $meta_html .= $bh_statement;
                                                }
                                            }
                                            $meta_html .= '</div>'; // End atbd listing meta
                                        }
                                        echo apply_filters('atbdp_listings_list_review_price', $meta_html);
                                        if (!empty($display_contact_info) || !empty($display_publish_date) || !empty($display_email) || !empty($display_web_link)) { ?>
                                            <div class="atbd_listing_data_list">
                                                <ul>
                                                    <?php
                                                    /**
                                                     * @since 4.7.6
                                                     */
                                                    do_action('atbdp_listings_before_location');
                                                    if (!empty($display_contact_info)) {
                                                        if (!empty($address) && 'contact' == $address_location && !empty($display_address_field)) { ?>
                                                            <li><p>
                                                                <?php directorist_icon( 'las la-map-marker'); ?><?php echo esc_html(stripslashes($address)); ?>
                                                                </p></li>
                                                        <?php } elseif (!empty($locs) && 'location' == $address_location) {
                                                            $local_names = array();
                                                            $locals = array();
                                                            foreach ($locs as $term) {
                                                                $local_names[$term->term_id] = $term->parent == 0 ? $term->slug : $term->slug;
                                                                ksort($local_names);
                                                                $locals = array_reverse($local_names);
                                                            }
                                                            $output = array();
                                                            foreach ($locals as $location) {
                                                                $term = get_term_by('slug', $location, ATBDP_LOCATION);
                                                                $link = ATBDP_Permalink::atbdp_get_location_page($term);
                                                                $space = str_repeat(' ', 1);
                                                                $output[] = "{$space}<a href='{$link}'>{$term->name}</a>";
                                                            }
                                                            ?>
                                                            <li>
                                                                <p>
                                                <span>
                                                    <?php echo directorist_icon( 'las la-map-marker', false ) . join(',', $output); ?>
                                                </span>
                                                                </p>
                                                            </li>
                                                        <?php }
                                                        /**
                                                         * @since 4.7.6
                                                         */
                                                        do_action('atbdp_listings_before_phone');
                                                        ?>
                                                        <?php if (!empty($phone_number) && !empty($display_phone_field)) { ?>
                                                            <li><p>
                                                                    <?php directorist_icon( 'las la-phone' ); ?><a
                                                                            href="tel:<?php echo esc_html(stripslashes($phone_number)); ?>"><?php echo esc_html(stripslashes($phone_number)); ?></a>

                                                                </p></li>
                                                            <?php
                                                        }
                                                    }
                                                    /**
                                                     * @since 4.7.6
                                                     */
                                                    do_action('atbdp_listings_before_post_date');
                                                    if (!empty($display_publish_date)) { ?>
                                                        <li><p>
                                                                <?php directorist_icon( 'las la-clock' ); ?><?php
                                                                $publish_date_format = get_directorist_option('publish_date_format', 'time_ago');
                                                                if ('time_ago' === $publish_date_format) {
                                                                    printf(__('Posted %s ago', 'directorist'), human_time_diff(get_the_time('U'), current_time('timestamp')));
                                                                } else {
                                                                    echo get_the_date();
                                                                }
                                                                ?></p></li>
                                                    <?php }
                                                    /**
                                                     * @since 4.7.6
                                                     */
                                                    do_action('atbdp_listings_after_post_date');
                                                    if (!empty($email && $display_email)) {
                                                        echo '<li><p>'.directorist_icon( 'las la-envelope', false ).'<a target="_top" href="mailto:' . $email . '">' . $email . '</a></p></li>';
                                                    }
                                                    if (!empty($web && $display_web_link)):
                                                        ?>
                                                        <li><p>
                                                                <?php directorist_icon( 'las la-globe' ); ?>
                                                                <a target="_blank" href="<?php echo esc_url($web); ?>"
                                                                    <?php echo !empty($use_nofollow) ? 'rel="nofollow"' : ''; ?>><?php echo esc_html($web); ?></a>
                                                            </p></li>
                                                    <?php
                                                    endif;
                                                    ?>
                                                </ul>
                                            </div><!-- End atbd listing meta -->
                                            <?php
                                        }
                                        //show category and location info
                                        ?>
                                        <?php if (!empty($excerpt) && !empty($enable_excerpt) && !empty($display_excerpt_field)) {
                                            $excerpt_limit = get_directorist_option('excerpt_limit', 20);
                                            $excerpt_limit = get_directorist_option('excerpt_limit', 20);
                                            $display_readmore = get_directorist_option('display_readmore', 0);
                                            $readmore_text = get_directorist_option('readmore_text', __('Read More', 'directorist'));
                                            ?>
                                            <p class="atbd_excerpt_content"><?php echo esc_html(stripslashes(wp_trim_words($excerpt, $excerpt_limit)));
                                            /**
                                             * @since 5.0.9
                                             */
                                            do_action('atbdp_listings_after_exerpt');
                                            if (!empty($display_readmore)) {
                                                ?><a
                                                href="<?php the_permalink(); ?>"><?php printf(__(' %s', 'directorist'), $readmore_text); ?></a></p>
                                            <?php }
                                        }
                                        /*if (!empty($display_mark_as_fav)) {
                                            echo atbdp_listings_mark_as_favourite(get_the_ID());
                                        }*/
                                        ?>
                                    </div><!-- end ./atbd_content_upper -->
                                    <?php
                                    $catViewCountAuthor = '';
                                    if (!empty($display_category) || !empty($display_view_count) || !empty($display_author_image)) {
                                        $catViewCountAuthor .= '<div class="atbd_listing_bottom_content">';
                                        if (!empty($display_category)) {
                                            if (!empty($cats)) {
                                                $totalTerm = count($cats);
                                                $catViewCountAuthor .= '<div class="atbd_content_left">';
                                                $catViewCountAuthor .= '<div class="atbd_listting_category">';
                                                $catViewCountAuthor .= '<a href="' . ATBDP_Permalink::atbdp_get_category_page($cats[0]) . '">';
                                                if ('none' != get_cat_icon($cats[0]->term_id)) {
                                                    $catViewCountAuthor .= directorist_icon( 'las la-tags', false );
                                                }
                                                $catViewCountAuthor .= $cats[0]->name;
                                                $catViewCountAuthor .= '</a>';
                                                if ($totalTerm > 1) {
                                                    $totalTerm = $totalTerm - 1;
                                                    $catViewCountAuthor .= '<div class="atbd_cat_popup">';
                                                    $catViewCountAuthor .= '<span>+' . $totalTerm . '</span>';
                                                    $catViewCountAuthor .= '<div class="atbd_cat_popup_wrapper">';
                                                    $output = array();
                                                    foreach (array_slice($cats, 1) as $cat) {
                                                        $link = ATBDP_Permalink::atbdp_get_category_page($cat);
                                                        $space = str_repeat(' ', 1);
                                                        $output [] = "{$space}<span><a href='{$link}'>{$cat->name}<span>,</span></a></span>";
                                                    }
                                                    $catViewCountAuthor .= '<span>' . join($output) . '</span>';
                                                    $catViewCountAuthor .= '</div>';
                                                    $catViewCountAuthor .= '</div>';
                                                }
                                                $catViewCountAuthor .= '</div>';
                                                $catViewCountAuthor .= '</div>';
                                            } else {
                                                $catViewCountAuthor .= '<div class="atbd_content_left">';
                                                $catViewCountAuthor .= '<div class="atbd_listting_category">';
                                                $catViewCountAuthor .= '<a href="">';
                                                $catViewCountAuthor .= directorist_icon( 'las la-tags', false );
                                                $catViewCountAuthor .= __('Uncategorized', 'directorist');
                                                $catViewCountAuthor .= '</a>';
                                                $catViewCountAuthor .= '</div>';
                                                $catViewCountAuthor .= '</div>';
                                            }
                                        }
                                        if (!empty($display_view_count) || !empty($display_author_image)) {
                                            $catViewCountAuthor .= '<ul class="atbd_content_right">';
                                            if (!empty($display_view_count)) {
                                                $catViewCountAuthor .= '<li class="atbd_count">';
                                                $catViewCountAuthor .= directorist_icon( 'las la-eye', false );
                                                $catViewCountAuthor .= !empty($post_view) ? $post_view : 0;
                                                $catViewCountAuthor .= '</li>';
                                            }
                                            if (!empty($display_author_image)) {
                                                $author = get_userdata($author_id);
                                                $author_first_last_name = $author->first_name . ' ' . $author->last_name;
                                                $catViewCountAuthor .= '<li class="atbd_author">';
                                                $catViewCountAuthor .= '<a href="' . ATBDP_Permalink::get_user_profile_page_link($author_id) . '" class="atbd_tooltip" aria-label="' . $author_first_last_name . '">';

                                                if (empty($u_pro_pic_meta)) {
                                                    $catViewCountAuthor .= $avata_img;
                                                }
                                                if (!empty($u_pro_pic_meta)) {
                                                    $catViewCountAuthor .= '<img src="' . esc_url($u_pro_pic[0]) . '" alt="Author Image">';
                                                }
                                                $catViewCountAuthor .= '</a>';
                                                $catViewCountAuthor .= '</li>';
                                            }
                                            $catViewCountAuthor .= ' </ul>';
                                        }
                                        $catViewCountAuthor .= ' </div>' //end ./atbd_listing_bottom_content
                                        ?>
                                    <?php }
                                    echo apply_filters('atbdp_listings_list_cat_view_count_author', $catViewCountAuthor);
                                    ?>
                                </div>
                            </article>
                        </div>



                    <?php }
                     $GLOBALS['post'] = $original_post;
                     wp_reset_postdata();
                    ?>
                    <?php
                    /**
                     * @since 5.0
                     */
                    do_action('atbdp_before_listings_pagination');

                    $show_pagination = isset($_POST['show_pagination']) ? esc_html($_POST['show_pagination']) : 'yes';
                    if ('yes' == $show_pagination) {
                        $defSquery = '';
                        if (isset($_POST['skeyword'])) {
                            $defSquery = $_POST['skeyword'];
                        }
                        $paged = !empty($paged) ? $paged : '';
                        if (isset($_POST['pageno'])) {
                            $paged = $_POST['pageno'];
                        }
                        echo bdmv_load_more_filter($all_listings, $paged, $defSquery);
                    } ?>

                </div>
            </div>
        </div>
        <?php
        return true;
    }
}
if (!function_exists('bdmv_header_advance_search')) {

    function bdmv_header_advance_search($search_more_filters_fields, $filters_button, $hidden_var)
    {
        $search_more_filters_fields = extract($search_more_filters_fields);
        $filters_button = extract($filters_button);
        $hidden_var = extract($hidden_var);
        $listing_map_location_address = get_directorist_option('listing_map_location_address', 'map_api');
        $currency = get_directorist_option('g_currency', 'USD');
        $c_symbol = atbdp_currency_symbol($currency);
        $query_args = array(
            'parent' => 0,
            'term_id' => 0,
            'hide_empty' => 0,
            'orderby' => 'name',
            'order' => 'asc',
            'show_count' => 0,
            'single_only' => 0,
            'pad_counts' => true,
            'immediate_category' => 0,
            'active_term_id' => 0,
            'ancestors' => array()
        );
        $categories_fields = search_category_location_filter($query_args, ATBDP_CATEGORY);
        $locations_fields = search_category_location_filter($query_args, ATBDP_LOCATION);
        ?>
        <div class="ads_slide" data-nonce="<?php echo wp_create_nonce('bdlm_ajax_nonce') ?>" id="directorist-search-area">
            <div class="ads-advanced">
                <div class="atbd_seach_fields_wrapper"<?php echo empty($search_border) ? 'style="border: none;"' : ''; ?>>
                    <div class="directorist-row atbdp-search-form">
                        <?php if ('yes' == $search_text) { ?>
                            <div class="col-md-6 col-sm-12 col-lg-4 search-text" data-text="yes">
                                <div class="single_search_field search_query">
                                    <input class="form-control search_fields" type="text" name="q"
                                           placeholder="<?php _e('What are you looking for?', 'directorist-listings-map'); ?>">
                                </div>
                            </div>
                        <?php }
                        if ('yes' == $search_category) {
                            $slug = !empty($term_slug) ? $term_slug : '';
                            $taxonomy_by_slug = get_term_by('slug', $slug, ATBDP_CATEGORY);
                            if (!empty($taxonomy_by_slug)) {
                                $taxonomy_id = $taxonomy_by_slug->term_taxonomy_id;
                            }
                            $selected = isset($_GET['in_cat']) ? $_GET['in_cat'] : -1;
                            ?>
                            <div class="col-md-6 col-sm-12 col-lg-4 search-category" data-cat="yes">
                                <div class="single_search_field search_category">
                                    <select name="in_cat" id="cat-type"
                                            class="form-control directory_field bdas-category-search">
                                        <option><?php _e('Select a category'); ?></option>
                                        <?php echo $categories_fields; ?>
                                    </select>
                                </div>
                            </div>
                        <?php }
                        if ('yes' == $search_location) {
                            if ('listing_location' == $listing_map_location_address) {
                                $slug = !empty($term_slug) ? $term_slug : '';
                                $location_by_slug = get_term_by('slug', $slug, ATBDP_LOCATION);
                                if (!empty($location_by_slug)) {
                                    $location_id = $location_by_slug->term_taxonomy_id;
                                }
                                $loc_selected = isset($_GET['in_loc']) ? $_GET['in_loc'] : -1;
                                ?>
                                <div class="directorist-col-md-12 col-sm-12 col-lg-4 search-location" data-loc="yes">
                                    <div class="single_search_field search_location">
                                        <select name="in_loc" id="loc-type"
                                                class="form-control directory_field bdas-category-location">
                                            <option><?php _e('Select a location'); ?></option>
                                            <?php echo $locations_fields; ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } else {
                                $select_listing_map = get_directorist_option('select_listing_map', 'google');
                                wp_enqueue_script('bdm-current-js');
                                wp_localize_script('bdm-current-js', 'adbdp_geolocation', array('select_listing_map' => $select_listing_map));
                                $geo_loc = directorist_icon( 'las la-crosshairs', false, 'bdmv_get_loc' );
                                $address_value = !empty($_POST['address']) ? $_POST['address'] : '';
                                $cityLat_value = !empty($_POST['cityLat']) ? $_POST['cityLat'] : '';
                                $cityLng_value = !empty($_POST['cityLng']) ? $_POST['cityLng'] : '';
                                ?>
                                <div class="col-md-6 col-sm-12 col-lg-4 search-address" data-address="yes">
                                    <div class="atbdp_map_address_field">
                                        <div class="atbdp_get_address_field">
                                            <input type="text" name="address" id="address"
                                                   value="<?php echo !empty($_GET['address']) ? $_GET['address'] : $address_value; ?>"
                                                   placeholder="<?php _e('location', 'directorist'); ?>"
                                                   autocomplete="off"
                                                   class="form-control location-name"><?php echo $geo_loc; ?>
                                        </div>
                                        <div class="address_result">
                                        </div>
                                        <input type="hidden" id="cityLat" name="cityLat"
                                               value="<?php echo !empty($_GET['cityLat']) ? $_GET['cityLat'] : $cityLat_value; ?>"/>
                                        <input type="hidden" id="cityLng" name="cityLng"
                                               value="<?php echo !empty($_GET['cityLng']) ? $_GET['cityLng'] : $cityLng_value; ?>"/>
                                    </div>
                                </div>
                            <?php }
                        } ?>
                        <?php
                        /**
                         * @since 5.0
                         */
                        do_action('atbdp_search_field_after_location');

                        ?>
                    </div>
                </div>
                <?php if (("yes" == $search_price) || ("yes" == $search_price_range)) { ?>
                    <div class="form-group ">

                        <label class=""><?php _e('Price Range', 'directorist'); ?></label>
                        <div class="price_ranges">
                            <?php if ("yes" == $search_price) {
                                $min_price_val = !empty($_POST['price']) ? esc_attr($_POST['price'][0]) : '';
                                $max_price_val = !empty($_POST['price']) ? esc_attr($_POST['price'][1]) : '';
                                ?>
                                <div class="range_single" data-price="yes">
                                    <input type="text" name="price[0]" class="form-control price"
                                           placeholder="<?php _e('Min Price', 'directorist'); ?>"
                                           value="<?php echo !empty($_GET['price']) ? esc_attr($_GET['price'][0]) : $min_price_val; ?>">
                                </div>
                                <div class="range_single">
                                    <input type="text" name="price[1]" class="form-control price"
                                           placeholder="<?php _e('Max Price', 'directorist'); ?>"
                                           value="<?php echo !empty($_GET['price']) ? esc_attr($_GET['price'][1]) : $max_price_val; ?>">
                                </div>
                            <?php }
                            if ("yes" == $search_price_range) { ?>
                                <div class="price-frequency" data-price-range="yes">
                                    <label class="pf-btn"><input type="radio" name="price_range"
                                                                 value="bellow_economy"<?php if ((!empty($_GET['price_range']) && 'bellow_economy' == $_GET['price_range']) || (!empty($_POST['price_range']) && 'bellow_economy' == $_POST['price_range'])) {
                                            echo "checked='checked'";
                                        } ?>><span><?php echo $c_symbol; ?></span></label>
                                    <label class="pf-btn"><input type="radio" name="price_range"
                                                                 value="economy" <?php if ((!empty($_GET['price_range']) && 'economy' == $_GET['price_range']) || (!empty($_POST['price_range']) && 'economy' == $_POST['price_range'])) {
                                            echo "checked='checked'";
                                        } ?>><span><?php echo $c_symbol, $c_symbol; ?></span></label>
                                    <label class="pf-btn"><input type="radio" name="price_range"
                                                                 value="moderate" <?php if ((!empty($_GET['price_range']) && 'moderate' == $_GET['price_range']) || (!empty($_POST['price_range']) && 'moderate' == $_POST['price_range'])) {
                                            echo "checked='checked'";
                                        } ?>><span><?php echo $c_symbol, $c_symbol, $c_symbol; ?></span></label>
                                    <label class="pf-btn"><input type="radio" name="price_range"
                                                                 value="skimming" <?php if ((!empty($_GET['price_range']) && 'skimming' == $_GET['price_range']) || (!empty($_POST['price_range']) && 'skimming' == $_POST['price_range'])) {
                                            echo "checked='checked'";
                                        } ?>><span><?php echo $c_symbol, $c_symbol, $c_symbol, $c_symbol; ?></span></label>
                                </div>
                            <?php } ?>
                        </div>

                    </div><!-- ends: .form-group -->
                <?php } ?>
                <?php if ("yes" == $search_rating) { ?>
                    <div class="form-group search-rating" data-rating="yes">
                        <label><?php _e('Filter by Ratings', 'directorist'); ?></label>
                        <select name='search_by_rating' class="select-basic form-control">
                            <option value=""><?php _e('Select Ratings', 'directorist'); ?></option>
                            <option value="5" <?php if ((!empty($_GET['search_by_rating']) && '5' == $_GET['search_by_rating']) || (!empty($_POST['search_by_rating']) && '5' == $_POST['search_by_rating'])) {
                                echo "selected";
                            } ?>><?php _e('5 Star', 'directorist'); ?></option>
                            <option value="4" <?php if ((!empty($_GET['search_by_rating']) && '4' == $_GET['search_by_rating']) || (!empty($_POST['search_by_rating']) && '4' == $_POST['search_by_rating'])) {
                                echo "selected";
                            } ?>><?php _e('4 Star & Up', 'directorist'); ?></option>
                            <option value="3" <?php if ((!empty($_GET['search_by_rating']) && '3' == $_GET['search_by_rating']) || (!empty($_POST['search_by_rating']) && '3' == $_POST['search_by_rating'])) {
                                echo "selected";
                            } ?>><?php _e('3 Star & Up', 'directorist'); ?></option>
                            <option value="2" <?php if ((!empty($_GET['search_by_rating']) && '2' == $_GET['search_by_rating']) || (!empty($_POST['search_by_rating']) && '2' == $_POST['search_by_rating'])) {
                                echo "selected";
                            } ?>><?php _e('2 Star & Up', 'directorist'); ?></option>
                            <option value="1" <?php if ((!empty($_GET['search_by_rating']) && '1' == $_GET['search_by_rating']) || (!empty($_POST['search_by_rating']) && '1' == $_POST['search_by_rating'])) {
                                echo "selected";
                            } ?>><?php _e('1 Star & Up', 'directorist'); ?></option>
                        </select>
                    </div><!-- ends: .form-group -->
                <?php } ?>
                <?php if ('map_api' == $listing_map_location_address && "yes" == $radius_search) {
                    $default_radius_distance = !empty($default_radius_distance) ? $default_radius_distance : 0;
                    $radius_value = !empty($_POST['miles']) ? $_POST['miles'] : $default_radius_distance;
                    ?>
                    <div class="form-group search-radius" data-radius="yes">
                        <div class="atbdpr-range rs-primary">
                            <span><?php _e('Radius Search', 'directorist'); ?></span>
                            <div class="atbd_slider-range-wrapper">
                                <div class="atbd_slider-range"></div>
                                <p class="d-flex justify-content-between">
                                    <span class="atbdpr_amount"></span>
                                </p>
                                <input type="hidden" id="atbd_rs_value" name="miles"
                                       value="<?php echo !empty($_GET['miles']) ? $_GET['miles'] : $radius_value; ?>">
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <?php
                if ("yes" == $search_open_now && in_array('directorist-business-hours/bd-business-hour.php', apply_filters('active_plugins', get_option('active_plugins')))) { ?>
                    <div class="form-group search-open" data-open="yes">
                        <label><?php _e('Open Now', 'directorist'); ?></label>
                        <div class="check-btn">
                            <div class="btn-checkbox">
                                <label>
                                    <input type="checkbox" name="open_now"
                                           value="open_now" <?php if ((!empty($_GET['open_now']) && 'open_now' == $_GET['open_now']) || (!empty($_POST['open_now']) && 'open_now' == $_POST['open_now'])) {
                                        echo "checked='checked'";
                                    } ?>>
                                    <span><?php directorist_icon( 'las la-clock' ); ?><?php _e('Open Now', 'directorist'); ?> </span>
                                </label>
                            </div>
                        </div>
                    </div><!-- ends: .form-group -->
                <?php }
                if ("yes" == $search_tag) {
                    $terms = get_terms(ATBDP_TAGS);
                    if (!empty($terms)) {
                        ?>
                        <div class="form-group ads-filter-tags" data-tag="yes">
                            <label><?php echo !empty($tag_label) ? $tag_label : __('Tags', 'directorist'); ?></label>
                            <div class="bads-tags">
                                <?php
                                $rand = rand();
                                foreach ($terms as $term) {
                                    ?>
                                    <div class="custom-control custom-checkbox checkbox-outline checkbox-outline-primary">
                                        <input type="checkbox" class="custom-control-input"
                                               name="in_tag" value="<?php echo $term->term_id; ?>"
                                               id="<?php echo $rand . $term->term_id; ?>" <?php if ((!empty($_GET['in_tag']) && $term->term_id == $_GET['in_tag']) || (!empty($_POST['in_tag']) && $term->term_id == $_POST['in_tag'])) {
                                            echo "checked";
                                        } ?>>
                                        <span class="check--select"></span>
                                        <label for="<?php echo $rand . $term->term_id; ?>"
                                               class="custom-control-label"><?php echo $term->name; ?></label>
                                    </div>
                                <?php } ?>
                            </div>
                            <a href="#"
                               class="more-less ad"><?php _e('Show More', 'directorist'); ?></a>
                        </div><!-- ends: .form-control -->
                    <?php }
                }
                if ("yes" == $search_custom_fields) {
                    $cat_value_for_custom_field = !empty($_POST['in_cat']) ? $_POST['in_cat'] : 0;
                    ?>
                    <div id="atbdp-custom-fields-search" class="atbdp-custom-fields-search"
                         data-custom-search-field="yes">
                        <?php do_action('wp_ajax_atbdp_custom_fields_search', !empty($_GET['in_cat']) ? $_GET['in_cat'] : $cat_value_for_custom_field); ?>
                    </div>
                <?php } ?>
                <?php if ("yes" == $search_website || "yes" == $search_email || "yes" == $search_phone || "yes" == $search_address || "yes" == $search_zip_code) { ?>
                    <div class="form-group">
                        <div class="bottom-inputs">
                            <?php if ("yes" == $search_website) {
                                $website_value = !empty($_POST['website']) ? $_POST['website'] : '';
                                ?>
                                <div class="search-website" data-website="yes">
                                    <input type="text" name="website"
                                           placeholder="<?php echo !empty($website_label) ? $website_label : __('Website', 'directorist'); ?>"
                                           value="<?php echo !empty($_GET['website']) ? $_GET['website'] : $website_value; ?>"
                                           class="form-control">
                                </div>
                            <?php }
                            if ("yes" == $search_email) {
                                $email_value = !empty($_POST['email']) ? $_POST['email'] : '';
                                ?>
                                <div class="search-email" data-email="yes">
                                    <input type="text" name="email"
                                           placeholder="<?php echo !empty($email_label) ? $email_label : __('Email', 'directorist'); ?>"
                                           value="<?php echo !empty($_GET['email']) ? $_GET['email'] : $email_value; ?>"
                                           class="form-control">
                                </div>
                            <?php }
                            if ("yes" == $search_phone) {
                                $phone_value = !empty($_POST['phone']) ? $_POST['phone'] : '';
                                ?>
                                <div class="search-phone" data-phone="yes">
                                    <input type="text" name="phone"
                                           placeholder="<?php _e('Phone Number', 'directorist'); ?>"
                                           value="<?php echo !empty($_GET['phone']) ? $_GET['phone'] : $phone_value; ?>"
                                           class="form-control">
                                </div>
                            <?php }
                            if ("yes" == $search_fax) {
                                $fax_value = !empty($_POST['fax']) ? $_POST['fax'] : '';
                                ?>
                                <div class="search-fax" data-fax="yes">
                                    <input type="text" name="fax"
                                           placeholder="<?php echo !empty($fax_label) ? $fax_label : __('Fax', 'directorist'); ?>"
                                           value="<?php echo !empty($_GET['fax']) ? $_GET['fax'] : $fax_value; ?>"
                                           class="form-control">
                                </div>
                            <?php }
                            if ("yes" == $search_zip_code) {
                                $zip_code_value = !empty($_POST['zip_code']) ? $_POST['zip_code'] : '';
                                ?>
                                <div class="search-zip" data-zip="yes">
                                    <input type="text" name="zip_code"
                                           placeholder="<?php echo !empty($zip_label) ? $zip_label : __('Zip/Post Code', 'directorist'); ?>"
                                           value="<?php echo !empty($_GET['zip_code']) ? $_GET['zip_code'] : $zip_code_value; ?>"
                                           class="form-control">
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="bdas-filter-actions">
                    <?php if ("yes" == $search_reset_filters) {
                        ?>
                        <button type="reset"
                                class="btn btn-outline btn-outline-primary btn-sm reset-filters"
                                data-reset="yes"><?php _e('Reset Filters', 'directorist-listings-map'); ?></button>
                    <?php }
                    if ("yes" == $search_apply_filters) { ?>
                        <button type="submit"
                                class="btn btn-primary btn-sm ajax-search"
                                data-apply="yes"><?php _e('Apply Filters', 'directorist-listings-map'); ?></button>
                    <?php } ?>

                </div><!-- ends: .bdas-filter-actions -->
            </div> <!--ads advanced -->
        </div>
        <input type="hidden" id="display_header" value="<?php echo !empty($display_header) ? $display_header : ''; ?>">
        <input type="hidden" id="header_title"
               value="<?php echo !empty($header_title_for_search) ? $header_title_for_search : ''; ?>">
        <input type="hidden" id="show_pagination"
               value="<?php echo !empty($show_pagination) ? $show_pagination : 'yes'; ?>">
        <input type="hidden" id="listings_per_page"
               value="<?php echo !empty($atts['listings_per_page']) ? $atts['listings_per_page'] : 6; ?>">
        <input type="hidden" id="location_slug" value="<?php echo !empty($term_slug) ? $term_slug : ''; ?>">
        <input type="hidden" id="category_slug" value="<?php echo !empty($category_slug) ? $category_slug : ''; ?>">
    <?php }

}
if (!function_exists('bdmv_custom_post')) {

    function bdmv_custom_field_post()
    {
        $args = array(
            'post_type' => ATBDP_CUSTOM_FIELD_POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'searchable',
                    'value' => 1,
                    'type' => 'NUMERIC',
                    'compare' => '='
                ),
            ),
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
        );
        $acadp_query = new WP_Query($args);
        return $acadp_query;
    }

}