<?php
/**
 * @author  wpWax
 * @since   1.3.0
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! get_directorist_option( 'enable_faqs', 1 ) ) {
	return;
}
?>

<div class="directorist-form-group directorist-faq-group">

	<?php if ( ! empty( $field_data['label'] ) ) { ?>

		<label class="directorist-form-label">

			<?php echo esc_attr( $field_data['label'] ) . ':'; ?>

		</label>

	<?php } ?>

	<?php
	$faqs = ! empty( $field_data['value'] ) ? $field_data['value'] : array();

	Listings_fAQs()->load_template( 'add-faq', array( 'listing_faq' => $faqs ) );
	?>

</div>
