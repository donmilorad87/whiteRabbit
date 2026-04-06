<?php
/**
 * Plugin activation handler.
 *
 * Runs once when the plugin is activated via the WordPress admin.
 * Use this for one-time setup: creating database tables, setting default options,
 * flushing rewrite rules, scheduling cron events, etc.
 *
 * @package WiseRabbit\SlotManager
 */

namespace WiseRabbit\SlotManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activation callback.
 *
 * @return void
 */
function activate() {
	$post_type = new PostType\SlotPostType();
	$post_type->register();
	flush_rewrite_rules();
}
