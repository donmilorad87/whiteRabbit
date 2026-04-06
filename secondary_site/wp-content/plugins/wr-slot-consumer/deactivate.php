<?php
/**
 * Plugin deactivation handler.
 *
 * Runs once when the plugin is deactivated via the WordPress admin.
 * Use this for temporary cleanup: unscheduling cron events, flushing rewrite rules.
 * Do NOT delete data here — that belongs in uninstall.php.
 *
 * @package WiseRabbit\SlotConsumer
 */

namespace WiseRabbit\SlotConsumer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivation callback.
 *
 * @return void
 */
function deactivate(): void {
	// Placeholder for future deactivation logic.
}
