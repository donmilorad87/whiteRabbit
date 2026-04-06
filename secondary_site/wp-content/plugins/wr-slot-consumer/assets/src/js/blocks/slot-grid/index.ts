/**
 * Slots Grid Gutenberg block with full styling controls + live preview.
 *
 * @package WiseRabbit\SlotConsumer
 */

import '../../../scss/blocks/slot-grid/editor.scss';

const { registerBlockType } = wp.blocks;
const { createElement: el, Fragment } = wp.element;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const {
	PanelBody, RangeControl, SelectControl, TextControl,
	ToggleControl, ColorPicker, Disabled, __experimentalBoxControl: BoxControl,
} = wp.components;
const { __ } = wp.i18n;
const ServerSideRender = wp.serverSideRender;

/* ─── helpers ─── */

function ColorField( label, value, onChange ) {
	return el( PanelBody, { title: label, initialOpen: false },
		el( ColorPicker, { color: value || '', onChange: onChange, enableAlpha: true } )
	);
}

function FontStyleSelect( label, weight, style, onWeightChange, onStyleChange ) {
	return el( Fragment, {},
		el( SelectControl, {
			label: label + ' ' + __( 'Weight', 'wr-slot-consumer' ),
			value: weight,
			options: [
				{ label: 'Normal (400)', value: '400' },
				{ label: 'Medium (500)', value: '500' },
				{ label: 'Semi-Bold (600)', value: '600' },
				{ label: 'Bold (650)', value: '650' },
				{ label: 'Extra Bold (700)', value: '700' },
			],
			onChange: onWeightChange,
			__next40pxDefaultSize: true,
			__nextHasNoMarginBottom: true,
		}),
		el( SelectControl, {
			label: label + ' ' + __( 'Style', 'wr-slot-consumer' ),
			value: style,
			options: [
				{ label: 'Normal', value: 'normal' },
				{ label: 'Italic', value: 'italic' },
			],
			onChange: onStyleChange,
			__next40pxDefaultSize: true,
			__nextHasNoMarginBottom: true,
		})
	);
}

function ShadowControls( prefix, attrs, setAttributes ) {
	const isCustom = attrs[ prefix + 'CustomShadow' ];
	return el( Fragment, {},
		el( ToggleControl, {
			label: __( 'Custom Shadow (raw CSS)', 'wr-slot-consumer' ),
			checked: isCustom,
			onChange: ( v ) => setAttributes( { [ prefix + 'CustomShadow' ]: v } ),
			__nextHasNoMarginBottom: true,
		}),
		isCustom
			? el( TextControl, {
				label: __( 'Box Shadow CSS', 'wr-slot-consumer' ),
				help: 'e.g. 0px 4px 10px rgba(0,0,0,0.1), inset 0 1px 2px red',
				value: attrs[ prefix + 'CustomShadowValue' ] || '',
				onChange: ( v ) => setAttributes( { [ prefix + 'CustomShadowValue' ]: v } ),
				__next40pxDefaultSize: true,
				__nextHasNoMarginBottom: true,
			})
			: el( Fragment, {},
				el( RangeControl, { label: 'X', value: attrs[ prefix + 'ShadowX' ], onChange: ( v ) => setAttributes( { [ prefix + 'ShadowX' ]: v } ), min: -50, max: 50, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
				el( RangeControl, { label: 'Y', value: attrs[ prefix + 'ShadowY' ], onChange: ( v ) => setAttributes( { [ prefix + 'ShadowY' ]: v } ), min: -50, max: 50, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
				el( RangeControl, { label: __( 'Blur', 'wr-slot-consumer' ), value: attrs[ prefix + 'ShadowBlur' ], onChange: ( v ) => setAttributes( { [ prefix + 'ShadowBlur' ]: v } ), min: 0, max: 100, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
				el( RangeControl, { label: __( 'Spread', 'wr-slot-consumer' ), value: attrs[ prefix + 'ShadowSpread' ], onChange: ( v ) => setAttributes( { [ prefix + 'ShadowSpread' ]: v } ), min: -50, max: 50, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
				el( ToggleControl, { label: __( 'Inset', 'wr-slot-consumer' ), checked: attrs[ prefix + 'ShadowInset' ], onChange: ( v ) => setAttributes( { [ prefix + 'ShadowInset' ]: v } ), __nextHasNoMarginBottom: true }),
				ColorField( __( 'Shadow Color', 'wr-slot-consumer' ), attrs[ prefix + 'ShadowColor' ], ( v ) => setAttributes( { [ prefix + 'ShadowColor' ]: v } ) )
			)
	);
}

/* ─── block ─── */

registerBlockType( 'wr-slot-consumer/slot-grid', {
	edit: function EditSlotGrid( props ) {
		const { attributes, setAttributes } = props;
		const a = attributes;
		const blockProps = useBlockProps();

		return el(
			'div',
			blockProps,

			el( InspectorControls, {},

				/* Display */
				el( PanelBody, { title: __( 'Display', 'wr-slot-consumer' ) },
					el( RangeControl, { label: __( 'Columns', 'wr-slot-consumer' ), value: a.columns, onChange: ( v ) => setAttributes( { columns: v } ), min: 1, max: 6, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
					el( RangeControl, { label: __( 'Items Per Page', 'wr-slot-consumer' ), help: __( 'Number of slots shown per page/load.', 'wr-slot-consumer' ), value: a.limit, onChange: ( v ) => setAttributes( { limit: v } ), min: 1, max: 100, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
					el( SelectControl, { label: __( 'Sort By', 'wr-slot-consumer' ), value: a.sortBy, options: [ { label: 'Recent', value: 'recent' }, { label: 'Random', value: 'random' } ], onChange: ( v ) => setAttributes( { sortBy: v } ), __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
					el( SelectControl, {
						label: __( 'Pagination', 'wr-slot-consumer' ),
						value: a.paginationMode,
						options: [
							{ label: __( 'Off', 'wr-slot-consumer' ), value: 'off' },
							{ label: __( 'Page Numbers', 'wr-slot-consumer' ), value: 'pagination' },
							{ label: __( 'Load More', 'wr-slot-consumer' ), value: 'loadmore' },
						],
						onChange: ( v ) => setAttributes( { paginationMode: v } ),
						__next40pxDefaultSize: true,
						__nextHasNoMarginBottom: true,
					}),
					a.paginationMode === 'loadmore' && el( SelectControl, {
						label: __( 'Load More Type', 'wr-slot-consumer' ),
						value: a.loadMoreType,
						options: [
							{ label: __( 'Button', 'wr-slot-consumer' ), value: 'button' },
							{ label: __( 'Infinite Scroll', 'wr-slot-consumer' ), value: 'infinite' },
						],
						onChange: ( v ) => setAttributes( { loadMoreType: v } ),
						__next40pxDefaultSize: true,
						__nextHasNoMarginBottom: true,
					})
				),

				/* Block Background */
				el( PanelBody, { title: __( 'Block Background', 'wr-slot-consumer' ), initialOpen: false },
					el( RangeControl, { label: __( 'Block Padding', 'wr-slot-consumer' ), value: a.blockPadding, onChange: ( v ) => setAttributes( { blockPadding: v } ), min: 0, max: 80, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
					el( RangeControl, { label: __( 'Block Border Radius', 'wr-slot-consumer' ), value: a.blockBorderRadius, onChange: ( v ) => setAttributes( { blockBorderRadius: v } ), min: 0, max: 40, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
					el( ToggleControl, { label: __( 'Enable Background', 'wr-slot-consumer' ), checked: a.blockBgEnabled, onChange: ( v ) => setAttributes( { blockBgEnabled: v } ), __nextHasNoMarginBottom: true }),
					a.blockBgEnabled && el( Fragment, {},
						el( ToggleControl, { label: __( 'Custom CSS (gradient etc.)', 'wr-slot-consumer' ), checked: a.blockBgCustom, onChange: ( v ) => setAttributes( { blockBgCustom: v } ), __nextHasNoMarginBottom: true }),
						a.blockBgCustom
							? el( TextControl, { label: __( 'CSS Background', 'wr-slot-consumer' ), help: 'e.g. linear-gradient(135deg, #667eea, #764ba2)', value: a.blockBgCustomValue, onChange: ( v ) => setAttributes( { blockBgCustomValue: v } ), __next40pxDefaultSize: true, __nextHasNoMarginBottom: true })
							: ColorField( __( 'Background Color', 'wr-slot-consumer' ), a.blockBgColor, ( v ) => setAttributes( { blockBgColor: v } ) )
					)
				),

				/* Link / More Info Mode */
				el( PanelBody, { title: __( 'More Info Action', 'wr-slot-consumer' ), initialOpen: false },
					el( SelectControl, {
						label: __( 'Click Action', 'wr-slot-consumer' ),
						value: a.linkMode,
						options: [
							{ label: __( 'Open Detail Page', 'wr-slot-consumer' ), value: 'page' },
							{ label: __( 'Show Popup', 'wr-slot-consumer' ), value: 'popup' },
						],
						onChange: ( v ) => setAttributes( { linkMode: v } ),
						__next40pxDefaultSize: true,
						__nextHasNoMarginBottom: true,
					}),
					a.linkMode === 'page' && el( TextControl, { label: __( 'Detail Page URL', 'wr-slot-consumer' ), help: __( 'Slot ID appended as ?slot_detail=ID', 'wr-slot-consumer' ), value: a.detailPageUrl, onChange: ( v ) => setAttributes( { detailPageUrl: v } ), placeholder: '/slot-detail/', __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
					a.linkMode === 'popup' && el( 'p', { style: { color: '#71717a', fontSize: '13px' } }, __( 'Clicking "More Info" will open a popup with all slot details.', 'wr-slot-consumer' ) )
				),

				/* Card / Item */
				el( PanelBody, { title: __( 'Card Styling', 'wr-slot-consumer' ), initialOpen: false },
					el( RangeControl, { label: __( 'Outer Padding', 'wr-slot-consumer' ), value: a.itemPadding, onChange: ( v ) => setAttributes( { itemPadding: v } ), min: 0, max: 40, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
					el( RangeControl, { label: __( 'Inner Padding', 'wr-slot-consumer' ), value: a.innerPadding, onChange: ( v ) => setAttributes( { innerPadding: v } ), min: 0, max: 60, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
					el( ToggleControl, { label: __( 'Card Background', 'wr-slot-consumer' ), checked: a.itemBgEnabled, onChange: ( v ) => setAttributes( { itemBgEnabled: v } ), __nextHasNoMarginBottom: true }),
					a.itemBgEnabled && el( Fragment, {},
						el( ToggleControl, { label: __( 'Custom CSS (gradient etc.)', 'wr-slot-consumer' ), checked: a.itemBgCustom, onChange: ( v ) => setAttributes( { itemBgCustom: v } ), __nextHasNoMarginBottom: true }),
						a.itemBgCustom
							? el( TextControl, { label: __( 'CSS Background', 'wr-slot-consumer' ), help: 'e.g. linear-gradient(135deg, #667eea, #764ba2)', value: a.itemBgCustomValue, onChange: ( v ) => setAttributes( { itemBgCustomValue: v } ), __next40pxDefaultSize: true, __nextHasNoMarginBottom: true })
							: ColorField( __( 'Background Color', 'wr-slot-consumer' ), a.itemBgColor, ( v ) => setAttributes( { itemBgColor: v } ) )
					),
					ColorField( __( 'Border Color', 'wr-slot-consumer' ), a.itemBorderColor, ( v ) => setAttributes( { itemBorderColor: v } ) ),
					ShadowControls( 'item', a, setAttributes )
				),

				/* Card Hover */
				el( PanelBody, { title: __( 'Card Hover', 'wr-slot-consumer' ), initialOpen: false },
					el( RangeControl, { label: __( 'Lift (translateY px)', 'wr-slot-consumer' ), value: a.itemHoverTranslateY, onChange: ( v ) => setAttributes( { itemHoverTranslateY: v } ), min: -20, max: 0, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
					ColorField( __( 'Hover Background', 'wr-slot-consumer' ), a.itemHoverBgColor, ( v ) => setAttributes( { itemHoverBgColor: v } ) ),
					ColorField( __( 'Hover Border Color', 'wr-slot-consumer' ), a.itemHoverBorderColor, ( v ) => setAttributes( { itemHoverBorderColor: v } ) ),
					el( TextControl, { label: __( 'Hover Shadow CSS', 'wr-slot-consumer' ), help: 'e.g. 0 12px 28px rgba(0,0,0,0.1)', value: a.itemHoverShadow, onChange: ( v ) => setAttributes( { itemHoverShadow: v } ), __next40pxDefaultSize: true, __nextHasNoMarginBottom: true })
				),

				/* Title */
				el( PanelBody, { title: __( 'Title', 'wr-slot-consumer' ), initialOpen: false },
					el( RangeControl, { label: __( 'Font Size (px)', 'wr-slot-consumer' ), value: a.titleFontSize, onChange: ( v ) => setAttributes( { titleFontSize: v } ), min: 10, max: 40, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
					el( RangeControl, { label: __( 'Line Height', 'wr-slot-consumer' ), value: a.titleLineHeight, onChange: ( v ) => setAttributes( { titleLineHeight: v } ), min: 1, max: 2.5, step: 0.05, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
					ColorField( __( 'Title Color', 'wr-slot-consumer' ), a.titleColor, ( v ) => setAttributes( { titleColor: v } ) ),
					FontStyleSelect( __( 'Title', 'wr-slot-consumer' ), a.titleFontWeight, a.titleFontStyle, ( v ) => setAttributes( { titleFontWeight: v } ), ( v ) => setAttributes( { titleFontStyle: v } ) )
				),

				/* Provider / Description */
				el( PanelBody, { title: __( 'Provider Text', 'wr-slot-consumer' ), initialOpen: false },
					el( RangeControl, { label: __( 'Font Size (px)', 'wr-slot-consumer' ), value: a.descFontSize, onChange: ( v ) => setAttributes( { descFontSize: v } ), min: 10, max: 30, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
					ColorField( __( 'Color', 'wr-slot-consumer' ), a.descColor, ( v ) => setAttributes( { descColor: v } ) ),
					FontStyleSelect( __( 'Provider', 'wr-slot-consumer' ), a.descFontWeight, a.descFontStyle, ( v ) => setAttributes( { descFontWeight: v } ), ( v ) => setAttributes( { descFontStyle: v } ) )
				),

				/* Stars */
				el( PanelBody, { title: __( 'Stars', 'wr-slot-consumer' ), initialOpen: false },
					el( RangeControl, { label: __( 'Star Size (px)', 'wr-slot-consumer' ), value: a.starsFontSize, onChange: ( v ) => setAttributes( { starsFontSize: v } ), min: 10, max: 36, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
					el( RangeControl, { label: __( 'Gap (px)', 'wr-slot-consumer' ), value: a.starsGap, onChange: ( v ) => setAttributes( { starsGap: v } ), min: 0, max: 12, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
					el( RangeControl, { label: __( 'Info Font Size', 'wr-slot-consumer' ), value: a.starsInfoFontSize, onChange: ( v ) => setAttributes( { starsInfoFontSize: v } ), min: 8, max: 24, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
					ColorField( __( 'Full Star Color', 'wr-slot-consumer' ), a.starsColor, ( v ) => setAttributes( { starsColor: v } ) ),
					ColorField( __( 'Half Star Color', 'wr-slot-consumer' ), a.starsHalfColor, ( v ) => setAttributes( { starsHalfColor: v } ) ),
					ColorField( __( 'Star Border Color', 'wr-slot-consumer' ), a.starsBorderColor, ( v ) => setAttributes( { starsBorderColor: v } ) ),
					ColorField( __( 'Info Text Color', 'wr-slot-consumer' ), a.starsInfoColor, ( v ) => setAttributes( { starsInfoColor: v } ) ),
					FontStyleSelect( __( 'Info', 'wr-slot-consumer' ), a.starsInfoFontWeight, 'normal', ( v ) => setAttributes( { starsInfoFontWeight: v } ), () => {} )
				),

				/* More Info Button */
				el( PanelBody, { title: __( 'More Info Button', 'wr-slot-consumer' ), initialOpen: false },
					el( ToggleControl, { label: __( 'Show Button', 'wr-slot-consumer' ), checked: a.showMoreInfo, onChange: ( v ) => setAttributes( { showMoreInfo: v } ), __nextHasNoMarginBottom: true }),
					a.showMoreInfo && el( Fragment, {},
						el( RangeControl, { label: __( 'Border Radius', 'wr-slot-consumer' ), value: a.btnBorderRadius, onChange: ( v ) => setAttributes( { btnBorderRadius: v } ), min: 0, max: 30, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
						ColorField( __( 'Background', 'wr-slot-consumer' ), a.btnBgColor, ( v ) => setAttributes( { btnBgColor: v } ) ),
						ColorField( __( 'Text Color', 'wr-slot-consumer' ), a.btnTextColor, ( v ) => setAttributes( { btnTextColor: v } ) ),
						ColorField( __( 'Border Color', 'wr-slot-consumer' ), a.btnBorderColor, ( v ) => setAttributes( { btnBorderColor: v } ) ),
						FontStyleSelect( __( 'Button', 'wr-slot-consumer' ), a.btnFontWeight, 'normal', ( v ) => setAttributes( { btnFontWeight: v } ), () => {} ),
						el( 'h4', { style: { margin: '16px 0 8px' } }, __( 'Box Shadow', 'wr-slot-consumer' ) ),
						ShadowControls( 'btn', a, setAttributes ),
						el( 'h4', { style: { margin: '16px 0 8px' } }, __( 'Text Shadow', 'wr-slot-consumer' ) ),
						el( RangeControl, { label: 'X', value: a.btnTextShadowX, onChange: ( v ) => setAttributes( { btnTextShadowX: v } ), min: -20, max: 20, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
						el( RangeControl, { label: 'Y', value: a.btnTextShadowY, onChange: ( v ) => setAttributes( { btnTextShadowY: v } ), min: -20, max: 20, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
						el( RangeControl, { label: __( 'Blur', 'wr-slot-consumer' ), value: a.btnTextShadowBlur, onChange: ( v ) => setAttributes( { btnTextShadowBlur: v } ), min: 0, max: 30, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true }),
						ColorField( __( 'Text Shadow Color', 'wr-slot-consumer' ), a.btnTextShadowColor, ( v ) => setAttributes( { btnTextShadowColor: v } ) ),
						el( 'h4', { style: { margin: '16px 0 8px' } }, __( 'Hover State', 'wr-slot-consumer' ) ),
						ColorField( __( 'Hover Background', 'wr-slot-consumer' ), a.btnHoverBgColor, ( v ) => setAttributes( { btnHoverBgColor: v } ) ),
						ColorField( __( 'Hover Text Color', 'wr-slot-consumer' ), a.btnHoverTextColor, ( v ) => setAttributes( { btnHoverTextColor: v } ) ),
						ColorField( __( 'Hover Border Color', 'wr-slot-consumer' ), a.btnHoverBorderColor, ( v ) => setAttributes( { btnHoverBorderColor: v } ) ),
						el( TextControl, { label: __( 'Hover Shadow CSS', 'wr-slot-consumer' ), value: a.btnHoverShadow, onChange: ( v ) => setAttributes( { btnHoverShadow: v } ), __next40pxDefaultSize: true, __nextHasNoMarginBottom: true })
					)
				)
			),

			/* Preview */
			el( Disabled, {},
				el( ServerSideRender, { block: 'wr-slot-consumer/slot-grid', attributes: attributes })
			)
		);
	},

	save: function SaveSlotGrid() {
		return null;
	},
});
