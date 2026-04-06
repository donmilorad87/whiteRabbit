<?php
/**
 * Webhook payload builder.
 * Auth signatures are now in HTTP headers (AuthSigner), not in the payload body.
 *
 * @package WiseRabbit\SlotManager\Webhook
 */

namespace WiseRabbit\SlotManager\Webhook;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WebhookPayload
 */
class WebhookPayload {

	/**
	 * Build a webhook payload.
	 *
	 * @param string $action      The action type (create, update, delete).
	 * @param array  $slot_data   The slot data array.
	 * @param int    $total_count Total number of slots on the manager.
	 * @return array The payload.
	 */
	public static function build( string $action, array $slot_data, int $total_count = 0 ): array {
		return array(
			'action'      => $action,
			'timestamp'   => gmdate( 'c' ),
			'slot'        => $slot_data,
			'total_count' => (int) $total_count,
		);
	}
}
