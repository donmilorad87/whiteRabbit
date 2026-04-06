<?php
/**
 * Webhook sender - WP Cron handler that processes the queue.
 *
 * @package WiseRabbit\SlotManager\Webhook
 */

namespace WiseRabbit\SlotManager\Webhook;

use WiseRabbit\SlotManager\Api\AuthSigner;
use WiseRabbit\SlotManager\Traits\LoggerTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WebhookSender
 */
class WebhookSender {

	use LoggerTrait;

	/**
	 * Max retry attempts per job.
	 *
	 * @var int
	 */
	const MAX_RETRIES = 3;

	/**
	 * Process all jobs in the webhook queue.
	 *
	 * @return void
	 */
	/**
	 * Maximum jobs to process in a single run (NASA Rule 2: bounded loops).
	 */
	const MAX_JOBS_PER_RUN = 50;

	/**
	 * Process all queued webhook jobs (bounded to MAX_JOBS_PER_RUN).
	 */
	public function process(): void {
		$queue = new WebhookQueue();

		for ( $i = 0; $i < self::MAX_JOBS_PER_RUN; $i++ ) {
			$job = $queue->dequeue();

			if ( null === $job ) {
				break;
			}

			try {
				$this->send( $job );
			} catch ( \Exception $e ) {
				$this->log_error( 'Webhook send failed: ' . $e->getMessage() );
				$this->handle_failure( $job, $queue );
			}
		}
	}

	/**
	 * Send a webhook HTTP request.
	 *
	 * @param array $job The job data with 'url' and 'payload'.
	 * @return void
	 * @throws \Exception On HTTP failure.
	 */
	private function send( array $job ): void {
		if ( empty( $job['url'] ) || empty( $job['target_url'] ) || empty( $job['payload'] ) ) {
			throw new \Exception( 'Malformed webhook job: missing url, target_url, or payload.' );
		}

		$api_key    = get_option( WR_SM_OPTION_PREFIX . 'api_key', '' );
		$target_url = $job['target_url'];

		$headers = AuthSigner::build_headers( $api_key, $target_url );
		$headers['Content-Type'] = 'application/json';

		$response = wp_remote_post(
			$job['url'],
			array(
				'headers'     => $headers,
				'body'        => wp_json_encode( $job['payload'] ),
				'timeout'     => 30,
				'sslverify'   => ! ( defined( 'ENVIRONMENT' ) && 'dev' === ENVIRONMENT ),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( $code < 200 || $code >= 300 ) {
			throw new \Exception( 'HTTP ' . $code . ' from ' . $job['url'] );
		}

		$this->log_info( 'Webhook sent successfully to: ' . $job['url'] );
	}

	/**
	 * Handle a failed webhook delivery.
	 *
	 * @param array        $job   The failed job.
	 * @param WebhookQueue $queue The queue instance.
	 * @return void
	 */
	private function handle_failure( array $job, WebhookQueue $queue ): void {
		$retries = isset( $job['retries'] ) ? (int) $job['retries'] : 0;

		if ( $retries < self::MAX_RETRIES ) {
			$job['retries'] = $retries + 1;
			$queue->enqueue( array( $job ) );
			$this->log_info( 'Re-queued failed webhook (attempt ' . $job['retries'] . '/' . self::MAX_RETRIES . '): ' . $job['url'] );
		} else {
			$this->log_error( 'Webhook permanently failed after ' . self::MAX_RETRIES . ' attempts: ' . $job['url'] );
		}
	}
}
