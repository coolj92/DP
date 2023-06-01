<?php
// Plugin version.
if ( ! defined( 'BDG_VERSION' ) ) {define( 'BDG_VERSION', $version );}
// Plugin Folder Path.
if ( ! defined( 'BDG_DIR' ) ) { define( 'BDG_DIR', plugin_dir_path( BDG_FILE ) ); }
// Plugin Folder URL.
if ( ! defined( 'BDG_URL' ) ) { define( 'BDG_URL', plugin_dir_url( BDG_FILE ) ); }
// Plugin Root File.
if ( ! defined( 'BDG_BASE' ) ) { define( 'BDG_BASE', plugin_basename( BDG_FILE ) ); }
// Plugin Text domain File.
if ( ! defined( 'BDG_TEXTDOMAIN' ) ) { define( 'BDG_TEXTDOMAIN', 'directorist-gallery' ); }
// Plugin Includes Path
if ( !defined('BDG_INC_DIR') ) { define('BDG_INC_DIR', BDG_DIR.'inc/'); }
// Plugin Assets Path
if ( !defined('BDG_ASSETS') ) { define('BDG_ASSETS', BDG_URL.'assets/'); }
if ( !defined('BDG_ADMIN_ASSETS') ) { define('BDG_ADMIN_ASSETS', BDG_URL.'admin/assets/'); }
if ( !defined('BDG_FONT_ASSETS') ) { define('BDG_FONT_ASSETS', BDG_URL.'public/assets/'); }
// Plugin Template Path
if ( !defined('BDG_TEMPLATES_DIR') ) { define('BDG_TEMPLATES_DIR', BDG_DIR.'templates/'); }
// Plugin Language File Path
if ( !defined('BDG_LANG_DIR') ) { define('BDG_LANG_DIR', dirname(plugin_basename( BDG_FILE ) ) . '/languages'); }
// plugin author url
if (!defined('ATBDP_AUTHOR_URL')) {
    define('ATBDP_AUTHOR_URL', 'https://directorist.com');
}
// post id from download post type (edd)
if (!defined('ATBDP_GALLERY_POST_ID')) {
    define('ATBDP_GALLERY_POST_ID', 13778 );
}

