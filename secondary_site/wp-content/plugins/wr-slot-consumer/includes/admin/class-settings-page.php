<?php
/**
 * Settings admin page.
 *
 * @package WiseRabbit\SlotConsumer\Admin
 */

namespace WiseRabbit\SlotConsumer\Admin;

use WiseRabbit\SlotConsumer\Traits\OptionPrefixTrait;
use WiseRabbit\SlotConsumer\Traits\LoggerTrait;
use WiseRabbit\SlotConsumer\Traits\TemplateLoaderTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SettingsPage
 */
class SettingsPage {

	use OptionPrefixTrait;
	use LoggerTrait;
	use TemplateLoaderTrait;

	/**
	 * Register the admin menu page.
	 *
	 * @return void
	 */
	public function register_page(): void {
		add_menu_page(
			__( 'Slot Consumer', 'wr-slot-consumer' ),
			__( 'Slot Consumer', 'wr-slot-consumer' ),
			'manage_options',
			'wr-sc-settings',
			array( $this, 'render_page' ),
			'dashicons-download',
			25
		);

		// Submenus point to the same page — JS switches tabs via hash.
		$tabs = array(
			'connection' => __( 'Connection', 'wr-slot-consumer' ),
			'api'        => __( 'API Settings', 'wr-slot-consumer' ),
			'cache'      => __( 'Cache', 'wr-slot-consumer' ),
			'sync'       => __( 'Manual Sync', 'wr-slot-consumer' ),
		);

		foreach ( $tabs as $slug => $label ) {
			add_submenu_page(
				'wr-sc-settings',
				$label,
				$label,
				'manage_options',
				'wr-sc-settings#' . $slug,
				array( $this, 'render_page' )
			);
		}

		// Remove the auto-generated duplicate first submenu.
		global $submenu;
		if ( isset( $submenu['wr-sc-settings'] ) ) {
			unset( $submenu['wr-sc-settings'][0] );
		}
	}

	/**
	 * Handle traditional form submission (fallback if JS fails).
	 *
	 * @return void
	 */
	public function handle_form(): void {
		if ( ! isset( $_POST['wr_sc_settings_action'] ) ) {
			return;
		}

		if ( wp_doing_ajax() ) {
			return;
		}

		if ( ! $this->verify_settings_nonce() ) {
			return;
		}

		$this->save_settings();

		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'wr-slot-consumer' ) . '</p></div>';
			}
		);
	}

	/**
	 * Handle AJAX settings save.
	 *
	 * @return void
	 */
	public function handle_ajax_save(): void {
		if ( ! $this->verify_settings_nonce() ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'wr-slot-consumer' ) ), 403 );
			return;
		}

		try {
			$this->save_settings();
			wp_send_json_success( array( 'message' => __( 'Settings saved.', 'wr-slot-consumer' ) ) );
		} catch ( \Exception $e ) {
			$this->log_error( 'AJAX settings save failed: ' . $e->getMessage() );
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Verify the settings form nonce.
	 *
	 * @return bool
	 */
	private function verify_settings_nonce(): bool {
		if ( ! isset( $_POST['wr_sc_settings_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wr_sc_settings_nonce'] ) ), 'wr_sc_settings_save' )
		) {
			return false;
		}

		return current_user_can( 'manage_options' );
	}

	/**
	 * Save settings from POST data.
	 *
	 * @return void
	 */
	private function save_settings(): void {
		if ( isset( $_POST['wr_sc_source_url'] ) ) {
			$this->update_option( 'source_url', untrailingslashit( esc_url_raw( wp_unslash( trim( $_POST['wr_sc_source_url'] ) ) ) ) );
		}

		if ( isset( $_POST['wr_sc_api_key'] ) ) {
			$this->update_option( 'api_key', sanitize_text_field( wp_unslash( $_POST['wr_sc_api_key'] ) ) );
		}

		if ( isset( $_POST['wr_sc_cache_expiry'] ) ) {
			$this->update_option( 'cache_expiry_minutes', max( 1, absint( wp_unslash( $_POST['wr_sc_cache_expiry'] ) ) ) );
		}

		if ( isset( $_POST['wr_sc_rate_limit'] ) ) {
			$this->update_option( 'rate_limit', max( 1, absint( wp_unslash( $_POST['wr_sc_rate_limit'] ) ) ) );
		}

		$this->log_info( 'Settings updated.' );
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$source_url    = $this->get_option( 'source_url', '' );
		$api_key       = $this->get_option( 'api_key', '' );
		$cache_expiry  = $this->get_option( 'cache_expiry_minutes', 60 );
		$rate_limit    = $this->get_option( 'rate_limit', 10 );

		$this->load_template( 'templates/admin/settings.php', compact( 'source_url', 'api_key', 'cache_expiry', 'rate_limit' ) );
	}
}
