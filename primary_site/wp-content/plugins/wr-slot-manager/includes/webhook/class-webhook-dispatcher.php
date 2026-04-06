<?php
/**
 * Webhook dispatcher — enqueues jobs for async delivery via WP Cron.
 *
 * @package WiseRabbit\SlotManager\Webhook
 */

namespace WiseRabbit\SlotManager\Webhook;

use WiseRabbit\SlotManager\Admin\SettingsPage;
use WiseRabbit\SlotManager\Traits\LoggerTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WebhookDispatcher
 */
class WebhookDispatcher {

	use LoggerTrait;

	/**
	 * Queue instance.
	 *
	 * @var WebhookQueue
	 */
	private $queue;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->queue = new WebhookQueue();
	}

	/**
	 * Enqueue webhook delivery to all connected sites.
	 *
	 * @param string $action      The action type (create, update, delete).
	 * @param array  $slot_data   The slot data array.
	 * @param int    $total_count Total slot count after the operation.
	 * @return void
	 */
	public function dispatch( string $action, array $slot_data, int $total_count = 0 ): void {
		$sites_page = new SettingsPage();
		$sites      = $sites_page->get_sites();

		if ( empty( $sites ) ) {
			$this->log_info( 'No connected sites. Skipping webhook dispatch.' );
			return;
		}

		$payload = WebhookPayload::build( $action, $slot_data, $total_count );
		$jobs    = array();

		foreach ( $sites as $site_url ) {
			$jobs[] = array(
				'url'        => trailingslashit( $site_url ) . '?rest_route=/wr-slot-consumer/v1/webhook',
				'target_url' => $site_url,
				'payload'    => $payload,
			);
		}

		$this->queue->enqueue( $jobs );

		$sender = new WebhookSender();
		$sender->process();

		$this->log_info( 'Dispatched ' . $action . ' webhook to ' . count( $sites ) . ' site(s).' );
	}
}
