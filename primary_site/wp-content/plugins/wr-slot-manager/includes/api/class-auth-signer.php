<?php
/**
 * 3-layer authentication signer and verifier.
 *
 * Layer 1: Bearer token (shared API key).
 * Layer 2: HMAC signature — HMAC-SHA256(base64(api_key:consumer_site_url), api_key).
 * Layer 3: Time-based nonce — HMAC-SHA256(base64(api_key:hmac):time_window, api_key).
 *
 * The HMAC always uses the consumer site URL, regardless of request direction.
 * The consumer URL is sent in the X-Origin header so the receiver can verify.
 *
 * @package WiseRabbit\SlotManager\Api
 */

namespace WiseRabbit\SlotManager\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AuthSigner
 */
class AuthSigner {

	/**
	 * Nonce time window in seconds (5 minutes).
	 *
	 * @var int
	 */
	const NONCE_WINDOW = 300;

	/**
	 * Generate HMAC signature using the consumer site URL.
	 *
	 * @param string $api_key      Shared API key.
	 * @param string $consumer_url The consumer site URL.
	 * @return string HMAC hex digest.
	 */
	public static function generate_hmac( string $api_key, string $consumer_url ): string {
		$payload = base64_encode( $api_key . ':' . $consumer_url );
		return hash_hmac( 'sha256', $payload, $api_key );
	}

	/**
	 * Generate a time-based nonce from the API key and HMAC.
	 *
	 * @param string $api_key Shared API key.
	 * @param string $hmac    The HMAC signature.
	 * @return string Nonce hex digest.
	 */
	public static function generate_nonce( string $api_key, string $hmac ): string {
		$seed   = base64_encode( $api_key . ':' . $hmac );
		$window = (string) floor( time() / self::NONCE_WINDOW );
		return hash_hmac( 'sha256', $seed . ':' . $window, $api_key );
	}

	/**
	 * Build all auth headers for an outgoing request.
	 *
	 * @param string $api_key      Shared API key.
	 * @param string $consumer_url The consumer site URL (always the consumer, regardless of direction).
	 * @return array Associative array of headers.
	 */
	public static function build_headers( string $api_key, string $consumer_url ): array {
		$hmac  = self::generate_hmac( $api_key, $consumer_url );
		$nonce = self::generate_nonce( $api_key, $hmac );

		return array(
			'Authorization' => 'Bearer ' . $api_key,
			'X-Signature'   => $hmac,
			'X-Auth-Nonce'  => $nonce,
			'X-Origin'      => $consumer_url,
		);
	}

	/**
	 * Validate all three layers on an incoming request.
	 *
	 * The consumer URL is read from the X-Origin header, then validated
	 * against a list of allowed URLs.
	 *
	 * @param \WP_REST_Request $request      The incoming REST request.
	 * @param string           $stored_key   The stored API key.
	 * @param array            $allowed_urls List of allowed consumer URLs (or empty to skip URL check).
	 * @return true|\WP_Error True on success, WP_Error with reason on failure.
	 */
	public static function validate_request( \WP_REST_Request $request, string $stored_key, array $allowed_urls = array() ): true|\WP_Error {
		if ( empty( $stored_key ) ) {
			return new \WP_Error( 'auth_missing', 'API key not configured.' );
		}

		// Layer 1: Bearer token.
		$auth_header = $request->get_header( 'Authorization' );
		if ( empty( $auth_header ) || 0 !== strpos( $auth_header, 'Bearer ' ) ) {
			return new \WP_Error( 'auth_bearer', 'Missing Bearer token.' );
		}

		$token = substr( $auth_header, 7 );
		if ( ! hash_equals( $stored_key, $token ) ) {
			return new \WP_Error( 'auth_bearer', 'Invalid Bearer token.' );
		}

		// Read consumer URL from X-Origin header.
		$consumer_url = $request->get_header( 'X-Origin' );
		if ( empty( $consumer_url ) ) {
			return new \WP_Error( 'auth_origin', 'Missing X-Origin header.' );
		}

		// Validate the consumer URL is in the allowed list.
		if ( empty( $allowed_urls ) ) {
			return new \WP_Error( 'auth_origin', 'No allowed origins configured.' );
		}

		if ( ! in_array( untrailingslashit( $consumer_url ), $allowed_urls, true ) ) {
			return new \WP_Error( 'auth_origin', 'Unknown origin site.' );
		}

		// Layer 2: HMAC signature.
		$signature = $request->get_header( 'X-Signature' );
		if ( empty( $signature ) ) {
			return new \WP_Error( 'auth_hmac', 'Missing X-Signature header.' );
		}

		$expected_hmac = self::generate_hmac( $stored_key, $consumer_url );
		if ( ! hash_equals( $expected_hmac, $signature ) ) {
			return new \WP_Error( 'auth_hmac', 'Invalid HMAC signature.' );
		}

		// Layer 3: Time-based nonce.
		$nonce = $request->get_header( 'X-Auth-Nonce' );
		if ( empty( $nonce ) ) {
			return new \WP_Error( 'auth_nonce', 'Missing X-Auth-Nonce header.' );
		}

		if ( ! self::verify_nonce( $nonce, $stored_key, $signature ) ) {
			return new \WP_Error( 'auth_nonce', 'Invalid or expired nonce.' );
		}

		return true;
	}

	/**
	 * Verify a time-based nonce (checks current + previous window).
	 *
	 * @param string $nonce   The nonce to verify.
	 * @param string $api_key Shared API key.
	 * @param string $hmac    The HMAC signature.
	 * @return bool
	 */
	private static function verify_nonce( string $nonce, string $api_key, string $hmac ): bool {
		$seed    = base64_encode( $api_key . ':' . $hmac );
		$current = floor( time() / self::NONCE_WINDOW );

		for ( $i = 0; $i <= 1; $i++ ) {
			$expected = hash_hmac( 'sha256', $seed . ':' . (string) ( $current - $i ), $api_key );
			if ( hash_equals( $expected, $nonce ) ) {
				return true;
			}
		}

		return false;
	}
}
