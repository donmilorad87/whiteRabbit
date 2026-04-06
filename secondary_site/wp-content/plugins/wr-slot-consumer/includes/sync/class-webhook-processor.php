<?php
/**
 * Webhook payload processor.
 * Applies single-slot changes from webhooks and triggers full sync
 * when the local cache count doesn't match the manager's total.
 *
 * @package WiseRabbit\SlotConsumer\Sync
 */

namespace WiseRabbit\SlotConsumer\Sync;

use WiseRabbit\SlotConsumer\Cache\SlotTransientCache;
use WiseRabbit\SlotConsumer\Traits\LoggerTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WebhookProcessor
 */
class WebhookProcessor {

	use LoggerTrait;

	/**
	 * Process an incoming webhook payload.
	 *
	 * @param array $payload The decoded webhook payload.
	 * @return bool True on success.
	 */
	public function process( array $payload ): bool {
		$action      = $payload['action'] ?? '';
		$slot_data   = $payload['slot'] ?? array();
		$total_count = isset( $payload['total_count'] ) ? (int) $payload['total_count'] : 0;

		if ( empty( $action ) || empty( $slot_data ) ) {
			$this->log_error( 'Invalid webhook payload: missing action or slot data.' );
			return false;
		}

		$cache = new SlotTransientCache();

		try {
			// Apply the single-slot change first.
			switch ( $action ) {
				case 'create':
				case 'update':
					$cache->update_slot( $slot_data );
					$this->log_info( 'Webhook processed: ' . $action . ' slot ID ' . $slot_data['id'] );
					break;

				case 'delete':
					$slot_id = isset( $slot_data['id'] ) ? (int) $slot_data['id'] : 0;
					if ( $slot_id > 0 ) {
						$cache->remove_slot( $slot_id );
						$this->log_info( 'Webhook processed: deleted slot ID ' . $slot_id );
					}
					break;

				default:
					$this->log_error( 'Unknown webhook action: ' . $action );
					return false;
			}

			// Check if local count matches manager's total.
			if ( $total_count > 0 ) {
				$local_count = count( $cache->get_all_slots() );

				if ( $local_count !== $total_count ) {
					$this->log_info(
						'Count mismatch: local=' . $local_count . ' manager=' . $total_count . '. Triggering full sync.'
					);

					$sync   = new SlotSyncManager();
					$result = $sync->sync( 'webhook-resync' );

					if ( is_wp_error( $result ) ) {
						$this->log_error( 'Webhook resync failed: ' . $result->get_error_message() );
					} else {
						$this->log_info( 'Webhook resync completed: ' . $result . ' slots.' );
					}
				}
			}

			return true;
		} catch ( \Exception $e ) {
			$this->log_error( 'Webhook processing failed: ' . $e->getMessage() );
			return false;
		}
	}
}
