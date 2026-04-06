<?php
/**
 * Logger trait for consistent logging.
 * Info: only in dev. Errors: always (NASA Rule 7 — never ignore failures).
 *
 * @package WiseRabbit\SlotConsumer\Traits
 */

namespace WiseRabbit\SlotConsumer\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait LoggerTrait
 */
trait LoggerTrait {

	/**
	 * Log an informational message (dev only).
	 *
	 * @param string $message The message to log.
	 * @return void
	 */
	protected function log_info( string $message ): void {
		if ( defined( 'ENVIRONMENT' ) && 'dev' === ENVIRONMENT ) {
			error_log( '[wr-slot-consumer] INFO: ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}

	/**
	 * Log an error message. Always fires regardless of ENVIRONMENT.
	 *
	 * @param string $message The error message to log.
	 * @return void
	 */
	protected function log_error( string $message ): void {
		error_log( '[wr-slot-consumer] ERROR: ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}
