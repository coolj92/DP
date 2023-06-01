<?php 
/**
 * Template Loader
 *
 * @since 1.0
 */
function swbdp_dirlink_load_template( $template_file, $args = array() ) {
    if ( is_array( $args ) ) {
        extract( $args );
    }

    $theme_template  = '/directorist-directory-linking/' . $template_file . '.php';
    $plugin_template = SWBDP_DIRLINK_DIR . $template_file . '.php';

    if ( file_exists( get_stylesheet_directory() . $theme_template ) ) {
        $file = get_stylesheet_directory() . $theme_template;
    } elseif ( file_exists( get_template_directory() . $theme_template ) ) {
        $file = get_template_directory() . $theme_template;
    } else {
        $file = $plugin_template;
    }


    if ( file_exists( $file ) ) {
        include $file;
    }
}