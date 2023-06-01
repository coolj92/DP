<?php
// prevent direct access to the file
defined('ABSPATH') || die('No direct script access allowed!');
$enable_gallery = get_directorist_option('enable_gallery', 1);
$gallery_cropping = get_directorist_option('gallery_cropping_ex', 1);
$gallery_image_width = get_directorist_option('gallery_image_width_ex', 251);
$gallery_image_height = get_directorist_option('gallery_image_height_ex', 200);
$gallery_img = get_post_meta(get_the_ID(), '_gallery_img', true);
$gallery_imgs = (!empty($field_data['value'])) ? $field_data['value'] : array();
$image_links = array(); // define a link placeholder variable
$image_links_full = array(); //full size image for lighthouse
$select_columns = get_directorist_option('select_column', 'directorist-col-md-4');
foreach ($gallery_imgs as $id) {

    if (!empty($gallery_cropping)) {
        $image_links[$id] = atbdp_image_cropping($id, $gallery_image_width, $gallery_image_height, true, 100)['url'];
    } else {
        $image_links[$id] = wp_get_attachment_image_src($id, 'large')[0];
    }
    $image_links_full[$id] = wp_get_attachment_image_src($id, 'full')[0];
}
if ($enable_gallery && $image_links) {
    ?>
<div class="directorist-gallery-grid-two directorist-row">
    <?php if ($image_links) {
        foreach ($image_links as $index => $image_link) {
            ?>
            <div class="directorist-grid-item <?php echo $select_columns; ?>">
                <figure>
                    <img src="<?php echo !empty($image_link) ? esc_url($image_link) : ''; ?>"
                            alt="<?php esc_attr_e('Details Image', BDG_TEXTDOMAIN); ?>"
                            class="img-flusid">
                    <figcaption><a
                                href="<?php echo !empty($image_links_full[$index]) ? esc_url($image_links_full[$index]) : ''; ?>"><?php directorist_icon( 'fas fa-search-plus' ); ?></a>
                    </figcaption>
                </figure>
            </div><!-- ends: .directorist-grid-item -->
        <?php }
    } ?>
</div>
<?php
}