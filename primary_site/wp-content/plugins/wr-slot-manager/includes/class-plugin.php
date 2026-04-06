<?php
/**
 * Main plugin orchestrator.
 *
 * @package WiseRabbit\SlotManager
 */

namespace WiseRabbit\SlotManager;

use WiseRabbit\SlotManager\PostType\SlotPostType;
use WiseRabbit\SlotManager\PostType\SlotMetaFields;
use WiseRabbit\SlotManager\Block\SlotFields\SlotFieldsBlock;
use WiseRabbit\SlotManager\Admin\AdminAssets;
use WiseRabbit\SlotManager\Admin\SettingsPage;
use WiseRabbit\SlotManager\Api\SlotsEndpoint;
use WiseRabbit\SlotManager\Cache\SlotCache;
use WiseRabbit\SlotManager\Hooks\SlotSaveHook;
use WiseRabbit\SlotManager\Hooks\SlotDeleteHook;
use WiseRabbit\SlotManager\Webhook\WebhookSender;

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
	 * Block frontend requests when theme is disabled (API/backend only mode).
	 * Admin, REST API, AJAX, and cron requests are allowed through.
	 *
	 * @return void
	 */
	public function block_frontend(): void {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		status_header( 200 );
		$time = gmdate( 'Y-m-d H:i:s' );
		echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>WiseRabbit API</title><style>*{margin:0;padding:0;box-sizing:border-box}body{background:#0a0a0a;color:#00ff41;font-family:"Courier New",monospace;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}.t{max-width:520px;width:100%;border:1px solid #00ff4122;padding:32px;position:relative;background:#0a0a0a;box-shadow:0 0 40px #00ff4108}.t::before{content:"";position:absolute;top:-1px;left:20px;right:20px;height:1px;background:linear-gradient(90deg,transparent,#00ff41,transparent)}.h{font-size:20px;letter-spacing:6px;text-transform:uppercase;margin-bottom:24px;text-shadow:0 0 10px #00ff4144}.r{color:#00ff4133;font-size:10px;line-height:1.15;margin-bottom:24px;white-space:pre}.l{color:#00ff4188;font-size:12px;line-height:2;border-top:1px solid #00ff4112;padding-top:16px}.g{color:#00ff41}.d{color:#00ff4144}.c::after{content:"_";animation:b 1s step-end infinite}@keyframes b{50%{opacity:0}}@media(max-width:480px){.t{padding:20px}.h{font-size:16px;letter-spacing:4px}}</style></head><body><div class="t"><div class="h">WiseRabbit API</div><pre class="r">  (\\(\n  ( -.-)  \n  o_(")(") </pre><div class="l"><span class="d">[' . esc_html( $time ) . ']</span> <span class="g">STATUS: ONLINE</span><br><span class="d">[' . esc_html( $time ) . ']</span> REST /wr-slot-manager/v1/slots<br><span class="d">[' . esc_html( $time ) . ']</span> POST /wr-slot-consumer/v1/webhook<br><span class="d">[' . esc_html( $time ) . ']</span> Awaiting requests<span class="c"></span></div></div></body></html>';
		exit;
	}

	/**
	 * Disable autosave in the block editor for slot posts.
	 *
	 * @param array                   $settings Editor settings.
	 * @param \WP_Block_Editor_Context $context  Editor context.
	 * @return array
	 */
	public function disable_slot_autosave( array $settings, \WP_Block_Editor_Context $context ): array {
		if ( isset( $context->post ) && 'slot' === $context->post->post_type ) {
			$settings['autosaveInterval'] = 0;
		}
		return $settings;
	}

	/**
	 * Initialize all plugin components.
	 *
	 * @return void
	 */
	private function init_components(): void {
		// Post type, meta fields, and editor block.
		$slot_post_type   = new SlotPostType();
		$slot_meta_fields = new SlotMetaFields();
		$slot_fields_block = new SlotFieldsBlock();

		add_action( 'init', array( $slot_post_type, 'register' ) );
		add_action( 'init', array( $slot_meta_fields, 'register' ) );
		add_action( 'init', array( $slot_fields_block, 'register' ) );

		// Disable autosave for slot post type.
		add_filter( 'block_editor_settings_all', array( $this, 'disable_slot_autosave' ), 10, 2 );

		// Disable frontend theme when WR_THEME_ENABLED is false.
		if ( defined( 'WR_THEME_ENABLED' ) && false === WR_THEME_ENABLED ) {
			add_action( 'template_redirect', array( $this, 'block_frontend' ) );
		}

		// Admin pages.
		$settings_page = new SettingsPage();
		$admin_assets  = new AdminAssets();

		add_action( 'admin_menu', array( $settings_page, 'register_page' ) );
		add_action( 'admin_enqueue_scripts', array( $admin_assets, 'enqueue' ) );

		// Admin form handlers (fallback for non-JS).
		add_action( 'admin_init', array( $settings_page, 'handle_sites_form' ) );
		add_action( 'admin_init', array( $settings_page, 'handle_api_form' ) );
		add_action( 'admin_init', array( $settings_page, 'handle_cache_form' ) );

		// AJAX handlers.
		add_action( 'wp_ajax_wr_sm_generate_api_key', array( $settings_page, 'handle_ajax_generate' ) );
		add_action( 'wp_ajax_wr_sm_sites_action', array( $settings_page, 'handle_ajax_sites' ) );
		add_action( 'wp_ajax_wr_sm_save_settings', array( $settings_page, 'handle_ajax_save' ) );

		// REST API.
		$slots_endpoint = new SlotsEndpoint();
		add_action( 'rest_api_init', array( $slots_endpoint, 'register_routes' ) );

		// Hooks for cache + webhook dispatch.
		$slot_cache     = new SlotCache();
		$slot_save_hook = new SlotSaveHook( $slot_cache );
		$slot_del_hook  = new SlotDeleteHook( $slot_cache );

		add_action( 'wp_after_insert_post', array( $slot_save_hook, 'handle' ), 20, 3 );
		add_action( 'wp_trash_post', array( $slot_del_hook, 'handle' ) );
		add_action( 'before_delete_post', array( $slot_del_hook, 'handle' ) );

		// Webhook cron sender.
		$webhook_sender = new WebhookSender();
		add_action( 'wr_sm_process_webhook_queue', array( $webhook_sender, 'process' ) );
	}
}
