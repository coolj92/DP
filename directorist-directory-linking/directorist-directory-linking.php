<?php
/**
 * Directorist - Directory Linking
 *
 * @package           Directorist_Directory_Linking
 * @author            wpwax
 * @copyright         2019 wpwax or Company Name
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Directorist - Directory Linking
 * Plugin URI:        https://directorist.com/product/directorist-directory-linking/
 * Description:       Directory type linking extension for directorist
 * Version:           1.0.3
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            wpwax
 * Author URI:        https://wpwax.com/
 * Text Domain:       directorist-directory-linking
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */


defined( 'ABSPATH' ) || exit;

// Setup The Consts.
if ( ! defined( 'SWBDP_DIRLINK_PLUGIN_FILE' ) ) {
	define( 'SWBDP_DIRLINK_PLUGIN_FILE', __FILE__ );
}

$const_file = plugin_dir_path( __FILE__ ) . '/const.php';
require $const_file;

// Include The Helpers.
$helpers_file = SWBDP_DIRLINK_PLUGIN_PATH . 'helpers/helpers.php';
require $helpers_file;

// Include The App.
$app = SWBDP_DIRLINK_PLUGIN_PATH . 'app/base.php';
if ( ! class_exists( 'Directorist_Directory_Linking' ) ) {
	include $app;
}
