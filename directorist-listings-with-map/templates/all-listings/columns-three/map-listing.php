<?php global $bdmv_listings; ?>
<div class="directorist-listing ">
    <?php
        bdmv_get_template( "view/listings/{$bdmv_listings->get_view()}" );
        do_action('bdmv-after-listing');
    ?>
</div>

<div class="directorist-map">
    <?php bdmv_get_template( 'view/map' ); ?>
</div>