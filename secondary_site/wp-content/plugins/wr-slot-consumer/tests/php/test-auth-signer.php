<?php
/**
 * Tests for AuthSigner — standalone, no WordPress dependencies.
 */

use WiseRabbit\SlotConsumer\Api\AuthSigner;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class Test_AuthSigner extends TestCase {

	private $api_key      = 'test-api-key-32chars-abcdef123456';
	private $consumer_url = 'http://sec.wiserabbit.com:81';

	// ── generate_hmac ──

	public function test_generate_hmac_returns_64_char_hex() {
		$hmac = AuthSigner::generate_hmac( $this->api_key, $this->consumer_url );
		$this->assertMatchesRegularExpression( '/^[a-f0-9]{64}$/', $hmac );
	}

	public function test_generate_hmac_is_deterministic() {
		$a = AuthSigner::generate_hmac( $this->api_key, $this->consumer_url );
		$b = AuthSigner::generate_hmac( $this->api_key, $this->consumer_url );
		$this->assertSame( $a, $b );
	}

	public function test_generate_hmac_different_keys_differ() {
		$a = AuthSigner::generate_hmac( 'key-one', $this->consumer_url );
		$b = AuthSigner::generate_hmac( 'key-two', $this->consumer_url );
		$this->assertNotSame( $a, $b );
	}

	public function test_generate_hmac_different_urls_differ() {
		$a = AuthSigner::generate_hmac( $this->api_key, 'http://site-a.com' );
		$b = AuthSigner::generate_hmac( $this->api_key, 'http://site-b.com' );
		$this->assertNotSame( $a, $b );
	}

	public function test_generate_hmac_handles_empty_inputs() {
		$hmac = AuthSigner::generate_hmac( '', '' );
		$this->assertMatchesRegularExpression( '/^[a-f0-9]{64}$/', $hmac );
	}

	// ── generate_nonce ──

	public function test_generate_nonce_returns_64_char_hex() {
		$hmac  = AuthSigner::generate_hmac( $this->api_key, $this->consumer_url );
		$nonce = AuthSigner::generate_nonce( $this->api_key, $hmac );
		$this->assertMatchesRegularExpression( '/^[a-f0-9]{64}$/', $nonce );
	}

	public function test_generate_nonce_is_deterministic_within_window() {
		$hmac = AuthSigner::generate_hmac( $this->api_key, $this->consumer_url );
		$a    = AuthSigner::generate_nonce( $this->api_key, $hmac );
		$b    = AuthSigner::generate_nonce( $this->api_key, $hmac );
		$this->assertSame( $a, $b );
	}

	public function test_generate_nonce_different_hmacs_differ() {
		$a = AuthSigner::generate_nonce( $this->api_key, 'hmac-aaa' );
		$b = AuthSigner::generate_nonce( $this->api_key, 'hmac-bbb' );
		$this->assertNotSame( $a, $b );
	}

	// ── build_headers ──

	public function test_build_headers_has_all_four_keys() {
		$headers = AuthSigner::build_headers( $this->api_key, $this->consumer_url );
		$this->assertArrayHasKey( 'Authorization', $headers );
		$this->assertArrayHasKey( 'X-Signature', $headers );
		$this->assertArrayHasKey( 'X-Auth-Nonce', $headers );
		$this->assertArrayHasKey( 'X-Origin', $headers );
	}

	public function test_build_headers_bearer_format() {
		$headers = AuthSigner::build_headers( $this->api_key, $this->consumer_url );
		$this->assertSame( 'Bearer ' . $this->api_key, $headers['Authorization'] );
	}

	public function test_build_headers_origin_matches_consumer_url() {
		$headers = AuthSigner::build_headers( $this->api_key, $this->consumer_url );
		$this->assertSame( $this->consumer_url, $headers['X-Origin'] );
	}

	public function test_build_headers_signature_matches_generate_hmac() {
		$headers  = AuthSigner::build_headers( $this->api_key, $this->consumer_url );
		$expected = AuthSigner::generate_hmac( $this->api_key, $this->consumer_url );
		$this->assertSame( $expected, $headers['X-Signature'] );
	}

	// ── validate_request ──

	private function make_request( $api_key, $consumer_url ) {
		$headers = AuthSigner::build_headers( $api_key, $consumer_url );
		$request = new WP_REST_Request();
		$request->set_header( 'Authorization', $headers['Authorization'] );
		$request->set_header( 'X-Signature', $headers['X-Signature'] );
		$request->set_header( 'X-Auth-Nonce', $headers['X-Auth-Nonce'] );
		$request->set_header( 'X-Origin', $headers['X-Origin'] );
		return $request;
	}

	public function test_validate_request_passes_with_valid_headers() {
		$request = $this->make_request( $this->api_key, $this->consumer_url );
		$result  = AuthSigner::validate_request( $request, $this->api_key, array( $this->consumer_url ) );
		$this->assertTrue( $result );
	}

	public function test_validate_request_fails_with_empty_stored_key() {
		$request = $this->make_request( $this->api_key, $this->consumer_url );
		$result  = AuthSigner::validate_request( $request, '', array( $this->consumer_url ) );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'auth_missing', $result->get_error_code() );
	}

	public function test_validate_request_fails_with_missing_bearer() {
		$request = new WP_REST_Request();
		$result  = AuthSigner::validate_request( $request, $this->api_key, array( $this->consumer_url ) );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'auth_bearer', $result->get_error_code() );
	}

	public function test_validate_request_fails_with_wrong_bearer_token() {
		$request = $this->make_request( 'wrong-key', $this->consumer_url );
		$result  = AuthSigner::validate_request( $request, $this->api_key, array( $this->consumer_url ) );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'auth_bearer', $result->get_error_code() );
	}

	public function test_validate_request_fails_with_missing_origin() {
		$request = new WP_REST_Request();
		$request->set_header( 'Authorization', 'Bearer ' . $this->api_key );
		$result = AuthSigner::validate_request( $request, $this->api_key, array( $this->consumer_url ) );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'auth_origin', $result->get_error_code() );
	}

	public function test_validate_request_fails_with_empty_allowed_urls() {
		$request = $this->make_request( $this->api_key, $this->consumer_url );
		$result  = AuthSigner::validate_request( $request, $this->api_key, array() );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'auth_origin', $result->get_error_code() );
	}

	public function test_validate_request_fails_with_unknown_origin() {
		$request = $this->make_request( $this->api_key, 'http://evil.com' );
		$result  = AuthSigner::validate_request( $request, $this->api_key, array( $this->consumer_url ) );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'auth_origin', $result->get_error_code() );
	}

	public function test_validate_request_fails_with_wrong_hmac() {
		$request = $this->make_request( $this->api_key, $this->consumer_url );
		$request->set_header( 'X-Signature', 'tampered-signature' );
		$result = AuthSigner::validate_request( $request, $this->api_key, array( $this->consumer_url ) );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'auth_hmac', $result->get_error_code() );
	}

	public function test_validate_request_fails_with_missing_nonce() {
		$headers = AuthSigner::build_headers( $this->api_key, $this->consumer_url );
		$request = new WP_REST_Request();
		$request->set_header( 'Authorization', $headers['Authorization'] );
		$request->set_header( 'X-Signature', $headers['X-Signature'] );
		$request->set_header( 'X-Origin', $headers['X-Origin'] );
		$result = AuthSigner::validate_request( $request, $this->api_key, array( $this->consumer_url ) );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'auth_nonce', $result->get_error_code() );
	}

	public function test_validate_request_fails_with_expired_nonce() {
		$request = $this->make_request( $this->api_key, $this->consumer_url );
		$request->set_header( 'X-Auth-Nonce', 'wrong-nonce' );
		$result = AuthSigner::validate_request( $request, $this->api_key, array( $this->consumer_url ) );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'auth_nonce', $result->get_error_code() );
	}

	// ── Cross-plugin parity ──

	public function test_hmac_matches_manager_plugin_output() {
		// Both plugins use identical AuthSigner logic — verify the algorithm is the same.
		$payload = base64_encode( $this->api_key . ':' . $this->consumer_url );
		$expected = hash_hmac( 'sha256', $payload, $this->api_key );
		$this->assertSame( $expected, AuthSigner::generate_hmac( $this->api_key, $this->consumer_url ) );
	}
}
