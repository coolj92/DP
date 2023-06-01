<?php
// prevent direct access to the file
defined('ABSPATH') || die('No direct script access allowed!');
$listing_id = ! empty( $field_data[ 'listing_id' ] ) ? $field_data['listing_id'] : '';
$text247 = get_directorist_option('text247', __('Open 24/7', 'directorist-business-hours')); // text for 24/7 type listing
$business_hour_title = get_directorist_option('business_hour_title', __('Business Hour', 'directorist-business-hours')); // text Business Hour Title
$atbh_display_single_listing = get_directorist_option('atbh_display_single_listing', 1);
$bdbh = get_post_meta($listing_id, '_bdbh', true);
$enable247hour = get_post_meta($listing_id, '_enable247hour', true);
$disable_bz_hour_listing = get_post_meta($listing_id, '_disable_bz_hour_listing', true);
$business_hours = !empty($bdbh) ? atbdp_sanitize_array($bdbh) : array(); // arrays of days and times if exist

// if business hour is active then add the following markup...

if ( !empty($atbh_display_single_listing) && empty($disable_bz_hour_listing) && (!is_empty_v($business_hours) || !empty($enable247hour))) {
?>

<div class="atbd_content_module directorist-business-hour-module">
    <div class="atbd_content_module__tittle_area">
        <div class="atbd_area_title">
            <h4>
            <span class="<?php echo $field_data['icon']; ?>"></span><?php echo esc_html($business_hour_title); ?>
            </h4>
        </div>
            <div class="atbd_upper_badge directorist_open_status_badge" data-listing_id="<?php echo esc_attr( get_the_ID() ); ?>">
                <?php if( ! directorist_hours_cache_plugin_compatibility() ) {
                    directorist_show_open_close_badge();
                } ?>
            </div>
    </div>

    <div class="atbdb_content_module_contents">
        <div class="directorist-open-hours" data-listing_id="<?php echo esc_attr( get_the_ID() ); ?>">
        <?php
        if( ! directorist_hours_cache_plugin_compatibility() ){
            if (!empty($enable247hour)) {
                echo '<p>'. esc_html($text247) . '</p>';
            } else {
                show_business_hours(); // show the business hour in an unordered list.
            }
        }
        ?>
        </div>
    </div>
</div>
<?php
}