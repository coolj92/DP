<?php
// Plugin version.
if ( ! defined( 'BDBH_VERSION' ) ) {define( 'BDBH_VERSION', bdbh_get_version_from_file_content( BDBH_FILE ) );}
// Plugin Folder Path.
if ( ! defined( 'BDBH_DIR' ) ) { define( 'BDBH_DIR', plugin_dir_path( BDBH_FILE ) ); }
// Plugin Folder URL.
if ( ! defined( 'BDBH_URL' ) ) { define( 'BDBH_URL', plugin_dir_url( BDBH_FILE ) ); }
// Plugin Root File.
if ( ! defined( 'BDBH_BASE' ) ) { define( 'BDBH_BASE', plugin_basename( BDBH_FILE ) ); }
// Plugin Includes Path
if ( !defined('BDBH_INC_DIR') ) { define('BDBH_INC_DIR', BDBH_DIR.'inc/'); }
// Plugin Assets Path
if ( !defined('BDBH_ASSETS') ) { define('BDBH_ASSETS', BDBH_URL.'assets/'); }
// Plugin Template Path
if ( !defined('BDBH_TEMPLATES_DIR') ) { define('BDBH_TEMPLATES_DIR', BDBH_DIR.'templates/'); }
// Plugin Language File Path
if ( !defined('BDBH_LANG_DIR') ) { define('BDBH_LANG_DIR', dirname(plugin_basename( BDBH_FILE ) ) . '/languages'); }
// Plugin Name
if ( !defined('BDBH_NAME') ) { define('BDBH_NAME', 'Directorist - Business Hour'); }

// Plugin Alert Message
if ( !defined('BDBH_ALERT_MSG') ) { define('BDBH_ALERT_MSG', __('You do not have the right to access this file directly', 'directorist-business-hours')); }

// plugin author url
if (!defined('ATBDP_AUTHOR_URL')) {
    define('ATBDP_AUTHOR_URL', 'https://directorist.com');
}
// post id from download post type (edd)
if (!defined('ATBDP_BUSINESS_HOURS_POST_ID')) {
    define('ATBDP_BUSINESS_HOURS_POST_ID', 13714 );
}
