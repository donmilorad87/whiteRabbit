<?php
/**
 * Slot sync manager — handles full sync from primary API.
 *
 * @package WiseRabbit\SlotConsumer\Sync
 */

namespace WiseRabbit\SlotConsumer\Sync;

use WiseRabbit\SlotConsumer\Api\AuthSigner;
use WiseRabbit\SlotConsumer\Cache\SlotTransientCache;
use WiseRabbit\SlotConsumer\Traits\OptionPrefixTrait;
use WiseRabbit\SlotConsumer\Traits\LoggerTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SlotSyncManager
 */
class SlotSyncManager {

	use OptionPrefixTrait;
	use LoggerTrait;

	/**
	 * Handle AJAX sync request.
	 *
	 * @return void
	 */
	public function handle_ajax_sync(): void {
		check_ajax_referer( 'wr_sc_sync_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'wr-slot-consumer' ) ), 403 );
			return;
		}

		$result = $this->sync();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			return;
		}

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: number of slots synced */
					__( 'Successfully synced %d slots.', 'wr-slot-consumer' ),
					$result
				),
				'count'   => $result,
			)
		);
	}

	/**
	 * Perform a full sync from the primary API.
	 *
	 * @param string $source Source identifier ('manual', 'block').
	 * @return int|\WP_Error Number of slots synced or WP_Error.
	 */
	public function sync( string $source = 'manual' ): int|\WP_Error {
		$source_url = $this->get_option( 'source_url', '' );
		$api_key    = $this->get_option( 'api_key', '' );

		if ( empty( $source_url ) || empty( $api_key ) ) {
			$this->log_error( 'Sync failed: source URL or API key not configured.' );
			return new \WP_Error( 'config_missing', __( 'Source URL and API key must be configured.', 'wr-slot-consumer' ) );
		}

		try {
			$api_url      = trailingslashit( $source_url ) . '?rest_route=/wr-slot-manager/v1/slots&source=' . $source;
			$consumer_url = untrailingslashit( get_option( 'siteurl', '' ) );

			$headers = AuthSigner::build_headers( $api_key, $consumer_url );

			$response = wp_remote_get(
				$api_url,
				array(
					'headers'   => $headers,
					'timeout'   => 30,
					'sslverify' => ! ( defined( 'ENVIRONMENT' ) && 'dev' === ENVIRONMENT ),
				)
			);

			if ( is_wp_error( $response ) ) {
				$this->log_error( 'Sync HTTP request failed: ' . $response->get_error_message() );
				return $response;
			}

			$code = wp_remote_retrieve_response_code( $response );

			if ( 200 !== $code ) {
				$this->log_error( 'Sync failed with HTTP ' . $code );
				return new \WP_Error( 'http_error', sprintf( __( 'Primary API returned HTTP %d.', 'wr-slot-consumer' ), $code ) );
			}

			$raw_body = wp_remote_retrieve_body( $response );
			$body     = json_decode( $raw_body, true );

			if ( ! isset( $body['success'] ) || true !== $body['success'] || ! isset( $body['data'] ) ) {
				$this->log_error( 'Sync failed: invalid response. URL: ' . $api_url . ' | HTTP: ' . $code . ' | Body: ' . substr( $raw_body, 0, 500 ) );
				return new \WP_Error(
					'invalid_response',
					__( 'Invalid response from primary API.', 'wr-slot-consumer' )
				);
			}

			$cache = new SlotTransientCache();
			$cache->clear();
			$cache->set_all_slots( $body['data'] );

			$count = count( $body['data'] );
			$this->log_info( 'Sync completed (' . $source . '). ' . $count . ' slots cached.' );

			return $count;
		} catch ( \Exception $e ) {
			$this->log_error( 'Sync exception: ' . $e->getMessage() );
			return new \WP_Error( 'sync_error', $e->getMessage() );
		}
	}
}
