<?php
/**
 * Template loader trait — VIP-compliant template inclusion.
 *
 * @package WiseRabbit\SlotManager\Traits
 */

namespace WiseRabbit\SlotManager\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait TemplateLoaderTrait
 */
trait TemplateLoaderTrait {

	/**
	 * Load a plugin template file with validated path.
	 *
	 * @param string $template Relative path within the plugin directory (e.g. 'templates/admin/api-key.php').
	 * @param array  $args     Variables to extract into the template scope.
	 * @return void
	 */
	protected function load_template( string $template, array $args = array() ): void {
		$file = WR_SM_PLUGIN_DIR . $template;

		if ( ! file_exists( $file ) ) {
			return;
		}

		if ( 0 !== strpos( realpath( $file ), realpath( WR_SM_PLUGIN_DIR ) ) ) {
			return;
		}

		if ( ! empty( $args ) ) {
			extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		include $file;
	}

	/**
	 * Render a plugin template into a string.
	 *
	 * @param string $template Relative path within the plugin directory.
	 * @param array  $args     Variables to extract into the template scope.
	 * @return string Rendered HTML.
	 */
	protected function render_template( string $template, array $args = array() ): string {
		ob_start();
		$this->load_template( $template, $args );
		return ob_get_clean();
	}
}
