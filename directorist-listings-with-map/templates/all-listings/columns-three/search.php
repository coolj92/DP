<?php 
    global $bdmv_listings; 
    $type = $bdmv_listings->data['listings']->type;
    $current_type = ! empty( $_POST['directory_type'] ) ? $_POST['directory_type'] :  $bdmv_listings->data['listings']->current_listing_type;
?>

<div class="search-area directorist-ad-search directorist-ads-form" data-nonce="<?php $bdmv_listings->get_the_nonce(); ?>" id="directorist-search-area">
    <form id="directorist-search-area-form">
    <?php
    $searchform = new \Directorist\Directorist_Listing_Search_Form( 'listing', $current_type );
    if( $searchform->form_data[0]['fields'] ) {
        foreach ( $searchform->form_data[0]['fields'] as $field ){ ?>
        <div class="directorist-basic-search-fields-each"><?php $searchform->field_template( $field ); ?></div>
        <?php } } ?>
        <?php
        if( $searchform->form_data[1]['fields'] ) {
        foreach ( $searchform->form_data[1]['fields'] as $field ){ ?>
                <div class="form-group directorist-search-field-<?php echo esc_attr( $field['widget_name'] )?>"><?php $searchform->field_template( $field ); ?></div>
        <?php } } ?>
        <?php
        
        $searchform->buttons_template(); ?>
    </form>
</div><!-- ends: .directorist-ad-search -->