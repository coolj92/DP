<?php global $bdmv_listings;

if ( $bdmv_listings->get_boolean( 'display_header' ) ) { ?>
    <div class="directorist-header-bar">
        <div class="<?php $bdmv_listings->header_container_class(); ?>">
            <div class="directorist-row">
                <div class="directorist-col-md-12">
                    <div class="directorist-header directorist-generic-header">
                        <?php if ( $bdmv_listings->has_header_title() ) { ?>
                            <div class="directorist-generic-header__title">
                            <?php
                            /**
                             * @since 5.4.0
                             */ 
                             if ( $bdmv_listings->column_is( 1 ) || $bdmv_listings->column_is( '2-style-2' ) ) { ?>
                                <button class="more-filter btn btn-outline btn-outline-primary">
                                    <?php directorist_icon( 'las la-filter' ); ?>
                                    <?php _e("All Filters","directorist_listings_map"); ?>
                                </button>
                                <?php }
                                do_action('bdmv_after_filter_button_in_listings_header');
                                if ( $bdmv_listings->has_header_title() ) {
                                    echo apply_filters('atbdp_total_listings_found_text',"<h3>". $bdmv_listings->data['header_title'] ."</h3>", $bdmv_listings->data['header_title'] );
                                }
                                ?>
                            </div>
                            <?php
                        }
                        /**
                         * @since 5.4.0
                         */
                        do_action('bdmv_after_total_listing_found_in_listings_header', $bdmv_listings->data['header_title'] );

                        if ( $bdmv_listings->get_option('listings_map_viewas') || $bdmv_listings->get_option('listings_map_sortby') ) { ?>
                            <div class="directorist-actions-btns directorist-listing-actions-btn btn-toolbar" role="toolbar">
                                <!-- Views dropdown -->
                                <?php if ( ! empty( $bdmv_listings->get_option('listings_map_viewas') ) ) {
                                    $html = '<div class="directorist-dropdown directorist-dropdown-js">';
                                    $html .= '<a class="directorist-dropdown__toggle directorist-dropdown__toggle-js directorist-btn directorist-btn-sm directorist-btn-px-15 directorist-btn-outline-primary directorist-toggle-has-icon" href="#" role="button" id="viewAsDropdownMenuLink">
                                        ' . $bdmv_listings->options['view_as_text'] . '<span class="atbd_drop-caret"></span>
                                    </a>';
                                    $html .= '<div class="directorist-dropdown__links directorist-dropdown__links-js view-as" aria-labelledby="viewAsDropdownMenuLink">';

                                    $html .= sprintf('<a data-view="grid" class="directorist-dropdown__links--single map-view-grid %s">%s</a>', $bdmv_listings->get_view_as_status( 'grid' ) , __('Grid','directorist-listings-with-map'));
                                    $html .= sprintf('<a data-view="list" class="directorist-dropdown__links--single map-view-list %s">%s</a>', $bdmv_listings->get_view_as_status( 'list' ) ,__('List','directorist-listings-with-map'));
                                    $html .= '</div>';
                                    $html .= '</div>';
                                    echo apply_filters('bdmv_view_as',$html);
                                } ?>
                                <!-- Orderby dropdown -->
                                <?php
                                $sort_html = '';
                                if ( $bdmv_listings->options['listings_map_sortby'] ) {
                                    $sort_by = isset( $_POST['sort_by'] ) ? $_POST['sort_by'] : '';
                                    $title_asc_active = ('title-asc' == $sort_by) ? "active" : '';
                                    $title_desc_active = ('title-desc' == $sort_by) ? "active" : '';
                                    $date_desc_active = ('date-desc' == $sort_by) ? "active" : '';
                                    $date_asc_active = ('date-asc' == $sort_by) ? "active" : '';
                                    $price_asc_active = ('price-asc' == $sort_by) ? "active" : '';
                                    $price_desc_active = ('price-desc' == $sort_by) ? "active" : '';
                                    $rand_active = ('rand' == $sort_by) ? "active" : '';
                                    $sort_html .= '<div class="directorist-dropdown directorist-dropdown-js directorist-dropdown-right">
                                        <a class="directorist-dropdown__toggle directorist-dropdown__toggle-js directorist-btn directorist-btn-sm directorist-btn-px-15 directorist-btn-outline-primary directorist-toggle-has-icon" href="#" role="button" id="sortByDropdownMenuLink"> ' .
                                           $bdmv_listings->options['sort_by_text'] . ' <span class="atbd_drop-caret"></span>
                                        </a>';
                                    $sort_html .= '<div class="directorist-dropdown__links directorist-dropdown__links-js sort-by"
                                             aria-labelledby="sortByDropdownMenuLink">';

                                    $sort_html .= sprintf('<a class="directorist-dropdown__links--single sort-title-asc %s" data-sort="title-asc">%s</a>', $title_asc_active, __("A to Z ( title )", 'directorist-listings-map'));
                                    $sort_html .= sprintf('<a class="directorist-dropdown__links--single sort-title-desc %s" data-sort="title-desc">%s</a>', $title_desc_active,  __("Z to A ( title )", 'directorist-listings-map'));
                                    $sort_html .= sprintf('<a class="directorist-dropdown__links--single sort-date-desc %s" data-sort="date-desc">%s</a>', $date_desc_active, __("Latest listings", 'directorist-listings-map'));
                                    $sort_html .= sprintf('<a class="directorist-dropdown__links--single sort-date-asc %s" data-sort="date-asc">%s</a>', $date_asc_active, __("Oldest listings", 'directorist-listings-map'));
                                    $sort_html .= sprintf('<a class="directorist-dropdown__links--single sort-price-asc %s" data-sort="price-asc">%s</a>',$price_asc_active, __("Price ( low to high )", 'directorist-listings-map'));
                                    $sort_html .= sprintf('<a class="directorist-dropdown__links--single sort-price-desc %s" data-sort="price-desc">%s</a>', $price_desc_active, __("Price ( high to low )", 'directorist-listings-map'));
                                    $sort_html .= sprintf('<a class="directorist-dropdown__links--single sort-rand %s" data-sort="rand">%s</a>',$rand_active, __("Random listings", 'directorist-listings-map'));
                                    $sort_html .= ' </div>';
                                    $sort_html .= ' </div>';
                                    /**
                                     * @since 5.4.0
                                     */
                                    echo apply_filters('atbdp_listings_with_map_header_sort_by_button', $sort_html);
                                }
                                ?>
                            </div>
                        <?php } ?>
                    </div>

                    <!--ads advance search-->
                    <?php if ( $bdmv_listings->column_is( 1 ) || $bdmv_listings->column_is( '2-style-2' ) ) {
                        $bdmv_listings->header_advance_search();
                    } ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>