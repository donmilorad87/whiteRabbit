<?php
/**
 * Prefixed option helper trait.
 *
 * @package WiseRabbit\SlotManager\Traits
 */

namespace WiseRabbit\SlotManager\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait OptionPrefixTrait
 */
trait OptionPrefixTrait {

	/**
	 * Get a prefixed option value.
	 *
	 * @param string $key     The option key (without prefix).
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	protected function get_option( string $key, mixed $default = false ): mixed {
		return \get_option( WR_SM_OPTION_PREFIX . $key, $default );
	}

	/**
	 * Update a prefixed option value.
	 *
	 * @param string $key   The option key (without prefix).
	 * @param mixed  $value The value to store.
	 * @return bool
	 */
	protected function update_option( string $key, mixed $value ): bool {
		return \update_option( WR_SM_OPTION_PREFIX . $key, $value );
	}

	/**
	 * Delete a prefixed option.
	 *
	 * @param string $key The option key (without prefix).
	 * @return bool
	 */
	protected function delete_option( string $key ): bool {
		return \delete_option( WR_SM_OPTION_PREFIX . $key );
	}
}
