<?php

if ( ! defined( 'SWBDP_DIRLINK_LANG_PATH' ) ) {
	define( 'SWBDP_DIRLINK_LANG_PATH', dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
if ( ! defined( 'SWBDP_DIRLINK_PLUGIN_PATH' ) ) {
	define( 'SWBDP_DIRLINK_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'SWBDP_DIRLINK_PLUGIN_DIR_URI' ) ) {
	define( 'SWBDP_DIRLINK_PLUGIN_DIR_URI', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'SWBDP_DIRLINK_ASSET_URI' ) ) {
	define( 'SWBDP_DIRLINK_ASSET_URI', SWBDP_DIRLINK_PLUGIN_DIR_URI . 'assets/' );
}
if ( ! defined( 'SWBDP_DIRLINK_CSS_URI' ) ) {
	define( 'SWBDP_DIRLINK_CSS_URI', SWBDP_DIRLINK_ASSET_URI . 'css/' );
}
if ( ! defined( 'SWBDP_DIRLINK_JS_URI' ) ) {
	define( 'SWBDP_DIRLINK_JS_URI', SWBDP_DIRLINK_ASSET_URI . 'js/' );
}
if ( ! defined('SWBDP_DIRLINK_DIR') ) { 
	define('SWBDP_DIRLINK_DIR', SWBDP_DIRLINK_PLUGIN_PATH.'templates/');
 }
 if ( ! defined('SWBDP_DIRLINK_VERSION') ) { 
	define('SWBDP_DIRLINK_VERSION', "1.0.1");
 }