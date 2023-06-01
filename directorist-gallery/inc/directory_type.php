<?php
/*
 * Class: Business Directory Multiple Image = ATPP
 * */
if (!class_exists('Gallery_Post_Type_Manager')) :
    class Gallery_Post_Type_Manager
    {
        public function __construct()
        {
            add_filter('atbdp_form_preset_widgets', array($this, 'atbdp_form_builder_widgets'));
            add_filter('atbdp_single_listing_content_widgets', array($this, 'atbdp_single_listing_content_widgets'));
            add_filter( 'directorist_field_template', array( $this, 'directorist_field_template' ), 10, 2 );
            add_filter( 'directorist_single_item_template', array( $this, 'directorist_single_item_template' ), 10, 2 );

        }

        public function directorist_single_item_template( $template, $field_data ) {
            if( 'gallery' === $field_data['widget_name'] ) {
                $template .= BD_Gallery()->load_template('view_gallery', [ 'field_data' => $field_data ]);
            }

            return $template;
        }

        public function directorist_field_template( $template, $field_data ) {

            if( 'gallery' === $field_data['widget_name'] ) {
               if( is_admin() ) { ?>
               <div id="directorist" class="directorist atbd_wrapper">
               <?php $template .= BD_Gallery()->load_template('gallery-img-field', [ 'field_data' => $field_data ]); ?>
              </div>
              <?php } else {
                $template .= BD_Gallery()->load_template('gallery_image_upload', [ 'field_data' => $field_data ]);
               }
            }

            return $template;
        }

        public function atbdp_single_listing_content_widgets($widgets)
        {
            $widgets['gallery'] = [
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
            $widgets['gallery'] = [
                'label' => 'Gallery Images',
                'icon' => 'uil uil-image',
                'options' => [
                    'type' => [
                        'type'  => 'hidden',
                        'value' => 'media',
                    ],
                    'field_key' => [
                        'type'   => 'meta-key',
                        'hidden' => true,
                        'value'  => 'gallery_img',
                    ],
                    'label' => [
                        'type'  => 'hidden',
                        'value' => 'Images',
                    ],
                    'required' => [
                        'type'  => 'toggle',
                        'label'  => 'Required',
                        'value' => false,
                    ],
                    'select_files_label' => [
                        'type'  => 'text',
                        'label' => 'Select Files Label',
                        'value' => 'Select Files',
                    ],
                    'max_image_limit' => [
                        'type'  => 'number',
                        'label' => 'Max Image Limit',
                        'value' => 5,
                    ],
                    'max_per_image_limit' => [
                        'type'  => 'number',
                        'label' => __( 'Max Upload Size Per Image in MB', 'directorist' ),
                        'description' => __( 'Here 0 means unlimited.', 'directorist' ) ,
                        'value' => 0,
                    ],
                    'max_total_image_limit' => [
                        'type'  => 'number',
                        'label' => 'Total Upload Size in MB',
                        'value' => 2,
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