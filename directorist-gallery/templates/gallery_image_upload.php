<?php
// prevent direct access to the file
defined('ABSPATH') || die('No direct script access allowed!');


wp_enqueue_script( 'directorist-ez-media-uploader' );

$enable_gallery         = get_directorist_option('enable_gallery', 1);
if ( $enable_gallery ) {
$p_id                   = ! empty( $field_data['form'] ) ? $field_data['form']->get_add_listing_id() : '';
$gallery_image          = $field_data['value'];

$limit                  = !empty( $field_data['max'] ) ? $field_data['max'] : $field_data['max_image_limit'];
$unlimited              = !empty( $field_data['unlimited'] ) ? $field_data['unlimited'] : '';
$max_file_size          = $field_data['max_per_image_limit'];
$max_total_file_size    = $field_data['max_total_image_limit'];
$max_file_size_kb       = (float) $max_file_size * 1024;//
$max_total_file_size_kb = (float) $max_total_file_size * 1024;//
$required               = $field_data['required'] ? '1' : '';


$img_upload_data = [
	'type'               => 'jpg, jpeg, png, gif',
	'max_num_of_img'     => $limit,
	'max_total_img_size' => $max_total_file_size_kb,
	'is_required'        => $required,
	'max_size_per_img'   => $max_file_size_kb,
];
$img_upload_data = json_encode( $img_upload_data );

?>
<div class="ez-media-uploader listing_gallery_ext" data-uploader="<?php echo esc_attr( $img_upload_data ); ?>">
    <div class="ezmu__loading-section ezmu--show">
        <span class="ezmu__loading-icon">
            <span class="ezmu__loading-icon-img-bg"></span>
        </span>
    </div>

    <div class="ezmu__old-files">
        <?php
        if (!empty($gallery_image)) {
            foreach ($gallery_image as $image) {
                $url = wp_get_attachment_image_url($image, 'full');
                $size = filesize(get_attached_file($image));
                ?>
                <span
                        class="ezmu__old-files-meta"
                        data-attachment-id="<?php echo esc_attr($image); ?>"
                        data-url="<?php echo esc_url($url); ?>"
                        data-size="<?php echo esc_attr($size / 1024); ?>"
                        data-type="image"
                ></span>
                <?php
            }
        }
        ?>
    </div>
    <div class="ezmu-dictionary">
        <!-- Label Texts -->
        <span class="ezmu-dictionary-label-featured"><?php echo __('Featured', 'directorist-gallery') ?></span>
        <span class="ezmu-dictionary-label-drag-n-drop"><?php echo __('Drag & Drop', 'directorist-gallery') ?></span>
        <span class="ezmu-dictionary-label-or"><?php echo __('or', 'directorist-gallery') ?></span>
        <span class="ezmu-dictionary-label-select-files"><?php echo $field_data['select_files_label'] ?></span>
        <span class="ezmu-dictionary-label-add-more"><?php echo __('Add More', 'directorist-gallery') ?></span>
        <!-- Alert Texts -->
        <span class="ezmu-dictionary-alert-max-total-file-size">
                                    <?php echo __('Max limit for total file size is __DT__', 'directorist-gallery') ?>
                                </span>
        <span class="ezmu-dictionary-alert-max-file-items">
                                    <?php echo __('Max limit for total file is __DT__', 'directorist-gallery') ?>
                                </span>

        <!-- Info Text -->
        <span class="ezmu-dictionary-info-max-total-file-size"><?php echo __('Maximum allowed file size is __DT__', 'directorist-gallery') ?></span>
        <span class="ezmu-dictionary-info-min-file-items" data-show='0'></span>
        <span class="ezmu-dictionary-info-type" data-show='0'></span>
        <span class="ezmu-dictionary-info-max-file-items"
                data-featured="<?php echo !empty($unlimited) ? '1' : ''; ?>">
                                    <?php echo !empty($unlimited) ? __('Unlimited images with this plan!', 'directorist-gallery') : ( ( $limit > 1 ) ? __('Maximum __DT__ files are allowed', 'directorist-gallery') : __('Maximum __DT__ file is allowed', 'directorist-gallery') ); ?></span>
    </div>
</div>

    <style>
        .directorist-listing-gallery-container{
            text-align: center;
            padding: 10px 0 15px;
        }
        .directorist-listing-gallery-container .directorist-listing-gallery-single {
            width: 208px;
            display: inline-block;
            position: relative;
        }

        .directorist-listing-gallery-container .directorist-listing-gallery-single .directorist-listing-gallery-single__remove {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #d3d1ec;
            line-height: 26px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            -webkit-transition: 0.2s;
            -moz-transition: 0.2s;
            -ms-transition: 0.2s;
            -o-transition: 0.2s;
            transition: 0.2s;
            cursor: pointer;
            color: #ffffff;
        }
    </style>
    <?php
}