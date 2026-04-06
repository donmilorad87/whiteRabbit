<?php
/**
 * REST API authentication using 3-layer validation.
 *
 * @package WiseRabbit\SlotConsumer\Api
 */

namespace WiseRabbit\SlotConsumer\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Authentication
 */
class Authentication {

	/**
	 * Permission callback — validates all 3 auth layers.
	 * The HMAC uses the consumer site URL (this site's own URL).
	 * X-Origin must match this site's URL.
	 *
	 * @param \WP_REST_Request $request The REST request.
	 * @return bool True if all layers pass.
	 */
	public static function validate_request( \WP_REST_Request $request ): bool {
		$stored_key = get_option( WR_SC_OPTION_PREFIX . 'api_key', '' );
		$own_url    = untrailingslashit( get_option( 'siteurl', '' ) );

		$result = AuthSigner::validate_request( $request, $stored_key, array( $own_url ) );

		return ( true === $result );
	}
}
