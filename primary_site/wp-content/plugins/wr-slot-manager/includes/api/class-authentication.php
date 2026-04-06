<?php
/**
 * REST API authentication using 3-layer validation.
 *
 * @package WiseRabbit\SlotManager\Api
 */

namespace WiseRabbit\SlotManager\Api;

use WiseRabbit\SlotManager\Admin\SettingsPage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Authentication
 */
class Authentication {

	/**
	 * Permission callback — validates all 3 auth layers.
	 * The HMAC uses the consumer site URL (from X-Origin header),
	 * validated against the Connected Sites list.
	 *
	 * @param \WP_REST_Request $request The REST request.
	 * @return bool True if all layers pass.
	 */
	public static function validate_request( \WP_REST_Request $request ): bool {
		$stored_key = get_option( WR_SM_OPTION_PREFIX . 'api_key', '' );

		$settings     = new SettingsPage();
		$allowed_urls = $settings->get_sites();

		$result = AuthSigner::validate_request( $request, $stored_key, $allowed_urls );

		return ( true === $result );
	}
}
