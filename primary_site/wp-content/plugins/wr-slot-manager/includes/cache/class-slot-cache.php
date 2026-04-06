<?php
/**
 * Slot cache manager using WordPress object cache (Redis-backed).
 *
 * @package WiseRabbit\SlotManager\Cache
 */

namespace WiseRabbit\SlotManager\Cache;

use WiseRabbit\SlotManager\PostType\SlotPostType;
use WiseRabbit\SlotManager\Traits\LoggerTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SlotCache
 */
class SlotCache {

	use LoggerTrait;

	/**
	 * Cache key for all slots.
	 *
	 * @var string
	 */
	const CACHE_KEY = 'all_slots';

	/**
	 * Get all cached slots. Falls back to DB if cache miss.
	 *
	 * @return array
	 */
	public function get_all_slots(): array {
		$cached = wp_cache_get( self::CACHE_KEY, WR_SM_CACHE_GROUP );

		if ( false !== $cached ) {
			return $cached;
		}

		return $this->rebuild_cache();
	}

	/**
	 * Rebuild the cache from the database using WP_Query.
	 *
	 * @return array
	 */
	public function rebuild_cache(): array {
		try {
			$query = new \WP_Query(
				array(
					'post_type'              => SlotPostType::POST_TYPE,
					'post_status'            => array( 'private', 'publish' ),
					'posts_per_page'         => 100,
					'no_found_rows'          => true,
					'update_post_meta_cache' => true,
					'update_post_term_cache' => false,
					'orderby'                => 'date',
					'order'                  => 'DESC',
				)
			);

			$slots = array();

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$slots[] = $this->format_slot( $query->post );
				}
				wp_reset_postdata();
			}

			wp_cache_set( self::CACHE_KEY, $slots, WR_SM_CACHE_GROUP, $this->get_expiry() );

			$this->log_info( 'Cache rebuilt with ' . count( $slots ) . ' slots.' );

			return $slots;
		} catch ( \Exception $e ) {
			$this->log_error( 'Cache rebuild failed: ' . $e->getMessage() );
			return array();
		}
	}

	/**
	 * Update a single slot in the cache.
	 *
	 * @param int $post_id The slot post ID.
	 * @return void
	 */
	public function update_single_slot( int $post_id ): void {
		try {
			$slots = $this->get_all_slots();
			$post  = get_post( $post_id );

			if ( ! $post || SlotPostType::POST_TYPE !== $post->post_type ) {
				return;
			}

			$slot_data = $this->format_slot( $post );
			$found     = false;

			$slots = array_map(
				function ( $slot ) use ( $post_id, $slot_data, &$found ) {
					if ( (int) $slot['id'] === $post_id ) {
						$found = true;
						return $slot_data;
					}
					return $slot;
				},
				$slots
			);

			if ( ! $found ) {
				$slots[] = $slot_data;
			}

			wp_cache_set( self::CACHE_KEY, $slots, WR_SM_CACHE_GROUP, $this->get_expiry() );
		} catch ( \Exception $e ) {
			$this->log_error( 'Failed to update slot in cache: ' . $e->getMessage() );
		}
	}

	/**
	 * Remove a slot from the cache.
	 *
	 * @param int $post_id The slot post ID.
	 * @return void
	 */
	public function remove_slot( int $post_id ): void {
		try {
			$slots   = $this->get_all_slots();
			$slots   = array_values(
				array_filter(
					$slots,
					function ( $slot ) use ( $post_id ) {
						return (int) $slot['id'] !== $post_id;
					}
				)
			);

			wp_cache_set( self::CACHE_KEY, $slots, WR_SM_CACHE_GROUP, $this->get_expiry() );
		} catch ( \Exception $e ) {
			$this->log_error( 'Failed to remove slot from cache: ' . $e->getMessage() );
		}
	}

	/**
	 * Format a slot post into an array for cache/API output.
	 *
	 * @param \WP_Post $post The slot post.
	 * @return array
	 */
	public function format_slot( \WP_Post $post ): array {
		$image_id       = (int) get_post_meta( $post->ID, 'wr_sm_image_id', true );
		$featured_image = $image_id ? wp_get_attachment_url( $image_id ) : '';

		return array(
			'id'              => $post->ID,
			'title'           => $post->post_title,
			'slug'            => $post->post_name,
			'description'     => get_post_meta( $post->ID, 'wr_sm_description', true ),
			'star_rating'     => (float) get_post_meta( $post->ID, 'wr_sm_star_rating', true ),
			'featured_image'  => $featured_image,
			'provider_name'   => get_post_meta( $post->ID, 'wr_sm_provider_name', true ),
			'rtp'             => (float) get_post_meta( $post->ID, 'wr_sm_rtp', true ),
			'min_wager'       => (float) get_post_meta( $post->ID, 'wr_sm_min_wager', true ),
			'max_wager'       => (float) get_post_meta( $post->ID, 'wr_sm_max_wager', true ),
			'status'          => $post->post_status,
			'created_at'      => $post->post_date_gmt,
			'updated_at'      => $post->post_modified_gmt,
		);
	}

	/**
	 * Get cache expiry in seconds from the admin setting.
	 *
	 * @return int Seconds.
	 */
	private function get_expiry(): int {
		$minutes = (int) get_option( WR_SM_OPTION_PREFIX . 'cache_expiry_minutes', 60 );
		return max( 1, $minutes ) * MINUTE_IN_SECONDS;
	}

	/**
	 * Flush the entire slot cache.
	 *
	 * @return void
	 */
	public function flush(): void {
		wp_cache_delete( self::CACHE_KEY, WR_SM_CACHE_GROUP );
		$this->log_info( 'Cache flushed.' );
	}
}
