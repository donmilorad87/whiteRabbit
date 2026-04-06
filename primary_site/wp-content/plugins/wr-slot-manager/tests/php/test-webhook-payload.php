<?php
/**
 * Tests for WebhookPayload — standalone, no WordPress dependencies.
 */

use WiseRabbit\SlotManager\Webhook\WebhookPayload;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class Test_WebhookPayload extends TestCase {

	public function test_build_returns_array_with_required_keys() {
		$payload = WebhookPayload::build( 'create', array( 'id' => 1 ), 10 );
		$this->assertArrayHasKey( 'action', $payload );
		$this->assertArrayHasKey( 'timestamp', $payload );
		$this->assertArrayHasKey( 'slot', $payload );
		$this->assertArrayHasKey( 'total_count', $payload );
	}

	public function test_build_action_matches_input() {
		$payload = WebhookPayload::build( 'update', array( 'id' => 5 ), 3 );
		$this->assertSame( 'update', $payload['action'] );
	}

	public function test_build_total_count_is_integer() {
		$payload = WebhookPayload::build( 'delete', array( 'id' => 1 ), '15' );
		$this->assertIsInt( $payload['total_count'] );
		$this->assertSame( 15, $payload['total_count'] );
	}

	public function test_build_timestamp_is_iso8601() {
		$payload = WebhookPayload::build( 'create', array( 'id' => 1 ), 0 );
		$parsed  = \DateTime::createFromFormat( \DateTime::ATOM, $payload['timestamp'] );
		$this->assertInstanceOf( \DateTime::class, $parsed );
	}

	public function test_build_slot_data_preserved() {
		$slot    = array( 'id' => 42, 'title' => 'Test Slot', 'rtp' => 95.5 );
		$payload = WebhookPayload::build( 'create', $slot, 1 );
		$this->assertSame( $slot, $payload['slot'] );
	}

	public function test_build_default_total_count_is_zero() {
		$payload = WebhookPayload::build( 'create', array( 'id' => 1 ) );
		$this->assertSame( 0, $payload['total_count'] );
	}
}
