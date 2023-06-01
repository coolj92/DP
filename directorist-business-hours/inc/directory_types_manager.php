<?php
// prevent direct access to the file
defined('ABSPATH') || die('No direct script access allowed!');
if (!class_exists('DBH_Directory_Type')) :
    class DBH_Directory_Type
    {
        public function __construct()
        {
            
            add_filter('atbdp_form_preset_widgets', array($this, 'atbdp_form_preset_widget'));
            add_filter('atbdp_single_listing_content_widgets', array($this, 'atbdp_single_listing_content_widgets'));
            add_filter('atbdp_listing_type_settings_field_list', array($this, 'atbdp_listing_type_settings_field_list'));
            add_filter( 'directorist_field_template', array( $this, 'directorist_field_template' ), 10, 2 );
            add_filter( 'directorist_single_item_template', array( $this, 'directorist_single_item_template' ), 10, 2 );
            add_filter( 'directorist_search_field_template', array( $this, 'directorist_search_field_template' ), 10, 2 );
            add_filter('atbdp_ultimate_listing_meta_user_submission', array($this, 'listing_meta_user_submission'), 10, 2);
            add_filter('atbdp_listing_meta_admin_submission', array($this, 'listing_meta_user_submission'), 10, 2);
            add_action('atbdp_all_listings_badge_template', array($this, 'atbdp_all_listings_badge_template'));
            add_filter( 'directorist_search_form_widgets', array($this, 'directorist_search_form_widgets'));
            add_filter( 'atbdp_add_listing_form_validation_logic', array( $this, 'atbdp_add_listing_form_validation_logic' ), 10, 3 );

        }


        public function atbdp_add_listing_form_validation_logic( $default_logic, $field_data, $info ) {

            if( 'business_hours' !== $field_data['widget_name'] ) {
                return $default_logic;
            }

            $disable_bz_hour_listing = !empty( $info['disable_bz_hour_listing'] ) ? sanitize_text_field( $info['disable_bz_hour_listing'] ) : '';
            if( $disable_bz_hour_listing ) {
                return false;
            }
            return $default_logic;
        }

        public function directorist_search_form_widgets( $widgets ) {

            $new_widget = [
                'options' => [
                    'required' => [
                        'type'  => 'toggle',
                        'label'  => 'Required',
                        'value' => false,
                    ],
                    'label' => [
                        'type'  => 'text',
                        'label' => 'Label',
                        'value' => 'Open Now',
                        'sync' => false,
                    ],
                ]
                ];
            $widgets['available_widgets']['widgets']['business_hours'] = $new_widget;
            return $widgets;
        }

        public function atbdp_all_listings_badge_template( $field ) {
            switch ($field['widget_key']) {
                case 'open_close_badge':
                    BD_Business_Hour()->load_template('badge', [ 'field' => $field ]);
                break;
            }  
        }

        public function listing_meta_user_submission( $meta, $info ) {
            $meta['_enable247hour']             = !empty( $info['enable247hour'] ) ? sanitize_text_field( $info['enable247hour'] ) : '';
            $meta['_disable_bz_hour_listing']   = !empty( $info['disable_bz_hour_listing'] ) ? sanitize_text_field( $info['disable_bz_hour_listing'] ) : '';
            $meta['_bdbh_version']              = !empty( $info['bdbh_version'] ) ? sanitize_text_field( $info['bdbh_version'] ) : '';
            return $meta;
        }

        public function directorist_field_template( $template, $field_data ) {

            if( 'business_hours' !== $field_data['widget_name'] ) {
                return $template;
            }          

            $field_on_demand = apply_filters( 'directorist_hours_form_field_templete_on_demand', true, $field_data );

            if( $field_on_demand ) {
                $template .= BD_Business_Hour()->load_template('business-hour-fields', [ 'field_data' => $field_data ]);
            }

            return $template;
        }
        
        public function directorist_search_field_template( $template, $field_data ) {

            if( 'business_hours' === $field_data['widget_name'] ) {
                $template .= BD_Business_Hour()->load_template('search', [ 'field_data' => $field_data ]);
             }
 
             return $template;

        }

        public function directorist_single_item_template( $template, $field_data ) {

            if( 'business_hours' !== $field_data['widget_name'] ) {
                return $template;
            }          

            $field_on_demand = apply_filters( 'directorist_hours_single_field_templete_on_demand', true, $field_data );

            if( $field_on_demand ) {
                $template .= BD_Business_Hour()->load_template('show_hours', [ 'field_data' => $field_data ]);
             }
 
             return $template;

        }

        public function atbdp_single_listing_content_widgets($widgets)
        {
            $widgets['business_hours'] = [
                'options' => [
                    'icon' => [
                        'type'  => 'icon',
                        'label' => 'Icon',
                        'value' => 'la la-question',
                    ],
                ]
            ];
            return $widgets;
        }

        public function atbdp_form_preset_widget( $widgets ){
            $widgets['business_hours'] = [
                'label' => 'Business Hours',
                'icon' => 'la la-clock-o',
                'show' => true,
                'options' => [
                    'type' => [
                        'type'  => 'hidden',
                        'value' => 'hours',
                    ],
                    'field_key' => [
                        'type'   => 'meta-key',
                        'hidden' => true,
                        'value'  => 'bdbh',
                    ],
                    'label' => [
                        'type'  => 'text',
                        'label' => 'Label',
                        'value' => 'Business Hours',
                    ],
                    'required' => [
                        'type'  => 'toggle',
                        'label'  => 'Required',
                        'value' => false,
                    ],
                    'only_for_admin' => [
                        'type'  => 'toggle',
                        'label'  => 'Only For Admin Use',
                        'value' => false,
                    ],


                ],
            ];
            return $widgets;
        }


        public function atbdp_listing_type_settings_field_list( $fields ){

            foreach( $fields as $key => $value ) {
                // setup widgets
                $hours_widget = [
                    'type' => "badge",
                    'id' => "open_close_badge",
                    'label' => "Open/Close",
                    'icon' => "uil uil-text-fields",
                    'hook' => "atbdp_open_close_badge",
                    'options' => [],
                  ];
               
                if( 'listings_card_grid_view' === $key  ) {
                    // register widget
                    $fields[$key]['card_templates']['grid_view_with_thumbnail']['widgets']['open_close_badge'] = $hours_widget;
                    $fields[$key]['card_templates']['grid_view_without_thumbnail']['widgets']['open_close_badge'] = $hours_widget;

                    // grid with preview image
                      array_push( $fields[$key]['card_templates']['grid_view_with_thumbnail']['layout']['thumbnail']['top_right']['acceptedWidgets'], 'open_close_badge' );
                      array_push( $fields[$key]['card_templates']['grid_view_with_thumbnail']['layout']['thumbnail']['top_left']['acceptedWidgets'], 'open_close_badge' );
                      array_push( $fields[$key]['card_templates']['grid_view_with_thumbnail']['layout']['thumbnail']['bottom_right']['acceptedWidgets'], 'open_close_badge' );
                      array_push( $fields[$key]['card_templates']['grid_view_with_thumbnail']['layout']['thumbnail']['bottom_left']['acceptedWidgets'], 'open_close_badge' );
                      array_push( $fields[$key]['card_templates']['grid_view_with_thumbnail']['layout']['body']['top']['acceptedWidgets'], 'open_close_badge' );
                      
                      // grid without preview image
                      array_push( $fields[$key]['card_templates']['grid_view_without_thumbnail']['layout']['body']['quick_info']['acceptedWidgets'], 'open_close_badge' );
                    }
                    
                    if( 'listings_card_list_view' === $key ) {
                        // register widget
                        $fields[$key]['card_templates']['list_view_with_thumbnail']['widgets']['open_close_badge'] = $hours_widget;
                        $fields[$key]['card_templates']['list_view_without_thumbnail']['widgets']['open_close_badge'] = $hours_widget;
                        
                        // grid with preview image
                        array_push( $fields[$key]['card_templates']['list_view_with_thumbnail']['layout']['thumbnail']['top_right']['acceptedWidgets'], 'open_close_badge' );
                        array_push( $fields[$key]['card_templates']['list_view_with_thumbnail']['layout']['body']['top']['acceptedWidgets'], 'open_close_badge' );
                        array_push( $fields[$key]['card_templates']['list_view_with_thumbnail']['layout']['body']['right']['acceptedWidgets'], 'open_close_badge' );

                        // grid without preview image
                        array_push( $fields[$key]['card_templates']['list_view_without_thumbnail']['layout']['body']['top']['acceptedWidgets'], 'open_close_badge' );
                        array_push( $fields[$key]['card_templates']['list_view_without_thumbnail']['layout']['body']['right']['acceptedWidgets'], 'open_close_badge' );
                }

            }
            return $fields;
        }
        
    }
endif;