<?php
/**
 * Main plugin orchestrator.
 *
 * @package WiseRabbit\SlotConsumer
 */

namespace WiseRabbit\SlotConsumer;

use WiseRabbit\SlotConsumer\Admin\SettingsPage;
use WiseRabbit\SlotConsumer\Admin\AdminAssets;
use WiseRabbit\SlotConsumer\Api\WebhookEndpoint;
use WiseRabbit\SlotConsumer\Sync\SlotSyncManager;
use WiseRabbit\SlotConsumer\Block\SlotGridBlock\SlotGridBlock;
use WiseRabbit\SlotConsumer\Block\SlotDetailBlock\SlotDetailBlock;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Plugin
 */
class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): static {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor. Registers all hooks.
	 */
	private function __construct() {
		$this->init_components();
	}

	/**
	 * Initialize all plugin components.
	 *
	 * @return void
	 */
	private function init_components(): void {
		// Admin.
		$settings_page = new SettingsPage();
		$admin_assets  = new AdminAssets();

		add_action( 'admin_menu', array( $settings_page, 'register_page' ) );
		add_action( 'admin_init', array( $settings_page, 'handle_form' ) );
		add_action( 'admin_enqueue_scripts', array( $admin_assets, 'enqueue' ) );

		// AJAX handlers.
		$sync_manager = new SlotSyncManager();
		add_action( 'wp_ajax_wr_sc_sync_data', array( $sync_manager, 'handle_ajax_sync' ) );
		add_action( 'wp_ajax_wr_sc_save_settings', array( $settings_page, 'handle_ajax_save' ) );

		// REST API webhook receiver.
		$webhook_endpoint = new WebhookEndpoint();
		add_action( 'rest_api_init', array( $webhook_endpoint, 'register_routes' ) );

		// Gutenberg blocks.
		$slot_grid_block   = new SlotGridBlock();
		$slot_detail_block = new SlotDetailBlock();
		add_action( 'init', array( $slot_grid_block, 'register' ) );
		add_action( 'init', array( $slot_detail_block, 'register' ) );
		add_action( 'rest_api_init', array( $slot_detail_block, 'register_rest_routes' ) );
	}
}
