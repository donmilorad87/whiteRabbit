<?php
/**
 * Rate limiter for REST API requests.
 * Uses WordPress transients to track request counts per origin IP.
 *
 * @package WiseRabbit\SlotConsumer\Api
 */

namespace WiseRabbit\SlotConsumer\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RateLimiter
 */
class RateLimiter {

	/**
	 * Check if the current request exceeds the rate limit.
	 *
	 * @return true|\WP_Error True if allowed, WP_Error if rate limited.
	 */
	public static function check(): true|\WP_Error {
		$limit = (int) get_option( WR_SC_OPTION_PREFIX . 'rate_limit', 10 );
		$ip    = self::get_client_ip();
		$key   = 'wr_sc_rl_' . md5( $ip );

		$data = get_transient( $key );

		if ( false === $data ) {
			set_transient( $key, array( 'count' => 1, 'start' => time() ), MINUTE_IN_SECONDS );
			return true;
		}

		if ( $data['count'] >= $limit ) {
			$remaining = MINUTE_IN_SECONDS - ( time() - $data['start'] );
			return new \WP_Error(
				'rate_limited',
				'Rate limit exceeded. Try again in ' . max( 1, $remaining ) . ' seconds.',
				array( 'status' => 429 )
			);
		}

		$data['count']++;
		set_transient( $key, $data, MINUTE_IN_SECONDS );

		return true;
	}

	/**
	 * Get the client IP address from the request.
	 *
	 * @return string
	 */
	private static function get_client_ip(): string {
		$headers = array( 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' );

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = trim( explode( ',', $ip )[0] );
				}
				return $ip;
			}
		}

		return '0.0.0.0';
	}
}
