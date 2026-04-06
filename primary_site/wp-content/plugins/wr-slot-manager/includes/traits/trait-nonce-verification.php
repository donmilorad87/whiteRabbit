<?php
/**
 * Nonce verification helper trait.
 *
 * @package WiseRabbit\SlotManager\Traits
 */

namespace WiseRabbit\SlotManager\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait NonceVerificationTrait
 */
trait NonceVerificationTrait {

	/**
	 * Verify a nonce from the request.
	 *
	 * @param string $nonce_field The nonce field name in $_POST.
	 * @param string $action      The nonce action string.
	 * @return bool True if valid, false otherwise.
	 */
	protected function verify_nonce( string $nonce_field, string $action ): bool {
		if ( ! isset( $_POST[ $nonce_field ] ) ) {
			return false;
		}
		return (bool) wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST[ $nonce_field ] ) ),
			$action
		);
	}
}
