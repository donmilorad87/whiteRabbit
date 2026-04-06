<?php
/**
 * Plugin activation handler.
 *
 * Creates a test page with the slot grid block for e2e testing.
 *
 * @package WiseRabbit\SlotConsumer
 */

namespace WiseRabbit\SlotConsumer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activation callback.
 *
 * @return void
 */
function activate(): void {
	$existing = get_page_by_path( 'test-page' );

	if ( ! $existing ) {
		$result = wp_insert_post( array(
			'post_title'   => 'Test Page',
			'post_name'    => 'test-page',
			'post_content' => '<!-- wp:wr-slot-consumer/slot-grid {"paginationMode":"pagination","limit":3,"linkMode":"popup"} /-->',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		), true );

		if ( is_wp_error( $result ) ) {
			error_log( '[wr-slot-consumer] Failed to create test page: ' . $result->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
