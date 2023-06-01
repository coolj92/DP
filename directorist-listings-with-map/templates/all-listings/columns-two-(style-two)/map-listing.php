<?php global $bdmv_listings; ?>
    <div class="directorist-listing ">
        <?php
            bdmv_get_template( "view/listings/{$bdmv_listings->get_view()}" );
            do_action('bdmv-after-listing');
        ?>
    </div>
<?php wp_reset_query();?>
    <div class="directorist-map-right">
        <?php bdmv_get_template( 'view/map' ); ?>
    </div>
<?php wp_reset_query();?>
