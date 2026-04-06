<?php
/**
 * Uninstall handler for WR Slot Consumer.
 *
 * Removes all plugin data: options, transients.
 *
 * @package WiseRabbit\SlotConsumer
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove all prefixed options.
global $wpdb;

$options = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->prepare(
		"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
		'wr\_sc\_%'
	)
);

$max = count( $options );
for ( $i = 0; $i < $max; $i++ ) {
	delete_option( $options[ $i ] );
}

// Remove transient cache.
delete_transient( 'wr_sc_slots_cache' );
