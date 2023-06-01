<?php global $bdmv_listings;

/**
 * @package Directorist
 * @since 5.5.1
 * @param $query_results
 */
do_action('atbdp_before_all_listings_grid', $bdmv_listings->data['query_results'] ); ?>

<div id="directorist" class="directorist-wrapper ads-advaced--wrapper">

    <?php bdmv_get_template( 'view/listings/header' ); ?>

    <div class="<?php echo $bdmv_listings->grid_container_class(); ?>">
        <?php
        /**
         * @since 5.0
         * It fires before the listings columns
         * It only fires if the parameter [directorist_all_listing action_before_after_loop="yes"]
         */
        if ( $bdmv_listings->get_boolean( 'action_before_after_loop' ) ) {
            do_action('bdm_before_grid_listings_loop');
        }

        $row_class = 'directorist-row';
        ob_start();
        Directorist\Helper::directorist_row();

        $row_class = ob_get_clean();
        ?>
        <div class="<?php echo $row_class ?>">
            <?php foreach ( $bdmv_listings->data['listings']->post_ids() as $listing_id ): ?>
                <div class="<?php Directorist\Helper::directorist_column( 6 ); ?>">
                    <?php $bdmv_listings->data['listings']->loop_template( 'grid', $listing_id ); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <!--end row-->

        <div class="directorist-row">
            <div class="col-lg-12">
                <?php
                /**
                 * @since 5.0
                 */
                do_action('atbdp_before_listings_pagination');
                if ( $bdmv_listings->get_boolean( 'show_pagination' ) ) {
                    echo bdmv_load_more_filter( $bdmv_listings->data['query_results'], $bdmv_listings->data['paged'], $bdmv_listings->data['defSquery'] );
                } ?>
            </div>
        </div>

        <?php
        /**
         * @since 5.0
         * to add custom html
         * It only fires if the parameter [directorist_all_listing action_before_after_loop="yes"]
         */
        if ( $bdmv_listings->get_boolean( 'action_before_after_loop' ) ) {
            do_action('bdm_after_grid_listings_loop');
        }
        ?>
    </div>
</div>
<style>
    .atbd_content_active #directorist.directorist-wrapper .atbdp_column {
        width: <?php echo $bdmv_listings->data['column_width']; ?>;
    }
</style>