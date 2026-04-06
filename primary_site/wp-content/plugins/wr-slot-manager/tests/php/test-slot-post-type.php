<?php
/**
 * Tests for SlotPostType::force_private_status — pure array logic, no WP needed.
 */

use WiseRabbit\SlotManager\PostType\SlotPostType;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class Test_SlotPostType extends TestCase {

	private $post_type;

	protected function set_up() {
		$this->post_type = new SlotPostType();
	}

	public function test_slot_post_forced_to_private() {
		$data = $this->post_type->force_private_status(
			array( 'post_type' => 'slot', 'post_status' => 'publish' ),
			array()
		);
		$this->assertSame( 'private', $data['post_status'] );
	}

	public function test_trash_status_not_overridden() {
		$data = $this->post_type->force_private_status(
			array( 'post_type' => 'slot', 'post_status' => 'trash' ),
			array()
		);
		$this->assertSame( 'trash', $data['post_status'] );
	}

	public function test_auto_draft_not_overridden() {
		$data = $this->post_type->force_private_status(
			array( 'post_type' => 'slot', 'post_status' => 'auto-draft' ),
			array()
		);
		$this->assertSame( 'auto-draft', $data['post_status'] );
	}

	public function test_other_post_type_not_affected() {
		$data = $this->post_type->force_private_status(
			array( 'post_type' => 'page', 'post_status' => 'publish' ),
			array()
		);
		$this->assertSame( 'publish', $data['post_status'] );
	}

	public function test_already_private_stays_private() {
		$data = $this->post_type->force_private_status(
			array( 'post_type' => 'slot', 'post_status' => 'private' ),
			array()
		);
		$this->assertSame( 'private', $data['post_status'] );
	}

	public function test_draft_status_forced_to_private() {
		$data = $this->post_type->force_private_status(
			array( 'post_type' => 'slot', 'post_status' => 'draft' ),
			array()
		);
		$this->assertSame( 'private', $data['post_status'] );
	}
}
