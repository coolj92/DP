<?php
$gallery_images = (!empty($args['gallery_image'])) ? $args['gallery_image'] : array();
$gallery_images = get_post_meta( $args['field_data']['form']->add_listing_id, '_gallery_img', true );
$plan = (!empty($args['fm_plan'])) ? $args['fm_plan'] : '';
$image_links = []; // define a link placeholder variable
if( !empty( $gallery_images ) ){
    foreach ($gallery_images as $id) {
        $image_links[$id] = wp_get_attachment_image_src($id)[0]; // store the attachment id and url
    }
}
// is multiple image upload extension is active  ?
$active_mi_ext = is_multiple_images_active(); // default is no
?>

<div class="add_listing_form_wrapper" id="directorist-bdg-gallery-upload">

    <div class="form-group">
        <!-- image container, which can be manipulated with js -->
        <div class="directorist-listing-gallery-container">
            <?php if (!empty($image_links)) {
                foreach ($image_links as $id => $image_link) { ?>
                    <div class="directorist-listing-gallery-single">
                        <input class="directorist-listing-gallery-single__attatchment" name="gallery_img[]" type="hidden"
                               value="<?= intval($id); ?>">
                        <img style="width: 100%; height: 100%;"
                             src="<?= esc_url($image_link) ?>"
                             alt="<?php esc_attr_e('Listing Image', ATBDP_TEXTDOMAIN); ?>">
                        <span class="directorist-listing-gallery-single__remove  dashicons dashicons-dismiss"
                              title="<?= __('Remove it', ATBDP_TEXTDOMAIN); ?>"></span>
                    </div>
                <?php }  // ends foreach for looping image
            } else { ?>
                <img src="<?= esc_url(ATBDP_ADMIN_ASSETS . 'images/no-image.png'); ?>"
                     alt="<?php esc_attr_e('No Image Found', ATBDP_TEXTDOMAIN); ?>">
                <p>No Images</p>
            <?php } //  ends if statement  ?>
        </div>
        <?php
        /* A hidden input to set and post the chosen image id
        <input id="listing_image_id" name="listing[gallery_images]" type="hidden" value="">*/
        ?>
        <!--  add & remove image links -->
        <p class="directorist-hide-if-no-js">
            <a href="#" id="directorist-listing-gallery-btn" class="directorist-btn directorist-btn-primary directorist-btn-sm">
                <span class="dashicons dashicons-format-image"></span>
                <?php _e('Upload Gallery Images', ATBDP_TEXTDOMAIN); ?>
            </a>
            <a id="directorist-gallery-remove" class="directorist-btn directorist-btn-danger directorist-btn-sm <?= (!empty($image_links)) ? '' : 'hidden' ?>"
               href="#"> <?php echo (1 == $active_mi_ext) ? esc_html__('Remove Images') : esc_html__('Remove Image'); ?></a><br>
            <?php
            if (is_fee_manager_active()){
                $not_planned = get_post_meta($plan, 'atfm_listing_gallery', true);
                if (!empty($plan || $not_planned)){
                    $image_limit = get_post_meta($plan, 'num_gallery_image', true);
                    $image_unlimited = get_post_meta($plan, 'num_gallery_image_unl', true);
                    $is_plural = $image_limit>1?'s':'';
                    //is unlimited
                    echo '<div class="atbd_validate_note_img">';
                    echo '<div class="validation"></div>';
                    if ($image_unlimited){
                        ?>
                        <span class="atbdp_make_str_green atpp_limit__notice"><?php _e("Unlimited images with this plan!", ATBDP_TEXTDOMAIN);?></span>
                        <?php
                    }else{
                        ?>
                        <span class="atpp_limit__notice"><?php _e("You can upload $image_limit image$is_plural with this plan!", ATBDP_TEXTDOMAIN);?></span>
                        <?php
                    }
                    echo '</div>';
                }
            }
            ?>
        </p>
    </div>
</div> <!--ends add_listing_form_wrapper-->
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
        Height: 26px;
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