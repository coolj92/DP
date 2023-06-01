<?php
/**
 * @author  wpWax
 * @since   1.0
 * @version 1.0
 */


if ( ! defined( 'ABSPATH' ) ) exit;


use SWBDP_DIRLINK\Builder\Builder as Builder;

if( empty( $args['linking_posts'] ) ) {
	return;
}

$post_id   = array();
?>


<div class="directorist-linking-content">


    <div class="directorist-linking-content__cards directorist-linking-content__slider">


    <?php if( $args['linking_posts']->have_posts() ) {


            while( $args['linking_posts']->have_posts() ) : $args['linking_posts']->the_post();


            $categories      =  get_the_terms( get_the_ID(), ATBDP_CATEGORY );
            $address = get_post_meta(get_the_ID(), '_address', true);
            $post_id[]       = get_the_ID();
    ?>
        <div class="directorist-linking-card">
            <div class="directorist-linking-card__content">


                <?php if( ! empty( $args['display_image'] ) ) { ?>


                <div class="directorist-linking-card__img">


                    <a href="<?php echo get_the_permalink(); ?>"><img src="<?php echo Builder::get_preview_img( get_the_ID(), $args['directory_type'] ) ?>" alt="<?php echo get_the_title(); ?>"></a>


                </div>


                <?php } ?>


                <div class="directorist-linking-card__details">


                    <?php if( ! empty( $args['display_title'] ) ) { ?>


                    <h2 class="directorist-linking-card__title"><a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a></h2>


                    <?php } ?>
                   
                    <?php if( ! empty( $args['display_category'] ) ) { ?>


                        <div class="directorist-linking-card__category">


                        <?php
                            if( $categories ) {


                                foreach( $categories as $category ) { ?>


                                    <a href="<?php echo esc_url( get_term_link( $category->term_id, ATBDP_CATEGORY ) ); ?>"><?php echo $category->name; ?></a>


                        <?php    }


                            } ?>


                        </div>
                   
                    <?php } ?>


                    <?php
$address = get_post_meta(get_the_ID(), '_address', true);
if( ! empty( $address ) ) {
?>
    <div class="link-address"><?php echo $address; ?></div>
<?php
}
?>
                   
                    <?php if( ! empty( $args['display_rating'] ) ) { ?>


                        <div class="directorist-linking-card__reviews">


                            <span>
                            <?php echo Builder::get_review_data()['review_stars']; ?>
                            </span>


                            <span class="directorist-linking-card__reviews__total">
                                <?php echo Builder::get_review_data()['total_reviews']; ?>
                                <?php echo Builder::get_review_data()['review_text']; ?>
                            </span>


                        </div>


                    <?php } ?>


                </div>


            </div>


        </div><!-- end: .directorist-linking-card -->


    <?php
        endwhile;
        wp_reset_query();
       
    } ?>


    </div>


    <?php if( ! empty( $args['display_see_post'] ) || ! empty( $args['display_navigation'] ) ) { ?>
       
    <div class="directorist-linking-content__action">


        <?php if( ! empty( $args['display_see_post'] ) ) { ?>


            <a href="<?php echo add_query_arg( 'link-id', json_encode( $post_id ), ATBDP_Permalink::get_search_result_page_link() . "?directory_type=" . $args['type_name'] ); ?>" class="directorist-linking-content__all-link"><?php echo $args['linking_view_all_text']; ?></a>


        <?php } ?>


        <?php if( 4 < $args['linking_posts']->post_count && ! empty( $args['display_navigation'] ) ) { ?>


        <div class="directorist-linking-content__slider-navigation">


            <a href="" class="directorist-linking-content__slider-nav directorist-linking-content__slider-nav--prev"><?php directorist_icon('las la-angle-left' )?></a>
            <a href="" class="directorist-linking-content__slider-nav directorist-linking-content__slider-nav--next"><?php directorist_icon('las la-angle-right' )?></a>


        </div>


        <?php } ?>


    </div>
   
    <?php } ?>


</div><!-- ends: .directorist-linking-content -->


