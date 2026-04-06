<?php
/**
 * Unified settings admin page with tabs.
 * Combines: Connected Sites, API Settings, Cache Configuration.
 *
 * @package WiseRabbit\SlotManager\Admin
 */

namespace WiseRabbit\SlotManager\Admin;

use WiseRabbit\SlotManager\Traits\OptionPrefixTrait;
use WiseRabbit\SlotManager\Traits\NonceVerificationTrait;
use WiseRabbit\SlotManager\Traits\LoggerTrait;
use WiseRabbit\SlotManager\Traits\TemplateLoaderTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SettingsPage
 */
class SettingsPage {

	use OptionPrefixTrait;
	use NonceVerificationTrait;
	use LoggerTrait;
	use TemplateLoaderTrait;

	/**
	 * Register the submenu page.
	 */
	public function register_page(): void {
		add_submenu_page(
			'edit.php?post_type=slot',
			__( 'Settings', 'wr-slot-manager' ),
			__( 'Settings', 'wr-slot-manager' ),
			'manage_options',
			'wr-sm-settings',
			array( $this, 'render_page' )
		);
	}

	// ── Connected Sites ──

	/**
	 * Handle traditional connected sites form submission.
	 */
	public function handle_sites_form(): void {
		if ( ! isset( $_POST['wr_sm_sites_action'] ) || wp_doing_ajax() ) {
			return;
		}

		if ( ! $this->verify_nonce( 'wr_sm_sites_nonce', 'wr_sm_sites_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->process_sites_action();
	}

	/**
	 * Handle AJAX site operations.
	 */
	public function handle_ajax_sites(): void {
		if ( ! $this->verify_nonce( 'wr_sm_sites_nonce', 'wr_sm_sites_save' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'wr-slot-manager' ) ), 403 );
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'wr-slot-manager' ) ), 403 );
			return;
		}

		try {
			$this->process_sites_action();
			wp_send_json_success( array( 'message' => __( 'Site list updated.', 'wr-slot-manager' ) ) );
		} catch ( \Exception $e ) {
			$this->log_error( 'AJAX sites operation failed: ' . $e->getMessage() );
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Process the current POST action (add/edit/remove).
	 */
	private function process_sites_action(): void {
		$action = sanitize_text_field( wp_unslash( $_POST['wr_sm_sites_action'] ?? '' ) );
		$sites  = $this->get_sites();

		switch ( $action ) {
			case 'add':
				$url = $this->sanitize_site_url( $_POST['wr_sm_site_url'] ?? '' );
				if ( $url && ! in_array( $url, $sites, true ) ) {
					$sites[] = $url;
					$this->log_info( 'Connected site added: ' . $url );
				}
				break;

			case 'edit':
				$index   = isset( $_POST['wr_sm_site_index'] ) ? absint( $_POST['wr_sm_site_index'] ) : -1;
				$new_url = $this->sanitize_site_url( $_POST['wr_sm_site_url'] ?? '' );
				if ( $new_url && isset( $sites[ $index ] ) ) {
					$sites[ $index ] = $new_url;
					$this->log_info( 'Connected site updated at index ' . $index . ': ' . $new_url );
				}
				break;

			case 'remove':
				$index = isset( $_POST['wr_sm_site_index'] ) ? absint( $_POST['wr_sm_site_index'] ) : -1;
				if ( isset( $sites[ $index ] ) ) {
					$removed = $sites[ $index ];
					unset( $sites[ $index ] );
					$sites = array_values( $sites );
					$this->log_info( 'Connected site removed: ' . $removed );
				}
				break;
		}

		$this->update_option( 'connected_sites', $sites );
	}

	/**
	 * Get the list of connected site URLs.
	 */
	public function get_sites(): array {
		$sites = $this->get_option( 'connected_sites', array() );
		return is_array( $sites ) ? $sites : array();
	}

	/**
	 * Sanitize and validate a site URL.
	 */
	private function sanitize_site_url( string $url ): string|false {
		$url = esc_url_raw( wp_unslash( trim( $url ) ) );
		return ( ! empty( $url ) && filter_var( $url, FILTER_VALIDATE_URL ) ) ? untrailingslashit( $url ) : false;
	}

	// ── API Key ──

	/**
	 * Handle traditional API key form submission.
	 */
	public function handle_api_form(): void {
		if ( ! isset( $_POST['wr_sm_api_key_action'] ) || wp_doing_ajax() ) {
			return;
		}

		if ( ! $this->verify_nonce( 'wr_sm_api_key_nonce', 'wr_sm_api_key_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->generate_key();
	}

	/**
	 * Handle AJAX key generation.
	 */
	public function handle_ajax_generate(): void {
		if ( ! $this->verify_nonce( 'wr_sm_api_key_nonce', 'wr_sm_api_key_save' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'wr-slot-manager' ) ), 403 );
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'wr-slot-manager' ) ), 403 );
			return;
		}

		try {
			$new_key = $this->generate_key();
			wp_send_json_success( array(
				'message' => __( 'API key generated.', 'wr-slot-manager' ),
				'key'     => $new_key,
			) );
		} catch ( \Exception $e ) {
			$this->log_error( 'AJAX API key generation failed: ' . $e->getMessage() );
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Generate and store a new API key.
	 */
	private function generate_key(): string {
		$new_key = wp_generate_password( 32, false );
		$this->update_option( 'api_key', $new_key );
		$this->log_info( 'API key regenerated.' );
		return $new_key;
	}

	// ── Cache & Rate Limit ──

	/**
	 * Handle traditional cache settings form submission.
	 */
	public function handle_cache_form(): void {
		if ( ! isset( $_POST['wr_sm_cache_action'] ) || wp_doing_ajax() ) {
			return;
		}

		if ( ! $this->verify_nonce( 'wr_sm_settings_nonce', 'wr_sm_settings_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->save_cache_settings();
	}

	/**
	 * Handle AJAX cache settings save.
	 */
	public function handle_ajax_save(): void {
		if ( ! $this->verify_nonce( 'wr_sm_settings_nonce', 'wr_sm_settings_save' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'wr-slot-manager' ) ), 403 );
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'wr-slot-manager' ) ), 403 );
			return;
		}

		try {
			$this->save_cache_settings();
			wp_send_json_success( array( 'message' => __( 'Settings saved.', 'wr-slot-manager' ) ) );
		} catch ( \Exception $e ) {
			$this->log_error( 'Settings save failed: ' . $e->getMessage() );
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Save cache and rate limit settings from POST data.
	 */
	private function save_cache_settings(): void {
		if ( isset( $_POST['wr_sm_cache_expiry'] ) ) {
			$this->update_option( 'cache_expiry_minutes', max( 1, absint( wp_unslash( $_POST['wr_sm_cache_expiry'] ) ) ) );
		}

		if ( isset( $_POST['wr_sm_rate_limit'] ) ) {
			$this->update_option( 'rate_limit', max( 1, absint( wp_unslash( $_POST['wr_sm_rate_limit'] ) ) ) );
		}

		$this->log_info( 'Settings updated.' );
	}

	// ── Render ──

	/**
	 * Render the settings page with tabs.
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$sites        = $this->get_sites();
		$api_key      = $this->get_option( 'api_key', '' );
		$cache_expiry = $this->get_option( 'cache_expiry_minutes', 60 );
		$rate_limit   = $this->get_option( 'rate_limit', 10 );

		$this->load_template( 'templates/admin/settings.php', compact(
			'sites', 'api_key', 'cache_expiry', 'rate_limit'
		) );
	}
}
