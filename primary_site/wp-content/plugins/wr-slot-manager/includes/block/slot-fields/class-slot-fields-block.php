<?php
/**
 * Slot Fields Gutenberg block registration.
 * This block is inserted via the CPT template and locked in place.
 *
 * @package WiseRabbit\SlotManager\Block\SlotFields
 */

namespace WiseRabbit\SlotManager\Block\SlotFields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SlotFieldsBlock
 */
class SlotFieldsBlock {

	/**
	 * Register the block type.
	 *
	 * @return void
	 */
	public function register(): void {
		$editor_js  = WR_SM_PLUGIN_DIR . 'assets/editor/js/slotFields.js';
		$editor_css = WR_SM_PLUGIN_DIR . 'assets/editor/css/slotFields.css';

		if ( file_exists( $editor_js ) ) {
			wp_register_script(
				'wr-sm-slot-fields-editor',
				WR_SM_PLUGIN_URL . 'assets/editor/js/slotFields.js',
				array(
					'wp-blocks',
					'wp-element',
					'wp-block-editor',
					'wp-components',
					'wp-data',
					'wp-i18n',
				),
				filemtime( $editor_js ),
				true
			);
		}

		if ( file_exists( $editor_css ) ) {
			wp_register_style(
				'wr-sm-slot-fields-editor-style',
				WR_SM_PLUGIN_URL . 'assets/editor/css/slotFields.css',
				array(),
				filemtime( $editor_css )
			);
		}

		wp_set_script_translations( 'wr-sm-slot-fields-editor', 'wr-slot-manager', WR_SM_PLUGIN_DIR . 'languages' );

		register_block_type(
			__DIR__ . '/block.json',
			array(
				'editor_style' => 'wr-sm-slot-fields-editor-style',
			)
		);
	}
}
