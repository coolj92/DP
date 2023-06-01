<?php
// Plugin version.
if ( ! defined( 'FAQS_VERSION' ) ) {define( 'FAQS_VERSION', $version );}
// Plugin Folder Path.
if ( ! defined( 'FAQS_DIR' ) ) { define( 'FAQS_DIR', plugin_dir_path( FAQS_FILE ) ); }
// Plugin Folder URL.
if ( ! defined( 'FAQS_URL' ) ) { define( 'FAQS_URL', plugin_dir_url( FAQS_FILE ) ); }
// Plugin Root File.
if ( ! defined( 'FAQS_BASE' ) ) { define( 'FAQS_BASE', plugin_basename( FAQS_FILE ) ); }
// Plugin Includes Path
if ( !defined('FAQS_INC_DIR') ) { define('FAQS_INC_DIR', FAQS_DIR.'inc/'); }
// Plugin Assets Path
if ( !defined('FAQS_ASSETS') ) { define('FAQS_ASSETS', FAQS_URL.'assets/'); }
// Plugin Template Path
if ( !defined('FAQS_TEMPLATES_DIR') ) { define('FAQS_TEMPLATES_DIR', FAQS_DIR.'templates/'); }
// Plugin Language File Path
if ( !defined('FAQS_LANG_DIR') ) { define('FAQS_LANG_DIR', dirname(plugin_basename( FAQS_FILE ) ) . '/languages'); }
// Plugin Name
if ( !defined('FAQS_NAME') ) { define('FAQS_NAME', 'Directorist - FAQs'); }

// Plugin Alert Message
if ( !defined('FAQS_ALERT_MSG') ) { define('FAQS_ALERT_MSG', __('You do not have the right to access this file directly', 'directorist-faqs')); }

// plugin author url
if (!defined('ATBDP_AUTHOR_URL')) {
    define('ATBDP_AUTHOR_URL', 'https://directorist.com');
}
// post id from download post type (edd)
if (!defined('ATBDP_FAQS_POST_ID')) {
    define('ATBDP_FAQS_POST_ID', 13780 );
}
