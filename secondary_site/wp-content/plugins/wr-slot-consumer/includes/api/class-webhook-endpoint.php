<?php
/**
 * REST API endpoint for receiving webhooks.
 * Auth is handled by 3-layer validation in the permission_callback.
 *
 * @package WiseRabbit\SlotConsumer\Api
 */

namespace WiseRabbit\SlotConsumer\Api;

use WiseRabbit\SlotConsumer\Sync\WebhookProcessor;
use WiseRabbit\SlotConsumer\Traits\LoggerTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WebhookEndpoint
 */
class WebhookEndpoint {

	use LoggerTrait;

	/**
	 * API namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'wr-slot-consumer/v1';

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/webhook',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'handle_webhook' ),
				'permission_callback' => array( Authentication::class, 'validate_request' ),
			)
		);
	}

	/**
	 * Handle incoming webhook POST request.
	 * All 3 auth layers already validated by permission_callback.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response
	 */
	public function handle_webhook( \WP_REST_Request $request ): \WP_REST_Response {
		$rate_check = RateLimiter::check();
		if ( is_wp_error( $rate_check ) ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => $rate_check->get_error_message(),
				),
				429
			);
		}

		try {
			$payload = $request->get_json_params();

			if ( empty( $payload ) || ! isset( $payload['action'] ) || ! isset( $payload['slot'] ) ) {
				$this->log_error( 'Webhook received with invalid payload.' );
				return new \WP_REST_Response(
					array(
						'success' => false,
						'message' => 'Invalid payload.',
					),
					400
				);
			}

			$processor = new WebhookProcessor();
			$result    = $processor->process( $payload );

			if ( ! $result ) {
				return new \WP_REST_Response(
					array(
						'success' => false,
						'message' => 'Processing failed.',
					),
					500
				);
			}

			$this->log_info( 'Webhook processed successfully: ' . $payload['action'] );

			return new \WP_REST_Response(
				array(
					'success' => true,
					'message' => 'Webhook processed.',
				),
				200
			);
		} catch ( \Exception $e ) {
			$this->log_error( 'Webhook endpoint error: ' . $e->getMessage() );
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Internal error.',
				),
				500
			);
		}
	}
}
