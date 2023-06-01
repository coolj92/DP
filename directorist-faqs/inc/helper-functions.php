<?php
// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if (!function_exists('atbdp_get_option')){

    /**
     * It retrieves an option from the database if it exists and returns false if it is not exist.
     * It is a custom function to get the data of custom setting page
     * @param string $name The name of the option we would like to get. Eg. map_api_key
     * @param string $group The name of the group where the option is saved. eg. general_settings
     * @param mixed $default    Default value for the option key if the option does not have value then default will be returned
     * @return mixed    It returns the value of the $name option if it exists in the option $group in the database, false otherwise.
     */
    function atbdp_get_option($name, $group, $default=false){
        // at first get the group of options from the database.
        // then check if the data exists in the array and if it exists then return it
        // if not, then return false
        if (empty($name) || empty($group)) {
            if (!empty($default)) return $default;
            return false;
        } // vail if either $name or option $group is empty
        $options_array = (array) get_option($group);
        if (array_key_exists($name, $options_array)) {
            return $options_array[$name];
        }else{
            if (!empty($default)) return $default;
            return false;
        }
    }
}




if (!function_exists('atbdp_sanitize_array')){
    /**
     * It sanitize a multi-dimensional array
     * @param array &$array The array of the data to sanitize
     * @return mixed
     */
    function atbdp_sanitize_array(&$array ) {

        foreach ($array as &$value) {

            if( !is_array($value) ) {

                // sanitize if value is not an array
                $value = sanitize_text_field($value);

            }else {

                // go inside this function again
                atbdp_sanitize_array($value);
            }

        }

        return $array;

    }
}


// atpp_get_template
function dfaqs_get_template( $template_file, $args = array() ) {
    if ( is_array( $args ) ) {
        extract( $args );
    }

    $theme_template  = '/directorist-faqs/' . $template_file . '.php';
    $plugin_template = FAQS_TEMPLATES_DIR . $template_file . '.php';

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