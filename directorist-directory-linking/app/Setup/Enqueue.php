<?php
namespace SWBDP_DIRLINK\Setup;

class Enqueue {
	/**
	 * Register
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );
	}

	/**
	 * Load Frontend Scripts
	 *
	 * @return void
	 */
	public function load_frontend_scripts() {
		
			wp_enqueue_script( 'linking-admin-js',
			SWBDP_DIRLINK_PLUGIN_DIR_URI . 'admin/js/admin.js',
			array('jquery','directorist-select2-script'),
			SWBDP_DIRLINK_VERSION,
			);
		

		wp_enqueue_script('linking-main-js',
		 SWBDP_DIRLINK_PLUGIN_DIR_URI . 'assets/js/main.js',
		 array('jquery'),
		 SWBDP_DIRLINK_VERSION,
		 true
		);

		
			wp_enqueue_style('linking-main-css',
			SWBDP_DIRLINK_PLUGIN_DIR_URI . 'assets/css/style.css',
			SWBDP_DIRLINK_VERSION
			);
		
		wp_localize_script(
			'linking-admin-js',
			'DIRLINK',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'action'  => 'dirlink_searching_type',
				'action'  => 'dirlink_searching_type',
			)
		);
	}


	public function load_admin_scripts() {
		global $typenow;

		if( 'at_biz_dir' == $typenow ) {
			wp_enqueue_style('atbdp_setup_select2', DIRECTORIST_VENDOR_CSS . 'select2.min.css', ATBDP_VERSION, true);
			wp_enqueue_script('directorist-select2-script', DIRECTORIST_VENDOR_JS . 'select2.min.js', array('jquery'), ATBDP_VERSION, true);
			wp_enqueue_script('linking-admin-js', SWBDP_DIRLINK_PLUGIN_DIR_URI . 'admin/js/admin.js', array('directorist-select2-script'));

			wp_localize_script(
				'linking-admin-js',
				'DIRLINK',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'action'  => 'dirlink_searching_type',
				)
			);
		}
		
	}



	/**
	 * Register Scripts
	 *
	 * @return void
	 */
	public function register_scripts( array $scripts ) {
		foreach ( $scripts as $id => $args ) {

			if ( ! empty( $args['desable'] ) ) {
				continue;
			}

			$defaults = array(
				'src'       => '',
				'dep'       => array(),
				'ver'       => false,
				'in_footer' => true,
			);

			$args = array_merge( $defaults, $args );
			wp_register_script( $id, $args['src'], $args['dep'], $args['ver'], $args['in_footer'] );
		}
	}
}
