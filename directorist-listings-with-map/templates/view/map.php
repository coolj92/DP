<?php global $bdmv_listings;
/**
 * This template displays the Directorist listings in map view.
 */
?>
<div id="directorist" class="directorist-wrapper">
    <div class="directorist-divider"></div>
    <div class="<?php $bdmv_listings->get_the_data( 'map_container' ); ?>">
    <?php $bdmv_listings->data['listings']->render_map(); ?>
    </div>
</div>



