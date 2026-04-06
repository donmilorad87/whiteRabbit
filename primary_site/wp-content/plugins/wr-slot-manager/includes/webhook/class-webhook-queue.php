<?php
/**
 * Webhook queue manager using wp_options.
 *
 * @package WiseRabbit\SlotManager\Webhook
 */

namespace WiseRabbit\SlotManager\Webhook;

use WiseRabbit\SlotManager\Traits\LoggerTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WebhookQueue
 */
class WebhookQueue {

	use LoggerTrait;

	/**
	 * Option key for the queue.
	 *
	 * @var string
	 */
	const QUEUE_KEY = 'wr_sm_webhook_queue';

	/**
	 * Enqueue webhook jobs.
	 *
	 * @param array $entries Array of ['url' => string, 'payload' => array].
	 * @return void
	 */
	public function enqueue( array $entries ): void {
		$queue = $this->get_queue();

		foreach ( $entries as $entry ) {
			$queue[] = $entry;
		}

		update_option( self::QUEUE_KEY, $queue, false );
		$this->log_info( 'Enqueued ' . count( $entries ) . ' webhook jobs. Queue size: ' . count( $queue ) );
	}

	/**
	 * Dequeue the next job from the queue.
	 *
	 * @return array|null The next job or null if empty.
	 */
	public function dequeue(): ?array {
		$queue = $this->get_queue();

		if ( empty( $queue ) ) {
			return null;
		}

		$job = array_shift( $queue );
		update_option( self::QUEUE_KEY, $queue, false );

		return $job;
	}

	/**
	 * Check if the queue is empty.
	 *
	 * @return bool
	 */
	public function is_empty(): bool {
		$queue = $this->get_queue();
		return empty( $queue );
	}

	/**
	 * Get the current queue count.
	 *
	 * @return int
	 */
	public function count(): int {
		return count( $this->get_queue() );
	}

	/**
	 * Clear the entire queue.
	 *
	 * @return void
	 */
	public function clear(): void {
		update_option( self::QUEUE_KEY, array(), false );
	}

	/**
	 * Get the raw queue array.
	 *
	 * @return array
	 */
	private function get_queue(): array {
		$queue = get_option( self::QUEUE_KEY, array() );
		return is_array( $queue ) ? $queue : array();
	}
}
