<?php
defined( 'ABSPATH' ) || exit;

use SWBDP_DIRLINK\Builder\Builder;
use SWBDP_DIRLINK\Setup\Settings;
use SWBDP_DIRLINK\Setup\Enqueue;


final class Directorist_Directory_Linking {

	/**
	 * Instance
	 *
	 * @return Directorist_Directory_Linking
	 * @since 1.0
	 */
	private static $instance;

	/**
	 * Get The Instance
	 *
	 * @return Directorist_Directory_Linking
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			// enable translation.
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
			add_filter( 'atbdp_listing_search_query_argument', array( self::$instance, 'search_query_argument') );

		}

		return self::$instance;
	}

	public function search_query_argument( $args ) {

		if( ! empty( $_GET['link-id'] ) ) {
			$args['post__in'] = json_decode( $_GET['link-id'] );
		}

		return $args;
	}

	/**
	 * It loads plugin text domain
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'directorist-authorize.net', false, SWBDP_DIRLINK_LANG_PATH );
	}

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->includes();
		$this->register_services();
	}

	/**
	 * File Includer
	 *
	 * @return void
	 */
	public function includes() {
		$files = array(
			'app/Builder/Builder',
			'app/Setup/Settings',
			'app/Setup/Enqueue',
		);

		foreach ( $files as $file ) {
			$file_path = SWBDP_DIRLINK_PLUGIN_PATH . "{$file}.php";
			if ( ! file_exists( $file_path ) ) {
				continue;
			}
			include $file_path;
		}
	}

	/**
	 * Get Services
	 *
	 * @return array
	 */
	public function get_services() {
		return array(
			Settings::class,
			Enqueue::class,
            Builder::class,
		);
	}

	/**
	 * Register Services
	 *
	 * @return void
	 */
	public function register_services() {
		$services = $this->get_services();

		if ( ! count( $services ) ) {
			return;
		}
		foreach ( $services as $class_name ) {
			if ( class_exists( $class_name ) ) {
				if ( method_exists( $class_name, 'register' ) ) {
					$service = new $class_name();
					$service->register();
				}
			}
		}
	}
}

function Directorist_Directory_Linking() {
	return Directorist_Directory_Linking::instance();
}

if ( ! function_exists( 'directorist_is_plugin_active' ) ) {
	function directorist_is_plugin_active( $plugin ) {
		return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) || directorist_is_plugin_active_for_network( $plugin );
	}
}

if ( ! function_exists( 'directorist_is_plugin_active_for_network' ) ) {
	function directorist_is_plugin_active_for_network( $plugin ) {
		if ( ! is_multisite() ) {
			return false;
		}
				
		$plugins = get_site_option( 'active_sitewide_plugins' );
		if ( isset( $plugins[ $plugin ] ) ) {
				return true;
		}

		return false;
	}
}

if (  directorist_is_plugin_active( 'directorist/directorist-base.php' ) ) {
	Directorist_Directory_Linking();
}
