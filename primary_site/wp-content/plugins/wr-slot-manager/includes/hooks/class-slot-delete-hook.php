<?php
/**
 * Hook handler for slot post deletions.
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
 * Class SlotDeleteHook
 */
class SlotDeleteHook {

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
	 * Handle wp_trash_post or before_delete_post action.
	 *
	 * @param int $post_id The post ID being deleted/trashed.
	 * @return void
	 */
	public function handle( int $post_id ): void {
		$post = get_post( $post_id );

		if ( ! $post || SlotPostType::POST_TYPE !== $post->post_type ) {
			return;
		}

		try {
			$slot_data = $this->cache->format_slot( $post );

			// Only dispatch if the slot is still in the cache (prevents double webhook on trash+delete).
			$cached = $this->cache->get_all_slots();
			$in_cache = false;
			foreach ( $cached as $s ) {
				if ( (int) $s['id'] === $post_id ) {
					$in_cache = true;
					break;
				}
			}

			$this->cache->remove_slot( $post_id );

			if ( ! $in_cache ) {
				$this->log_info( 'Slot already removed from cache, skipping webhook for post ID: ' . $post_id );
				return;
			}

			$total_count = count( $this->cache->get_all_slots() );

			$dispatcher = new WebhookDispatcher();
			$dispatcher->dispatch( 'delete', $slot_data, $total_count );

			$this->log_info( 'Slot deleted and webhook dispatched for post ID: ' . $post_id );
		} catch ( \Exception $e ) {
			$this->log_error( 'SlotDeleteHook failed for post ID ' . $post_id . ': ' . $e->getMessage() );
		}
	}
}
