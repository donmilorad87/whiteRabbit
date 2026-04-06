<?php
/**
 * Admin asset enqueuing.
 *
 * @package WiseRabbit\SlotManager\Admin
 */

namespace WiseRabbit\SlotManager\Admin;

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
		$screen = get_current_screen();

		if ( ! $screen || 'slot' !== $screen->post_type ) {
			return;
		}

		// Disable autosave for slot posts.
		wp_dequeue_script( 'autosave' );

		// Only load admin JS on list/settings pages, not in the block editor.
		if ( $screen->is_block_editor() ) {
			return;
		}

		// Toastify vendor.
		wp_enqueue_style(
			'toastify',
			WR_SM_PLUGIN_URL . 'assets/admin/vendor/toastify.min.css',
			array(),
			'1.12.0'
		);

		wp_enqueue_script(
			'toastify',
			WR_SM_PLUGIN_URL . 'assets/admin/vendor/toastify.min.js',
			array(),
			'1.12.0',
			true
		);

		// Plugin admin assets.
		$css_file = WR_SM_PLUGIN_DIR . 'assets/admin/css/admin.css';
		$js_file  = WR_SM_PLUGIN_DIR . 'assets/admin/js/admin.js';

		if ( file_exists( $css_file ) ) {
			wp_enqueue_style(
				'wr-slot-manager-admin',
				WR_SM_PLUGIN_URL . 'assets/admin/css/admin.css',
				array( 'toastify' ),
				filemtime( $css_file )
			);
		}

		if ( file_exists( $js_file ) ) {
			wp_enqueue_script(
				'wr-slot-manager-admin',
				WR_SM_PLUGIN_URL . 'assets/admin/js/admin.js',
				array( 'toastify' ),
				filemtime( $js_file ),
				true
			);

			wp_localize_script(
				'wr-slot-manager-admin',
				'wrSmAdmin',
				array(
					'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
					'apiNonce'      => wp_create_nonce( 'wr_sm_api_key_save' ),
					'sitesNonce'    => wp_create_nonce( 'wr_sm_sites_save' ),
					'settingsNonce' => wp_create_nonce( 'wr_sm_settings_save' ),
				)
			);
		}
	}
}
