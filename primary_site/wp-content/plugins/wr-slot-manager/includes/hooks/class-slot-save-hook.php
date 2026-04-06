<?php
/**
 * Hook handler for slot post saves.
 * Uses wp_after_insert_post so all meta fields are already saved.
 *
 * @package WiseRabbit\SlotManager\Hooks
 */

namespace WiseRabbit\SlotManager\Hooks;

use WiseRabbit\SlotManager\Cache\SlotCache;
use WiseRabbit\SlotManager\PostType\SlotPostType;
use WiseRabbit\SlotManager\Webhook\WebhookDispatcher;
use WiseRabbit\SlotManager\Traits\LoggerTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SlotSaveHook
 */
class SlotSaveHook {

	use LoggerTrait;

	/**
	 * Slot cache instance.
	 *
	 * @var SlotCache
	 */
	private $cache;

	/**
	 * Constructor.
	 *
	 * @param SlotCache $cache The slot cache instance.
	 */
	public function __construct( SlotCache $cache ) {
		$this->cache = $cache;
	}

	/**
	 * Handle wp_after_insert_post action.
	 * Fires after post AND meta are fully saved.
	 *
	 * @param int      $post_id The post ID.
	 * @param \WP_Post $post    The post object.
	 * @param bool     $update  Whether this is an update.
	 * @return void
	 */
	public function handle( int $post_id, \WP_Post $post, bool $update ): void {
		if ( SlotPostType::POST_TYPE !== $post->post_type ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		try {
			$this->cache->update_single_slot( $post_id );

			$action      = $update ? 'update' : 'create';
			$slot_data   = $this->cache->format_slot( $post );
			$total_count = count( $this->cache->get_all_slots() );

			$dispatcher = new WebhookDispatcher();
			$dispatcher->dispatch( $action, $slot_data, $total_count );

			$this->log_info( 'Slot ' . $action . 'd and webhook queued for post ID: ' . $post_id );
		} catch ( \Exception $e ) {
			$this->log_error( 'SlotSaveHook failed for post ID ' . $post_id . ': ' . $e->getMessage() );
		}
	}
}
