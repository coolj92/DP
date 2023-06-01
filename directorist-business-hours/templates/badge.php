<?php
// prevent direct access to the file
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

$bdbh 			= get_post_meta( get_the_ID(), '_bdbh', true);
$enable247hour 	= get_post_meta( get_the_ID(), '_enable247hour', true);
$disable_hours  = get_post_meta( get_the_ID(), '_disable_bz_hour_listing', true );
$business_hours = !empty($bdbh) ? atbdp_sanitize_array($bdbh) : array();

if( (!is_empty_v($business_hours) || !empty($enable247hour)) && ! $disable_hours ) {

?>
<div class="atbd_upper_badge directorist_open_status_badge" id="directorist_open_status_badge-<?php echo esc_attr( get_the_ID() ); ?>" data-listing_id="<?php echo esc_attr( get_the_ID() ); ?>">
	<?php if( ! directorist_hours_cache_plugin_compatibility() ) {
	directorist_show_open_close_badge( get_the_ID() );
} ?>
</div>
<?php 
}