<?php 
global $bdmv_listings; 
$type = $bdmv_listings->data['listings']->type;
$current_type = ! empty( $_POST['directory_type'] ) ? $_POST['directory_type'] :  $bdmv_listings->data['listings']->current_listing_type;;
$searchform = new \Directorist\Directorist_Listing_Search_Form( 'listing', $current_type );
?>
<div class=" search-area directorist-ad-search directorist-ads-form" data-nonce="<?php $bdmv_listings->get_the_nonce(); ?>" id="directorist-search-area">
    <form id="directorist-search-area-form">
        <?php 
        foreach ( $searchform->form_data[0]['fields'] as $field ){ ?>
        <div class="directorist-basic-search-fields-each"><?php $searchform->field_template( $field ); ?></div>
        <?php } ?>
        <div class="dlm-action-wrapper dlm-filter-slide dlm-filter-dropdown">
            <!-- .dlm-filter-slide / .dlm-filter-dropdown -->
            <button type="submit" class="directorist-btn directorist-btn-sm directorist-btn-dark"><?php _e('Search', 'directorist'); ?></button>
            <?php if ( $bdmv_listings->has_any_hidden_fields() ) { ?>
                <button type="button" class="directorist-btn directorist-btn-sm directorist-btn-outline-dark dlm_filter-btn"><?php _e('More Filters', 'directorist'); ?></button>
            <?php } ?>
        </div>
        <div class="dlm-filter-slide dlm-filter-slide-wrapper">
            <div class="directorist-more-filter-contents">
            <?php
            foreach ( $searchform->form_data[1]['fields'] as $field ){ ?>
                <div class="form-group directorist-search-field-<?php echo esc_attr( $field['widget_name'] )?>"><?php $searchform->field_template( $field ); ?></div>
            <?php } ?>
        <?php $searchform->buttons_template(); ?>
            </div>
        </div>
    </form>
</div><!-- ends: .directorist-ad-search -->
