<?php
/**
 * REST API endpoint for slots.
 *
 * @package WiseRabbit\SlotManager\Api
 */

namespace WiseRabbit\SlotManager\Api;

use WiseRabbit\SlotManager\Cache\SlotCache;
use WiseRabbit\SlotManager\Traits\LoggerTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SlotsEndpoint
 */
class SlotsEndpoint {

	use LoggerTrait;

	/**
	 * API namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'wr-slot-manager/v1';

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/slots',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_slots' ),
				'permission_callback' => array( Authentication::class, 'validate_request' ),
			)
		);
	}

	/**
	 * Handle GET /slots request.
	 *
	 * Bearer token already validated by permission_callback.
	 * We set an admin user context so WP_Query can read private posts.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response
	 */
	public function get_slots( \WP_REST_Request $request ): \WP_REST_Response {
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
			$this->set_admin_context();

			$cache = new SlotCache();
			$slots = $cache->get_all_slots();

			return new \WP_REST_Response(
				array(
					'success' => true,
					'data'    => $slots,
					'count'   => count( $slots ),
				),
				200
			);
		} catch ( \Exception $e ) {
			$this->log_error( 'REST slots endpoint failed: ' . $e->getMessage() );

			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Failed to retrieve slots.', 'wr-slot-manager' ),
				),
				500
			);
		}
	}

	/**
	 * Set an admin user context so WP_Query can read private posts.
	 *
	 * @return void
	 */
	private function set_admin_context(): void {
		$admin_id = wp_cache_get( 'wr_sm_admin_user_id', WR_SM_CACHE_GROUP );

		if ( false === $admin_id ) {
			$admins   = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
			$admin_id = ! empty( $admins ) ? (int) $admins[0]->ID : 0;

			if ( $admin_id > 0 ) {
				wp_cache_set( 'wr_sm_admin_user_id', $admin_id, WR_SM_CACHE_GROUP, HOUR_IN_SECONDS );
			}
		}

		if ( $admin_id > 0 ) {
			wp_set_current_user( $admin_id );
		} else {
			$this->log_error( 'No administrator user found — private slots will not be visible via API.' );
		}
	}
}
