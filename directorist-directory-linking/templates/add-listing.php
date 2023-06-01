<?php
/**
 * @author  wpWax
 * @since   1.0
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

 $type =  ! empty( $args['field_data']['type'] ) ?  $args['field_data']['type'] : '';
?>

<div class="directorist-form-group directorist-form-link-type-field">

    <div class="directorist-form-label"><?php echo esc_html( $args['field_data']['label'] ); ?>:<?php echo !empty( $args['field_data']['required'] ) ? '<span class="directorist-form-required"> *</span>' : ''; ?></div>

	<select name="<?php echo $args['field_data']['field_key']; ?>[]" class="at_biz_dir-linking_type directorist-form-element" data-placeholder="<?php echo !empty( $args['field_data']['placeholder'] ) ? $args['field_data']['placeholder'] : ''; ?>" <?php echo ( 'multiple' == $type ) ? 'multiple' : ''; ?> <?php echo ! empty( $args['field_data']['required'] ) ? 'required' : ''; ?> data-type="<?php echo $args['type_id'];?>">

    <?php 
    if ( $type != 'multiple' ) { ?>
        <option value=""><?php echo !empty( $args['field_data']['placeholder'] ) ? $args['field_data']['placeholder'] : ''; ?></option>;

<?php } ?>

<?php if( $args['linking_posts'] ) { 
    // Get the saved value
    $selectedValue = maybe_unserialize($args['type_value']); 
    while( $args['linking_posts']->have_posts() ) : $args['linking_posts']->the_post(); ?>
        <option value="<?php echo get_the_ID(); ?>" <?php echo in_array( get_the_ID(), (array) $selectedValue ) ? "selected" : ''; ?>><?php echo get_the_title(); ?></option>
    <?php endwhile;
} ?>

	</select>

</div>