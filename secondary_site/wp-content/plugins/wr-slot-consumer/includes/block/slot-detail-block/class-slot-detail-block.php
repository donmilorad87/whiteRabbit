<?php
/**
 * Gutenberg block for a dedicated slot detail page.
 * Shows a single slot with full details and CSS custom property styling.
 *
 * @package WiseRabbit\SlotConsumer\Block\SlotDetailBlock
 */

namespace WiseRabbit\SlotConsumer\Block\SlotDetailBlock;

use WiseRabbit\SlotConsumer\Cache\SlotTransientCache;
use WiseRabbit\SlotConsumer\Traits\TemplateLoaderTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SlotDetailBlock
 */
class SlotDetailBlock {

	use TemplateLoaderTrait;

	/**
	 * Register the block type.
	 */
	public function register(): void {
		$editor_js = WR_SC_PLUGIN_DIR . 'assets/blocks/js/slot-detail.js';

		if ( file_exists( $editor_js ) ) {
			wp_register_script(
				'wr-sc-slot-detail-editor',
				WR_SC_PLUGIN_URL . 'assets/blocks/js/slot-detail.js',
				array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render', 'wp-data', 'wp-api-fetch' ),
				filemtime( $editor_js ),
				true
			);
		}

		$frontend_css = WR_SC_PLUGIN_DIR . 'assets/blocks/css/frontend.css';
		if ( file_exists( $frontend_css ) ) {
			wp_register_style(
				'wr-sc-slot-detail-style',
				WR_SC_PLUGIN_URL . 'assets/blocks/css/frontend.css',
				array(),
				filemtime( $frontend_css )
			);
		}

		wp_set_script_translations( 'wr-sc-slot-detail-editor', 'wr-slot-consumer', WR_SC_PLUGIN_DIR . 'languages' );

		register_block_type(
			__DIR__ . '/block.json',
			array(
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Provide the slot list as a REST endpoint for the editor dropdown.
	 */
	public function register_rest_routes(): void {
		register_rest_route(
			'wr-slot-consumer/v1',
			'/slot-list',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_slot_list' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * Return a simplified slot list for the editor dropdown.
	 */
	public function get_slot_list(): \WP_REST_Response {
		$cache = new SlotTransientCache();
		$slots = $cache->get_all_slots();

		$list = array_map(
			function ( array $slot ): array {
				return array(
					'value' => (int) $slot['id'],
					'label' => $slot['title'] ?? __( 'Untitled', 'wr-slot-consumer' ),
				);
			},
			$slots
		);

		return new \WP_REST_Response( $list, 200 );
	}

	/**
	 * Server-side render callback.
	 */
	public function render( array $attributes ): string {
		$defaults = array(
			'slotId'            => 0,
			'showImage'         => true,
			'showRating'        => true,
			'showDescription'   => true,
			'showProvider'      => true,
			'showRtp'           => true,
			'showWager'         => true,
			'bgColor'           => '',
			'bgPadding'         => 0,
			'bgBorderRadius'    => 12,
			'titleColor'        => '',
			'titleFontSize'     => 0,
			'titleFontWeight'   => '750',
			'descColor'         => '',
			'descFontSize'      => 0,
			'metaBgColor'       => '',
			'metaBorderColor'   => '',
			'metaBorderRadius'  => 12,
			'metaLabelColor'    => '',
			'metaValueColor'    => '',
			'starsColor'        => '#f0b429',
			'starsHalfColor'    => '#e2c773',
			'starsBorderColor'  => '',
			'starsFontSize'     => 20,
			'imageBorderRadius' => 12,
			'imageMaxHeight'    => 420,
		);

		$a = wp_parse_args( $attributes, $defaults );

		$slot_id = (int) $a['slotId'];

		// Fallback: read from URL query param if no slotId attribute set.
		if ( ! $slot_id && isset( $_GET['slot_detail'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$slot_id = (int) sanitize_text_field( wp_unslash( $_GET['slot_detail'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$show_image       = (bool) $a['showImage'];
		$show_rating      = (bool) $a['showRating'];
		$show_description = (bool) $a['showDescription'];
		$show_provider    = (bool) $a['showProvider'];
		$show_rtp         = (bool) $a['showRtp'];
		$show_wager       = (bool) $a['showWager'];

		$cache = new SlotTransientCache();
		$slot  = null;

		if ( $slot_id > 0 ) {
			$slots = $cache->get_all_slots();
			foreach ( $slots as $s ) {
				if ( (int) $s['id'] === $slot_id ) {
					$slot = $s;
					break;
				}
			}
		}

		$style_vars = $this->build_css_vars( $a );

		return $this->render_template( 'templates/blocks/slot-detail.php', compact(
			'slot', 'show_image', 'show_rating', 'show_description',
			'show_provider', 'show_rtp', 'show_wager', 'style_vars'
		) );
	}

	/**
	 * Build CSS custom properties from attributes.
	 */
	private function build_css_vars( array $a ): string {
		$vars = array();

		if ( ! empty( $a['bgColor'] ) ) {
			$vars['--sp-bg'] = $a['bgColor'];
		}
		if ( (int) $a['bgPadding'] > 0 ) {
			$vars['--sp-pad'] = (int) $a['bgPadding'] . 'px';
		}
		$vars['--sp-radius']     = (int) $a['bgBorderRadius'] . 'px';

		if ( ! empty( $a['titleColor'] ) ) {
			$vars['--sp-title-color'] = $a['titleColor'];
		}
		if ( (int) $a['titleFontSize'] > 0 ) {
			$vars['--sp-title-size'] = (int) $a['titleFontSize'] . 'px';
		}
		$vars['--sp-title-weight'] = $a['titleFontWeight'];

		if ( ! empty( $a['descColor'] ) ) {
			$vars['--sp-desc-color'] = $a['descColor'];
		}
		if ( (int) $a['descFontSize'] > 0 ) {
			$vars['--sp-desc-size'] = (int) $a['descFontSize'] . 'px';
		}

		if ( ! empty( $a['metaBgColor'] ) ) {
			$vars['--sp-meta-bg'] = $a['metaBgColor'];
		}
		if ( ! empty( $a['metaBorderColor'] ) ) {
			$vars['--sp-meta-border'] = $a['metaBorderColor'];
		}
		$vars['--sp-meta-radius'] = (int) $a['metaBorderRadius'] . 'px';
		if ( ! empty( $a['metaLabelColor'] ) ) {
			$vars['--sp-meta-label'] = $a['metaLabelColor'];
		}
		if ( ! empty( $a['metaValueColor'] ) ) {
			$vars['--sp-meta-value'] = $a['metaValueColor'];
		}

		$vars['--sp-stars-color'] = $a['starsColor'];
		$vars['--sp-stars-half']  = $a['starsHalfColor'];
		$vars['--sp-stars-size']  = (int) $a['starsFontSize'] . 'px';
		if ( ! empty( $a['starsBorderColor'] ) ) {
			$vars['--sp-stars-border'] = $a['starsBorderColor'];
		}

		$vars['--sp-img-radius'] = (int) $a['imageBorderRadius'] . 'px';
		$vars['--sp-img-max-h']  = (int) $a['imageMaxHeight'] . 'px';

		$parts = array();
		foreach ( $vars as $prop => $val ) {
			$parts[] = esc_attr( $prop ) . ':' . esc_attr( $val );
		}

		return implode( ';', $parts );
	}
}
