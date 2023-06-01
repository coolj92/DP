<?php
// prevent direct access to the file
defined('ABSPATH') || die('No direct script access allowed!');

class BDMV_Settings
{
    public function __construct()
    {
        // Add setting section to the Directorist settings page.
        add_filter( 'atbdp_extension_settings_submenu', array( $this, 'atbdp_extension_settings_submenus' ) );
        add_filter( 'atbdp_listing_type_settings_field_list', array( $this, 'atbdp_listing_type_settings_field_list' ) );
    }

    public function atbdp_listing_type_settings_field_list( $fields ) {
        $fields['bdmv_listings_with_map_columns'] = [
            'label' => __('Columns', 'directorist-listings-map'),
                    'type'  => 'select',
                    'value' => '3',
                    'options' => $this->directorist_map_listings_columns()
        ];
        $fields['listing_map_view'] = [
            'label' => __('Default View', 'directorist-listings-map'),
                    'type'  => 'select',
                    'value' => 'grid',
                    'options' => [
                        [
                            'value' => 'grid',
                            'label' => __('Grid', 'directorist-listings-map'),
                        ],
                        [
                            'value' => 'list',
                            'label' => __('List', 'directorist-listings-map'),
                        ],
                    ],
        ];
        $fields['listings_map_viewas'] = [
            'label'             => __('Display "View As" Dropdown', 'directorist-listings-map'),
            'type'              => 'toggle',
            'value'             => true,
        ];
        $fields['listings_map_sortby'] = [
            'label'             => __('Display "Sort By" Dropdown', 'directorist-listings-map'),
            'type'              => 'toggle',
            'value'             => true,
        ];
      
        return $fields;
    }

    public function atbdp_extension_settings_submenus( $submenu ) {
        $submenu['listings_with_map'] = [
            'label' => __('Listings with Map', 'directorist-listings-with-map'),
                    'icon' => '<i class="fas fa-map-marked-alt"></i>',
                    'sections' => apply_filters( 'atbdp_booking_settings_controls', [
                        'general_section' => [
                            'title'       => '',
                            'fields'      =>  [ 'bdmv_listings_with_map_columns' ],
                        ],
                        'listings_settings' => [
                            'title'       => __('Listings Settings', 'directorist-listings-with-map'),
                            'fields'      =>  [ 'listing_map_view', 'listings_map_viewas', 'listings_map_sortby' ],
                        ],
                    ] ),
        ];


        return $submenu;
    }

    public function directorist_map_listings_columns(){
        $columns = [
            [
                'value' => '2',
                'label' => __('2', 'directorist-listings-map'),
            ],
            [
                'value' => '3',
                'label' => __('3', 'directorist-listings-map'),
            ],
        ];
        $columns = apply_filters( 'bdmv_columns_setting', $columns );

        return $columns;
    }
}