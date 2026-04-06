<?php
/**
 * Admin asset enqueuing.
 *
 * @package WiseRabbit\SlotConsumer\Admin
 */

namespace WiseRabbit\SlotConsumer\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AdminAssets
 */
class AdminAssets {

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public function enqueue( string $hook_suffix ): void {
		if ( 'toplevel_page_wr-sc-settings' !== $hook_suffix ) {
			return;
		}

		// Toastify vendor.
		wp_enqueue_style(
			'toastify',
			WR_SC_PLUGIN_URL . 'assets/admin/vendor/toastify.min.css',
			array(),
			'1.12.0'
		);

		wp_enqueue_script(
			'toastify',
			WR_SC_PLUGIN_URL . 'assets/admin/vendor/toastify.min.js',
			array(),
			'1.12.0',
			true
		);

		// Plugin admin assets.
		$css_file = WR_SC_PLUGIN_DIR . 'assets/admin/css/admin.css';
		$js_file  = WR_SC_PLUGIN_DIR . 'assets/admin/js/admin.js';

		if ( file_exists( $css_file ) ) {
			wp_enqueue_style(
				'wr-slot-consumer-admin',
				WR_SC_PLUGIN_URL . 'assets/admin/css/admin.css',
				array( 'toastify' ),
				filemtime( $css_file )
			);
		}

		if ( file_exists( $js_file ) ) {
			wp_enqueue_script(
				'wr-slot-consumer-admin',
				WR_SC_PLUGIN_URL . 'assets/admin/js/admin.js',
				array( 'toastify' ),
				filemtime( $js_file ),
				true
			);

			wp_localize_script(
				'wr-slot-consumer-admin',
				'wrScAdmin',
				array(
					'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
					'syncNonce'     => wp_create_nonce( 'wr_sc_sync_nonce' ),
					'settingsNonce' => wp_create_nonce( 'wr_sc_settings_save' ),
				)
			);
		}
	}
}
