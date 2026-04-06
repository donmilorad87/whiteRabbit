<?php
/**
 * Gutenberg block registration for slot grid.
 *
 * @package WiseRabbit\SlotConsumer\Block\SlotGridBlock
 */

namespace WiseRabbit\SlotConsumer\Block\SlotGridBlock;

use WiseRabbit\SlotConsumer\Cache\SlotTransientCache;
use WiseRabbit\SlotConsumer\Sync\SlotSyncManager;
use WiseRabbit\SlotConsumer\Traits\LoggerTrait;
use WiseRabbit\SlotConsumer\Traits\TemplateLoaderTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SlotGridBlock
 */
class SlotGridBlock {

	use LoggerTrait;
	use TemplateLoaderTrait;

	/**
	 * Default block attributes.
	 *
	 * @var array
	 */
	private const DEFAULTS = array(
		'columns'                => 3,
		'limit'                  => 12,
		'sortBy'                 => 'recent',
		'paginationMode'         => 'off',
		'loadMoreType'           => 'button',
		'linkMode'               => 'page',
		'detailPageUrl'          => '',
		'moreInfoLabel'          => 'More Info',
		'showMoreInfo'           => true,
		// Block background.
		'blockBgEnabled'         => false,
		'blockBgColor'           => '#ffffff',
		'blockBgCustom'          => false,
		'blockBgCustomValue'     => '',
		'blockPadding'           => 0,
		'blockBorderRadius'      => 0,
		// Card styling.
		'itemBgEnabled'          => false,
		'itemBgColor'            => '#ffffff',
		'itemBgCustom'           => false,
		'itemBgCustomValue'      => '',
		'itemBorderColor'        => '',
		'itemShadowX'            => 0,
		'itemShadowY'            => 1,
		'itemShadowBlur'         => 3,
		'itemShadowSpread'       => 0,
		'itemShadowColor'        => 'rgba(0,0,0,0.06)',
		'itemShadowInset'        => false,
		'itemCustomShadow'       => false,
		'itemCustomShadowValue'  => '',
		'itemPadding'            => 0,
		'innerPadding'           => 20,
		// Title.
		'titleFontSize'          => 16,
		'titleColor'             => '',
		'titleLineHeight'        => 1.35,
		'titleFontWeight'        => '650',
		'titleFontStyle'         => 'normal',
		// Provider/desc.
		'descColor'              => '',
		'descFontSize'           => 13,
		'descFontWeight'         => '500',
		'descFontStyle'          => 'normal',
		// Stars.
		'starsGap'               => 2,
		'starsColor'             => '#f0b429',
		'starsHalfColor'         => '#e2c773',
		'starsInfoColor'         => '',
		'starsInfoFontSize'      => 13,
		'starsInfoFontWeight'    => '500',
		'starsBorderColor'       => '',
		'starsFontSize'          => 18,
		// Card hover.
		'itemHoverBgColor'       => '',
		'itemHoverBorderColor'   => '',
		'itemHoverShadow'        => '',
		'itemHoverTranslateY'    => -4,
		// Button.
		'btnBgColor'             => '',
		'btnTextColor'           => '',
		'btnBorderRadius'        => 8,
		'btnBorderColor'         => '',
		'btnFontWeight'          => '600',
		'btnShadowX'             => 0,
		'btnShadowY'             => 0,
		'btnShadowBlur'          => 0,
		'btnShadowSpread'        => 0,
		'btnShadowColor'         => '',
		'btnShadowInset'         => false,
		'btnCustomShadow'        => false,
		'btnCustomShadowValue'   => '',
		'btnTextShadowX'         => 0,
		'btnTextShadowY'         => 0,
		'btnTextShadowBlur'      => 0,
		'btnTextShadowColor'     => '',
		'btnHoverBgColor'        => '',
		'btnHoverTextColor'      => '',
		'btnHoverBorderColor'    => '',
		'btnHoverShadow'         => '',
	);

	/**
	 * Register the block type.
	 *
	 * @return void
	 */
	public function register(): void {
		$editor_js = WR_SC_PLUGIN_DIR . 'assets/blocks/js/slot-grid.js';

		if ( file_exists( $editor_js ) ) {
			wp_register_script(
				'wr-sc-slot-grid-editor',
				WR_SC_PLUGIN_URL . 'assets/blocks/js/slot-grid.js',
				array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
				filemtime( $editor_js ),
				true
			);
		}

		$frontend_css = WR_SC_PLUGIN_DIR . 'assets/blocks/css/frontend.css';
		if ( file_exists( $frontend_css ) ) {
			wp_register_style(
				'wr-sc-slot-grid-style',
				WR_SC_PLUGIN_URL . 'assets/blocks/css/frontend.css',
				array(),
				filemtime( $frontend_css )
			);
		}

		$frontend_js = WR_SC_PLUGIN_DIR . 'assets/blocks/js/slot-grid-frontend.js';
		if ( file_exists( $frontend_js ) ) {
			wp_register_script(
				'wr-sc-slot-grid-frontend',
				WR_SC_PLUGIN_URL . 'assets/blocks/js/slot-grid-frontend.js',
				array(),
				filemtime( $frontend_js ),
				true
			);
		}

		wp_set_script_translations( 'wr-sc-slot-grid-editor', 'wr-slot-consumer', WR_SC_PLUGIN_DIR . 'languages' );

		register_block_type(
			__DIR__ . '/block.json',
			array(
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Server-side render callback.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render( array $attributes ): string {
		$a = wp_parse_args( $attributes, self::DEFAULTS );

		$per_page        = (int) $a['limit'];
		$columns         = (int) $a['columns'];
		$link_mode       = $a['linkMode'];
		$detail_page_url = $a['detailPageUrl'];
		$show_more_info  = (bool) $a['showMoreInfo'];
		$pagination_mode = $a['paginationMode'];
		$load_more_type  = $a['loadMoreType'];
		$more_info_label = $a['moreInfoLabel'] ?: __( 'More Info', 'wr-slot-consumer' );

		$star_full_color   = $a['starsColor'] ?: '#f0b429';
		$star_half_color   = $a['starsHalfColor'] ?: '#e2c773';
		$star_empty_color  = '#d4d4d8';
		$star_border_color = $a['starsBorderColor'] ?: '';
		$star_font_size    = ( (int) $a['starsFontSize'] ?: 18 ) . 'px';

		$style_vars = $this->build_css_vars( $a );

		// Auto-sync when transient cache is expired.
		$cache = new SlotTransientCache();
		if ( $cache->is_expired() ) {
			$this->log_info( 'BLOCK RENDER: ' .  'Transient expired — triggering auto-sync from primary site.' );
			$sync   = new SlotSyncManager();
			$result = $sync->sync( 'block' );
			if ( is_wp_error( $result ) ) {
				$this->log_info( 'BLOCK RENDER: ' .  'Auto-sync FAILED — ' . $result->get_error_message() );
			} else {
				$this->log_info( 'BLOCK RENDER: ' .  'Auto-sync OK — ' . $result . ' slots fetched.' );
			}
		} else {
			$this->log_info( 'BLOCK RENDER: ' .  'Transient valid — serving from cache.' );
		}

		$all_slots = $this->get_sorted_slots( $a['sortBy'] );

		if ( empty( $all_slots ) ) {
			$slots           = array();
			$total_pages     = 0;
			$current_page    = 1;
			$remaining_slots = array();
		} else {
			$page_data       = $this->paginate( $all_slots, $per_page, $pagination_mode );
			$slots           = $page_data['slots'];
			$total_pages     = $page_data['total_pages'];
			$current_page    = $page_data['current_page'];
			$remaining_slots = $page_data['remaining'];

			if ( 'loadmore' === $pagination_mode || 'popup' === $link_mode ) {
				wp_enqueue_script( 'wr-sc-slot-grid-frontend' );
			}
		}

		return $this->render_template( 'templates/blocks/slot-grid.php', compact(
			'slots', 'columns', 'link_mode', 'detail_page_url', 'show_more_info',
			'more_info_label', 'style_vars', 'pagination_mode', 'load_more_type', 'per_page',
			'total_pages', 'current_page', 'remaining_slots',
			'star_full_color', 'star_half_color', 'star_empty_color',
			'star_border_color', 'star_font_size'
		) );
	}

	/**
	 * Fetch and sort all slots from cache.
	 *
	 * @param string $sort_by Sort method (recent|random).
	 * @return array
	 */
	private function get_sorted_slots( string $sort_by ): array {
		$cache = new SlotTransientCache();
		$slots = $cache->get_all_slots();

		if ( empty( $slots ) ) {
			return array();
		}

		if ( 'random' === $sort_by ) {
			shuffle( $slots );
			return $slots;
		}

		usort(
			$slots,
			function ( $x, $y ) {
				return strtotime( $y['updated_at'] ?? '' ) - strtotime( $x['updated_at'] ?? '' );
			}
		);

		return $slots;
	}

	/**
	 * Slice slots into the correct page.
	 *
	 * @param array  $all_slots      All slots.
	 * @param int    $per_page       Items per page.
	 * @param string $pagination_mode Pagination mode.
	 * @return array{slots: array, total_pages: int, current_page: int, remaining: array}
	 */
	private function paginate( array $all_slots, int $per_page, string $pagination_mode ): array {
		$total       = count( $all_slots );
		$total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;

		if ( 'pagination' === $pagination_mode ) {
			$current_page = isset( $_GET['sg_page'] ) ? max( 1, (int) sanitize_text_field( wp_unslash( $_GET['sg_page'] ) ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$current_page = min( $current_page, $total_pages );
			$offset       = ( $current_page - 1 ) * $per_page;

			return array(
				'slots'        => array_slice( $all_slots, $offset, $per_page ),
				'total_pages'  => $total_pages,
				'current_page' => $current_page,
				'remaining'    => array(),
			);
		}

		if ( 'loadmore' === $pagination_mode ) {
			return array(
				'slots'        => array_slice( $all_slots, 0, $per_page ),
				'total_pages'  => $total_pages,
				'current_page' => 1,
				'remaining'    => array_slice( $all_slots, $per_page ),
			);
		}

		return array(
			'slots'        => $per_page > 0 ? array_slice( $all_slots, 0, $per_page ) : $all_slots,
			'total_pages'  => 1,
			'current_page' => 1,
			'remaining'    => array(),
		);
	}

	/**
	 * Build CSS custom properties string from attributes.
	 *
	 * @param array $a Parsed attributes.
	 * @return string Inline style string.
	 */
	private function build_css_vars( array $a ): string {
		$vars = array();

		// Layout.
		$vars['--sg-columns']   = (int) $a['columns'];
		$vars['--sg-item-pad']  = (int) $a['itemPadding'] . 'px';
		$vars['--sg-inner-pad'] = (int) $a['innerPadding'] . 'px';

		// Block background.
		$this->add_background_vars( $vars, $a, 'block' );
		$this->add_positive_px( $vars, '--sg-block-pad', $a['blockPadding'] );
		$this->add_positive_px( $vars, '--sg-block-radius', $a['blockBorderRadius'] );

		// Card background.
		if ( ! empty( $a['itemBgEnabled'] ) ) {
			$this->add_background_vars( $vars, $a, 'item' );
		} else {
			$vars['--sg-item-bg'] = 'transparent';
		}

		// Item shadow.
		$vars['--sg-item-shadow'] = $this->build_shadow(
			$a, 'item', 'rgba(0,0,0,0.06)'
		);

		$this->add_non_empty( $vars, '--sg-item-border', $a['itemBorderColor'] );

		// Title.
		$vars['--sg-title-size']   = (int) $a['titleFontSize'] . 'px';
		$vars['--sg-title-lh']     = (float) $a['titleLineHeight'];
		$vars['--sg-title-weight'] = $a['titleFontWeight'];
		$vars['--sg-title-style']  = $a['titleFontStyle'];
		$this->add_non_empty( $vars, '--sg-title-color', $a['titleColor'] );

		// Provider/desc.
		$vars['--sg-desc-size']   = (int) $a['descFontSize'] . 'px';
		$vars['--sg-desc-weight'] = $a['descFontWeight'];
		$vars['--sg-desc-style']  = $a['descFontStyle'];
		$this->add_non_empty( $vars, '--sg-desc-color', $a['descColor'] );

		// Stars.
		$vars['--sg-stars-gap']         = (int) $a['starsGap'] . 'px';
		$vars['--sg-stars-color']       = $a['starsColor'];
		$vars['--sg-stars-half']        = $a['starsHalfColor'];
		$vars['--sg-stars-info-size']   = (int) $a['starsInfoFontSize'] . 'px';
		$vars['--sg-stars-info-weight'] = $a['starsInfoFontWeight'];
		$vars['--sg-stars-size']        = ( (int) $a['starsFontSize'] ?: 18 ) . 'px';
		$this->add_non_empty( $vars, '--sg-stars-info-color', $a['starsInfoColor'] );
		$this->add_non_empty( $vars, '--sg-stars-border', $a['starsBorderColor'] );

		// Card hover.
		$vars['--sg-item-hover-y'] = (int) ( $a['itemHoverTranslateY'] ?? -4 ) . 'px';
		$this->add_non_empty( $vars, '--sg-item-hover-bg', $a['itemHoverBgColor'] );
		$this->add_non_empty( $vars, '--sg-item-hover-border', $a['itemHoverBorderColor'] );
		$this->add_non_empty( $vars, '--sg-item-hover-shadow', $a['itemHoverShadow'] );

		// Button.
		$vars['--sg-btn-radius'] = (int) $a['btnBorderRadius'] . 'px';
		$vars['--sg-btn-weight'] = $a['btnFontWeight'];
		$this->add_non_empty( $vars, '--sg-btn-bg', $a['btnBgColor'] );
		$this->add_non_empty( $vars, '--sg-btn-color', $a['btnTextColor'] );
		$this->add_non_empty( $vars, '--sg-btn-border', $a['btnBorderColor'] );

		// Button shadow.
		$btn_shadow = $this->build_shadow( $a, 'btn', 'transparent' );
		if ( '0px 0px 0px 0px transparent' !== $btn_shadow && 'inset 0px 0px 0px 0px transparent' !== $btn_shadow ) {
			$vars['--sg-btn-shadow'] = $btn_shadow;
		}

		// Button text shadow.
		$ts = (int) $a['btnTextShadowX'] . 'px ' . (int) $a['btnTextShadowY'] . 'px ' . (int) $a['btnTextShadowBlur'] . 'px ' . ( $a['btnTextShadowColor'] ?: 'transparent' );
		if ( '0px 0px 0px transparent' !== $ts ) {
			$vars['--sg-btn-text-shadow'] = $ts;
		}

		// Button hover.
		$this->add_non_empty( $vars, '--sg-btn-hover-bg', $a['btnHoverBgColor'] );
		$this->add_non_empty( $vars, '--sg-btn-hover-color', $a['btnHoverTextColor'] );
		$this->add_non_empty( $vars, '--sg-btn-hover-border', $a['btnHoverBorderColor'] );
		$this->add_non_empty( $vars, '--sg-btn-hover-shadow', $a['btnHoverShadow'] );

		$parts = array();
		foreach ( $vars as $prop => $val ) {
			$parts[] = esc_attr( $prop ) . ':' . esc_attr( $val );
		}

		return implode( ';', $parts );
	}

	/**
	 * Add a CSS variable only when the value is non-empty.
	 *
	 * @param array  $vars  Variables array (by reference).
	 * @param string $prop  CSS custom property name.
	 * @param string $value Value to check.
	 * @return void
	 */
	private function add_non_empty( array &$vars, string $prop, string $value ): void {
		if ( ! empty( $value ) ) {
			$vars[ $prop ] = $value;
		}
	}

	/**
	 * Add a positive pixel value as CSS variable.
	 *
	 * @param array  $vars Variables array (by reference).
	 * @param string $prop CSS custom property name.
	 * @param mixed  $value Raw numeric value.
	 * @return void
	 */
	private function add_positive_px( array &$vars, string $prop, mixed $value ): void {
		if ( (int) $value > 0 ) {
			$vars[ $prop ] = (int) $value . 'px';
		}
	}

	/**
	 * Add background CSS variable for block or item.
	 *
	 * @param array  $vars   Variables array (by reference).
	 * @param array  $a      Parsed attributes.
	 * @param string $prefix 'block' or 'item'.
	 * @return void
	 */
	private function add_background_vars( array &$vars, array $a, string $prefix ): void {
		$enabled_key = $prefix . 'BgEnabled';
		$custom_key  = $prefix . 'BgCustom';
		$custom_val  = $prefix . 'BgCustomValue';
		$color_key   = $prefix . 'BgColor';
		$css_prop    = 'block' === $prefix ? '--sg-block-bg' : '--sg-item-bg';

		if ( empty( $a[ $enabled_key ] ) ) {
			return;
		}

		if ( ! empty( $a[ $custom_key ] ) && ! empty( $a[ $custom_val ] ) ) {
			$vars[ $css_prop ] = $a[ $custom_val ];
		} elseif ( ! empty( $a[ $color_key ] ) ) {
			$vars[ $css_prop ] = $a[ $color_key ];
		}
	}

	/**
	 * Build a box-shadow CSS value from prefixed attributes.
	 *
	 * @param array  $a       Parsed attributes.
	 * @param string $prefix  Attribute prefix (item|btn).
	 * @param string $default Default shadow color.
	 * @return string CSS box-shadow value.
	 */
	private function build_shadow( array $a, string $prefix, string $default ): string {
		if ( $a[ $prefix . 'CustomShadow' ] && ! empty( $a[ $prefix . 'CustomShadowValue' ] ) ) {
			return $a[ $prefix . 'CustomShadowValue' ];
		}

		$inset = $a[ $prefix . 'ShadowInset' ] ? 'inset ' : '';

		return $inset
			. (int) $a[ $prefix . 'ShadowX' ] . 'px '
			. (int) $a[ $prefix . 'ShadowY' ] . 'px '
			. (int) $a[ $prefix . 'ShadowBlur' ] . 'px '
			. (int) $a[ $prefix . 'ShadowSpread' ] . 'px '
			. ( $a[ $prefix . 'ShadowColor' ] ?: $default );
	}

}
