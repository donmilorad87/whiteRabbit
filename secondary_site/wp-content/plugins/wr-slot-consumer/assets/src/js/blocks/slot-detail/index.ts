/**
 * Slot Detail — dedicated slot detail page block with selector + styling.
 *
 * @package WiseRabbit\SlotConsumer
 */

const { registerBlockType } = wp.blocks;
const { createElement: el, Fragment } = wp.element;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const {
	PanelBody, SelectControl, RangeControl, ToggleControl, ColorPicker, TextControl, Disabled,
} = wp.components;
const { __ } = wp.i18n;
const ServerSideRender = wp.serverSideRender;

function ColorField( label: string, value: string, onChange: ( v: string ) => void ) {
	return el( PanelBody, { title: label, initialOpen: false },
		el( ColorPicker, { color: value || '', onChange, enableAlpha: true } )
	);
}

registerBlockType( 'wr-slot-consumer/slot-detail', {
	edit: function EditSlotPage( props: any ) {
		const { attributes, setAttributes } = props;
		const a = attributes;
		const blockProps = useBlockProps();

		// Fetch slot list for the dropdown via REST API.
		const [ slotOptions, setSlotOptions ] = ( wp.element as any ).useState( [ { label: __( '— Select a Slot —', 'wr-slot-consumer' ), value: '0' } ] );
		const [ loaded, setLoaded ] = ( wp.element as any ).useState( false );

		( wp.element as any ).useEffect( () => {
			if ( loaded ) return;
			( window as any ).wp.apiFetch( { path: '/wr-slot-consumer/v1/slot-list' } ).then( ( list: any[] ) => {
				const opts = [
					{ label: __( '— Select a Slot —', 'wr-slot-consumer' ), value: '0' },
					...list.map( ( s: any ) => ( { label: s.label, value: String( s.value ) } ) ),
				];
				setSlotOptions( opts );
				setLoaded( true );
			} ).catch( () => {
				setLoaded( true );
			} );
		}, [] );

		return el(
			'div',
			blockProps,

			el( InspectorControls, {},

				/* Slot Selector */
				el( PanelBody, { title: __( 'Select Slot', 'wr-slot-consumer' ) },
					el( SelectControl, {
						label: __( 'Slot', 'wr-slot-consumer' ),
						value: String( a.slotId || 0 ),
						options: slotOptions,
						onChange: ( v: string ) => setAttributes( { slotId: parseInt( v ) || 0 } ),
						__next40pxDefaultSize: true,
						__nextHasNoMarginBottom: true,
					} )
				),

				/* Visible Sections */
				el( PanelBody, { title: __( 'Visible Sections', 'wr-slot-consumer' ), initialOpen: false },
					el( ToggleControl, { label: __( 'Image', 'wr-slot-consumer' ), checked: a.showImage, onChange: ( v: boolean ) => setAttributes( { showImage: v } ), __nextHasNoMarginBottom: true } ),
					el( ToggleControl, { label: __( 'Star Rating', 'wr-slot-consumer' ), checked: a.showRating, onChange: ( v: boolean ) => setAttributes( { showRating: v } ), __nextHasNoMarginBottom: true } ),
					el( ToggleControl, { label: __( 'Description', 'wr-slot-consumer' ), checked: a.showDescription, onChange: ( v: boolean ) => setAttributes( { showDescription: v } ), __nextHasNoMarginBottom: true } ),
					el( ToggleControl, { label: __( 'Provider', 'wr-slot-consumer' ), checked: a.showProvider, onChange: ( v: boolean ) => setAttributes( { showProvider: v } ), __nextHasNoMarginBottom: true } ),
					el( ToggleControl, { label: __( 'RTP %', 'wr-slot-consumer' ), checked: a.showRtp, onChange: ( v: boolean ) => setAttributes( { showRtp: v } ), __nextHasNoMarginBottom: true } ),
					el( ToggleControl, { label: __( 'Wager Range', 'wr-slot-consumer' ), checked: a.showWager, onChange: ( v: boolean ) => setAttributes( { showWager: v } ), __nextHasNoMarginBottom: true } ),
				),

				/* Background */
				el( PanelBody, { title: __( 'Background', 'wr-slot-consumer' ), initialOpen: false },
					el( RangeControl, { label: __( 'Padding', 'wr-slot-consumer' ), value: a.bgPadding, onChange: ( v: number ) => setAttributes( { bgPadding: v } ), min: 0, max: 80, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true } ),
					el( RangeControl, { label: __( 'Border Radius', 'wr-slot-consumer' ), value: a.bgBorderRadius, onChange: ( v: number ) => setAttributes( { bgBorderRadius: v } ), min: 0, max: 40, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true } ),
					ColorField( __( 'Background Color', 'wr-slot-consumer' ), a.bgColor, ( v: string ) => setAttributes( { bgColor: v } ) ),
				),

				/* Image */
				el( PanelBody, { title: __( 'Image', 'wr-slot-consumer' ), initialOpen: false },
					el( RangeControl, { label: __( 'Border Radius', 'wr-slot-consumer' ), value: a.imageBorderRadius, onChange: ( v: number ) => setAttributes( { imageBorderRadius: v } ), min: 0, max: 40, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true } ),
					el( RangeControl, { label: __( 'Max Height (px)', 'wr-slot-consumer' ), value: a.imageMaxHeight, onChange: ( v: number ) => setAttributes( { imageMaxHeight: v } ), min: 100, max: 800, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true } ),
				),

				/* Title */
				el( PanelBody, { title: __( 'Title', 'wr-slot-consumer' ), initialOpen: false },
					el( RangeControl, { label: __( 'Font Size (px)', 'wr-slot-consumer' ), value: a.titleFontSize, onChange: ( v: number ) => setAttributes( { titleFontSize: v } ), min: 0, max: 60, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true } ),
					el( SelectControl, {
						label: __( 'Font Weight', 'wr-slot-consumer' ),
						value: a.titleFontWeight,
						options: [
							{ label: 'Normal (400)', value: '400' },
							{ label: 'Medium (500)', value: '500' },
							{ label: 'Semi-Bold (600)', value: '600' },
							{ label: 'Bold (700)', value: '700' },
							{ label: 'Extra Bold (750)', value: '750' },
						],
						onChange: ( v: string ) => setAttributes( { titleFontWeight: v } ),
						__next40pxDefaultSize: true,
						__nextHasNoMarginBottom: true,
					} ),
					ColorField( __( 'Title Color', 'wr-slot-consumer' ), a.titleColor, ( v: string ) => setAttributes( { titleColor: v } ) ),
				),

				/* Description */
				el( PanelBody, { title: __( 'Description', 'wr-slot-consumer' ), initialOpen: false },
					el( RangeControl, { label: __( 'Font Size (px)', 'wr-slot-consumer' ), value: a.descFontSize, onChange: ( v: number ) => setAttributes( { descFontSize: v } ), min: 0, max: 30, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true } ),
					ColorField( __( 'Description Color', 'wr-slot-consumer' ), a.descColor, ( v: string ) => setAttributes( { descColor: v } ) ),
				),

				/* Stars */
				el( PanelBody, { title: __( 'Stars', 'wr-slot-consumer' ), initialOpen: false },
					el( RangeControl, { label: __( 'Star Size (px)', 'wr-slot-consumer' ), value: a.starsFontSize, onChange: ( v: number ) => setAttributes( { starsFontSize: v } ), min: 10, max: 40, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true } ),
					ColorField( __( 'Full Star Color', 'wr-slot-consumer' ), a.starsColor, ( v: string ) => setAttributes( { starsColor: v } ) ),
					ColorField( __( 'Half Star Color', 'wr-slot-consumer' ), a.starsHalfColor, ( v: string ) => setAttributes( { starsHalfColor: v } ) ),
					ColorField( __( 'Star Border Color', 'wr-slot-consumer' ), a.starsBorderColor, ( v: string ) => setAttributes( { starsBorderColor: v } ) ),
				),

				/* Meta Grid */
				el( PanelBody, { title: __( 'Meta Info', 'wr-slot-consumer' ), initialOpen: false },
					el( RangeControl, { label: __( 'Border Radius', 'wr-slot-consumer' ), value: a.metaBorderRadius, onChange: ( v: number ) => setAttributes( { metaBorderRadius: v } ), min: 0, max: 30, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true } ),
					ColorField( __( 'Background Color', 'wr-slot-consumer' ), a.metaBgColor, ( v: string ) => setAttributes( { metaBgColor: v } ) ),
					ColorField( __( 'Border Color', 'wr-slot-consumer' ), a.metaBorderColor, ( v: string ) => setAttributes( { metaBorderColor: v } ) ),
					ColorField( __( 'Label Color', 'wr-slot-consumer' ), a.metaLabelColor, ( v: string ) => setAttributes( { metaLabelColor: v } ) ),
					ColorField( __( 'Value Color', 'wr-slot-consumer' ), a.metaValueColor, ( v: string ) => setAttributes( { metaValueColor: v } ) ),
				),
			),

			/* Preview */
			el( Disabled, {},
				el( ServerSideRender, { block: 'wr-slot-consumer/slot-detail', attributes } )
			)
		);
	},

	save: function SaveSlotPage() {
		return null;
	},
} );
