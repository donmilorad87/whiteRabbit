<?php
/**
 * Slot transient cache manager.
 *
 * @package WiseRabbit\SlotConsumer\Cache
 */

namespace WiseRabbit\SlotConsumer\Cache;

use WiseRabbit\SlotConsumer\Traits\LoggerTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SlotTransientCache
 */
class SlotTransientCache {

	use LoggerTrait;

	/**
	 * Transient key for cached slots.
	 *
	 * @var string
	 */
	const TRANSIENT_KEY = 'wr_sc_slots_cache';

	/**
	 * Get all cached slots.
	 *
	 * @return array
	 */
	public function get_all_slots(): array {
		$data = get_transient( self::TRANSIENT_KEY );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Set the full slots cache.
	 *
	 * @param array $slots The slots array.
	 * @return void
	 */
	public function set_all_slots( array $slots ): void {
		$expiry = $this->get_expiry_seconds();
		set_transient( self::TRANSIENT_KEY, $slots, $expiry );
		$this->log_info( 'Cache set with ' . count( $slots ) . ' slots. Expiry: ' . ( $expiry / 60 ) . ' min.' );
	}

	/**
	 * Get cache expiry in seconds from the admin setting.
	 *
	 * @return int Seconds.
	 */
	private function get_expiry_seconds(): int {
		$minutes = (int) get_option( WR_SC_OPTION_PREFIX . 'cache_expiry_minutes', 60 );
		return max( 1, $minutes ) * MINUTE_IN_SECONDS;
	}

	/**
	 * Update a single slot in the cache (add or replace).
	 *
	 * @param array $slot_data The slot data with 'id' key.
	 * @return void
	 */
	public function update_slot( array $slot_data ): void {
		if ( empty( $slot_data['id'] ) ) {
			return;
		}

		$slots    = $this->get_all_slots();
		$found    = false;
		$target_id = (int) $slot_data['id'];

		$slots = array_map(
			function ( $slot ) use ( $target_id, $slot_data, &$found ) {
				if ( (int) $slot['id'] === $target_id ) {
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

		$this->set_all_slots( $slots );
	}

	/**
	 * Remove a slot from the cache by ID.
	 *
	 * @param int $slot_id The slot ID.
	 * @return void
	 */
	public function remove_slot( int $slot_id ): void {
		$slots = $this->get_all_slots();
		$slots = array_values(
			array_filter(
				$slots,
				function ( $slot ) use ( $slot_id ) {
					return (int) $slot['id'] !== (int) $slot_id;
				}
			)
		);

		$this->set_all_slots( $slots );
		$this->log_info( 'Removed slot ID ' . $slot_id . ' from cache.' );
	}

	/**
	 * Check if the cache is expired or empty.
	 *
	 * @return bool True if transient is missing or expired.
	 */
	public function is_expired(): bool {
		return false === get_transient( self::TRANSIENT_KEY );
	}

	/**
	 * Clear the entire cache.
	 *
	 * @return void
	 */
	public function clear(): void {
		delete_transient( self::TRANSIENT_KEY );
		$this->log_info( 'Cache cleared.' );
	}
}
