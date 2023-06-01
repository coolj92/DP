<?php
namespace SWBDP_DIRLINK\Builder;
use Directorist\Helper;
class Builder {
	/**
	 * Register
	 *
	 * @return void
	 */
	public function register() {

		add_filter( 'atbdp_form_preset_widgets', array( $this, 'preset_widgets' ) );
		add_filter( 'atbdp_single_listing_content_widgets', array( $this, 'single_listing_content_widgets' ) );
		add_filter( 'directorist_field_template', array( $this, 'directorist_field_template' ), 10, 2 );
		add_filter( 'directorist_single_item_template', array( $this, 'directorist_single_item_template' ), 10, 2 );

		//ajax  
		add_action( 'wp_ajax_dirlink_searching_type', array( $this, 'dirlink_searching_type' ) );
		add_action( 'wp_ajax_nopriv_dirlink_searching_type', array( $this, 'dirlink_searching_type' ) );

	}

	public function dirlink_searching_type() {
		$search = isset( $_REQUEST['search'] ) ? sanitize_text_field( $_REQUEST['search'] ) : '';
		$type   = isset( $_REQUEST['type'] ) ? sanitize_text_field( $_REQUEST['type'] ) : '';
		try {

			$args = array(
				'post_type'              => ATBDP_POST_TYPE,
				'post_status'            => 'publish',
				'orderby'                => 'relevance',
				'posts_per_page'         => 10,
				's'                      => $search,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			);

			$tax_queries['tax_query'] = array(
				'taxonomy' => ATBDP_TYPE,
				'field' => 'term_id',
				'terms' => $type,
			);
			$args['tax_query'] = $tax_queries;

			$query = new \WP_Query( $args );
			$data = array();

			if ( $query->have_posts() ) {
				$data = array_map( function( $post ) {
					return array(
						'id'   => $post->ID,
						'text' => strip_tags( $post->post_title ),
					);
				}, $query->posts );
			}

			wp_send_json_success( $data );
		} catch( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	public static function get_preview_img( $id, $directory_type ) {
		$id = get_the_ID();
		$default_image_src = Helper::default_preview_image_src( $directory_type );
		$image_quality     = get_directorist_option('preview_image_quality', 'large');
		$listing_prv_img   = get_post_meta($id, '_listing_prv_img', true);
		$listing_img       = get_post_meta($id, '_listing_img', true);

		if ( is_array( $listing_img ) && ! empty( $listing_img ) ) {
			$thumbnail_img = atbdp_get_image_source( $listing_img[0] );
			$thumbnail_id = $listing_img[0];
		}

		if ( ! empty( $listing_prv_img ) ) {
			$thumbnail_img = atbdp_get_image_source( $listing_prv_img );
			$thumbnail_id = $listing_prv_img;
		}

		if ( ! empty( $img_src ) ) {
			$thumbnail_img = $img_src;
			$thumbnail_id = 0;
		}

		if ( empty( $thumbnail_img ) ) {
			$thumbnail_img = $default_image_src;
			$thumbnail_id = 0;
		}

		return $thumbnail_img;
	}

	public static function get_review_data() {
		// Review
		$average           = ATBDP()->review->get_average(get_the_ID());
		$average           = (int) $average;
		$average_with_zero = number_format( $average, 1 );
		$reviews_count     = ATBDP()->review->db->count(array('post_id' => get_the_ID()));
		$review_text       = ( $reviews_count > 1 ) ? 'Reviews' : 'Review';

		// Icons
		$icon_empty_star = '<i class="'. 'far fa-star'.'"></i>';
		$icon_half_star  = '<i class="'. 'fas fa-star-half-alt'.'"></i>';
		$icon_full_star  = '<i class="'. 'fas fa-star'.'"></i>';

		// Stars
		$star_1 = ( $average >= 0.5 && $average < 1) ? $icon_half_star : $icon_empty_star;
		$star_1 = ( $average >= 1) ? $icon_full_star : $star_1;

		$star_2 = ( $average >= 1.5 && $average < 2) ? $icon_half_star : $icon_empty_star;
		$star_2 = ( $average >= 2) ? $icon_full_star : $star_2;

		$star_3 = ( $average >= 2.5 && $average < 3) ? $icon_half_star : $icon_empty_star;
		$star_3 = ( $average >= 3) ? $icon_full_star : $star_3;

		$star_4 = ( $average >= 3.5 && $average < 4) ? $icon_half_star : $icon_empty_star;
		$star_4 = ( $average >= 4) ? $icon_full_star : $star_4;

		$star_5 = ( $average >= 4.5 && $average < 5 ) ? $icon_half_star : $icon_empty_star;
		$star_5 = ( $average >= 5 ) ? $icon_full_star : $star_5;

		$review_stars = "{$star_1}{$star_2}{$star_3}{$star_4}{$star_5}";

		return [
			'review_stars'    => $review_stars,
			'review_text'     => $review_text,
			'average_reviews' => $average_with_zero,
			'total_reviews'   => $reviews_count,
		];
	}

	public function preset_widgets( $widgets ) { 

        $types = directory_types();
		$current_type = ! empty( $_GET['listing_type_id'] ) ? $_GET['listing_type_id'] : '';
		$enable_multi_directory = get_directorist_option( 'enable_multi_directory' );

		if( $types && ! empty( $enable_multi_directory ) ) {

			foreach( $types as $type ) {

				if( $current_type != $type->term_id ) {

					$widgets['linking-' . $type->term_id] = [
						'label' => 'Link Directory - ' . $type->name,
						'icon' => 'la la-link',
						'show' => true,
						'options' => [
							'type' => [
								'type'  => 'hidden',
								'value' => 'add_new',
							],
							'field_key' => [
								'type'   => 'meta-key',
								'hidden' => true,
								'value'  => 'swbdp_dirlink_type-' . $type->term_id,
							],
							'label' => [
								'type'  => 'text',
								'label' => 'Label',
								'value' => 'Link Directory - ' . $type->name,
							],
							'type' => [
                                'type'  => 'radio',
                                'value' => 'multiple',
                                'label' => __( 'Selection Type', 'directorist-directory-linking' ),
                                'options' => [
                                    [
                                        'label' => __('Single Selection', 'directorist-directory-linking'),
                                        'value' => 'single',
                                    ],
                                    [
                                        'label' => __('Multi Selection', 'directorist-directory-linking'),
                                        'value' => 'multiple',
                                    ]
                                ]
                            ],
							'placeholder' => [
                                'type'  => 'text',
                                'label' => __( 'Placeholder', 'directorist' ),
                                'value' => '',
                            ],
							'only_for_admin' => [
                                'type'  => 'toggle',
                                'label'  => __( 'Only For Admin Use', 'directorist' ),
                                'value' => false,
                            ],
							'required' => [
								'type'  => 'toggle',
								'label'  => 'Required',
								'value' => false,
							],
						],
					];
					

				}

			}

		}

        return $widgets;
	}

	public function single_listing_content_widgets( $widgets ) {

		$types = directory_types();
		$enable_linking_type = get_directorist_option( 'enable_linking_type', true );
		$current_type = ! empty( $_GET['listing_type_id'] ) ? $_GET['listing_type_id'] : '';

		if( $types && ! empty( $enable_linking_type ) ) {

			foreach( $types as $type ) {

				if( $current_type != $type->term_id ) {
					$widgets['linking-' . $type->term_id] = [
						'options' => [
							'icon' => [
								'type'  => 'icon',
								'label' => 'Icon',
								'value' => 'la la-link',
							],
							'posts_per_page' => [
								'type'  => 'text',
								'label' => 'Posts Per Page',
								'value' => '8',
							],
							'display_image' => [
								'type'  => 'toggle',
								'label' => 'Display Image',
								'value' => 'true',
							],
							'display_title' => [
								'type'  => 'toggle',
								'label' => 'Display Title',
								'value' => 'true',
							],
							'display_category' => [
								'type'  => 'toggle',
								'label' => 'Display Category',
								'value' => 'true',
							],
							'display_rating' => [
								'type'  => 'toggle',
								'label' => 'Display Rating',
								'value' => 'true',
							],
							'display_see_post' => [
								'type'  => 'toggle',
								'label' => 'Display View All Posts Link',
								'value' => 'true',
							],
							'linking_view_all_text' => [
								'type'  => 'text',
								'label' => 'View All Listings Label',
								'value' => 'View all listings',
							],
							'display_navigation' => [
								'type'  => 'toggle',
								'label' => 'Display Navigation',
								'value' => 'true',
							],
						]
					];
				}
			}
		}
            return $widgets;
    }

	public function directorist_field_template( $template, $field_data ) {

		$types 				 = directory_types();
		$enable_linking_type = get_directorist_option( 'enable_linking_type', true );
		
		if( $types && ! empty( $enable_linking_type ) ) {

			foreach( $types as $type ) {
	
				if( 'linking-' . $type->term_id === $field_data['widget_name'] ) {
					
					$args = array(
						'post_type' => ATBDP_POST_TYPE,
						'post_status' => 'publish',
					);

                    $tax_queries['tax_query'] = array(
                        'taxonomy' => ATBDP_TYPE,
                        'field' => 'term_id',
                        'terms' => $type->term_id,
                    );
                    $args['tax_query'] = $tax_queries;

					$linking_posts = new \WP_Query( $args );

					$selected_value = ! empty( $field_data['value'] ) ? $field_data['value'] : array();
					$explode_value = ! empty( $selected_value[0] ) ? explode( ',', $selected_value[0] ) : array();
					$selected    = ( 1 < count( $explode_value ) ) ? $explode_value : $selected_value;
					$data = array(
						'linking_posts' => $linking_posts,
						'field_data'    => $field_data,
						'type_id'   	=> $type->term_id,
						'type_value'    => $selected,
					);

					$template .= swbdp_dirlink_load_template( 'add-listing', $data );
				}

			}
			

		}

		return $template;
	}

	public function directorist_single_item_template( $template, $field_data ) {

		$types = directory_types();
		$enable_linking_type = get_directorist_option( 'enable_linking_type', true );
		
		if( $types && ! empty( $enable_linking_type ) ) {

			
		
			foreach( $types as $type ) {
				
				if( 'linking-' . $type->term_id == $field_data['widget_name'] ) {
					$selected_value = ! empty( $field_data['value'] ) ? $field_data['value'] : array();
					$explode_value  = ! empty( $selected_value[0] ) ? explode( ',', $selected_value[0] ) : array();
					$selected   	= ( 1 < count( $explode_value ) ) ? $explode_value : $selected_value;
					$linking_type   = get_term_by( 'ID', $type->term_id, ATBDP_TYPE );
					$post_type      = get_the_terms( get_the_ID(), ATBDP_TYPE );

					$args = array(
						'post_type' => ATBDP_POST_TYPE,
						'post__in' => $selected,
						'posts_per_page' => $field_data['posts_per_page']
					);
					
					$linking_posts = ! empty( $selected ) ? new \WP_Query( $args ) : [];

					$data = array( 
						'linking_posts'	 	 	=> $linking_posts,
						'directory_type' 	 	=> $type->term_id,
						'type_name'		 	 	=> $linking_type->slug,
						'post_type'		 	 	=> $post_type[0]->name,
						'display_image' 	 	=> ! empty( $field_data['display_image'] ) ? $field_data['display_image'] : '',
						'display_title' 	 	=> ! empty( $field_data['display_title'] ) ? $field_data['display_title'] : '',
						'display_category'   	=> ! empty( $field_data['display_category'] ) ? $field_data['display_category'] : '',
						'display_rating' 	 	=> ! empty( $field_data['display_rating'] ) ? $field_data['display_rating'] : '',
						'display_see_post'   	=> ! empty( $field_data['display_see_post'] ) ? $field_data['display_see_post'] : '',
						'linking_view_all_text' => ! empty( $field_data['linking_view_all_text'] ) ? $field_data['linking_view_all_text'] : 'View all listings',
						'display_navigation' 	=> ! empty( $field_data['display_navigation'] ) ? $field_data['display_navigation'] : '',
					);

					$template .= swbdp_dirlink_load_template( 'single-listing', $data );
					
				}

			}

		}
		return $template;
	}

}
