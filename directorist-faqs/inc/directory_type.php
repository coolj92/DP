<?php
/*
 * Class: Business Directory Multiple Image = ATPP
 * */
if (!class_exists('FAQS_Post_Type_Manager')) :
    class FAQS_Post_Type_Manager
    {
        public function __construct()
        {

            add_filter('atbdp_form_preset_widgets', array($this, 'atbdp_form_builder_widgets'));
            add_filter('atbdp_single_listing_content_widgets', array($this, 'atbdp_single_listing_content_widgets'));
            add_filter( 'directorist_field_template', array( $this, 'directorist_field_template' ), 10, 2 );
            add_filter( 'directorist_single_item_template', array( $this, 'directorist_single_item_template' ), 10, 2 );

        }

        public function directorist_single_item_template( $template, $field_data ) {


            if( 'faqs' !== $field_data['widget_name'] ) {
                return $template;
            }  

            $field_on_demand = apply_filters( 'directorist_faqs_single_field_templete_on_demand', true, $field_data );

            if( $field_on_demand ) {
                $template .= Listings_fAQs()->load_template('view-faqs', [ 'field_data' => $field_data ]);
            }

            return $template;
        }

        public function directorist_field_template( $template, $field_data ) {

            if( 'faqs' !== $field_data['widget_name'] ) {
                return $template;
            }          

            $field_on_demand = apply_filters( 'directorist_faqs_form_field_templete_on_demand', true, $field_data );

            if( $field_on_demand ) {
                $template .= Listings_fAQs()->load_template('faqs', [ 'field_data' => $field_data ]);
            }

            return $template;
        }

        public function atbdp_single_listing_content_widgets($widgets)
        {
            $widgets['faqs'] = [
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
        public function atbdp_form_builder_widgets($widgets)
        {
            $widgets['faqs'] = [
                'label' => 'FAQs',
                'icon' => 'la la-question',
                'show' => true,
                'options' => [
                    'type' => [
                        'type'  => 'hidden',
                        'value' => 'add_new',
                    ],
                    'field_key' => [
                        'type'   => 'meta-key',
                        'hidden' => true,
                        'value'  => 'faqs',
                    ],
                    'label' => [
                        'type'  => 'text',
                        'label' => 'Label',
                        'value' => 'FAQs',
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
      

    }
endif;