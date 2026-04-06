<?php
/**
 * Uninstall handler for WR Slot Manager.
 *
 * Removes all plugin data: posts, options, cache.
 *
 * @package WiseRabbit\SlotManager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove all slot posts.
$slots = get_posts(
	array(
		'post_type'      => 'slot',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

$max = count( $slots );
for ( $i = 0; $i < $max; $i++ ) {
	wp_delete_post( $slots[ $i ], true );
}

// Remove all prefixed options.
global $wpdb;

$options = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->prepare(
		"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
		'wr\_sm\_%'
	)
);

$max = count( $options );
for ( $i = 0; $i < $max; $i++ ) {
	delete_option( $options[ $i ] );
}

// Flush object cache for our group.
wp_cache_delete( 'all_slots', 'wr_slot_manager' );
