/**
 * Slot Fields block — inline editor for all slot meta + image.
 *
 * Local state for all fields. editPost() only on explicit save.
 * Ctrl+S / Cmd+S shortcut supported.
 *
 * @package WiseRabbit\SlotManager
 */

import '../../scss/editor/slotFields.scss';

const { registerBlockType } = wp.blocks;
const { createElement: el, useEffect, useRef, useState, useCallback } = wp.element;
const { MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { TextControl, TextareaControl, Button, Placeholder, Tooltip } = wp.components;
const { useSelect, useDispatch } = wp.data;
const { __ } = wp.i18n;

/* ─── Constants ─── */

const FOCUSABLE_SELECTOR =
	'a[href], button:not([disabled]), input:not([type="hidden"]):not([disabled]), ' +
	'textarea:not([disabled]), select:not([disabled]), [tabindex="0"]';

const META_PREFIX = 'wr_sm_';

/* ─── Utilities ─── */

/**
 * Clamp a parsed number within [min, max].
 *
 * @param {string} val      Raw input value.
 * @param {number} min      Lower bound.
 * @param {number} max      Upper bound.
 * @param {number} fallback Value when input is empty.
 * @return {number|null}    Parsed number, fallback, or null to reject.
 */
function clampNumber( val, min, max, fallback ) {
	if ( val === '' ) {
		return fallback;
	}
	const num = parseFloat( val );
	return ( isNaN( num ) || num < min || num > max ) ? null : num;
}

/**
 * Get the real active element, resolving through Gutenberg's iframe.
 */
function getActiveElement() {
	const top = document.activeElement;
	if ( top && top.tagName === 'IFRAME' && top.contentDocument ) {
		return top.contentDocument.activeElement;
	}
	return top;
}

/**
 * Get visible focusable elements within a root node.
 */
function getFocusable( root ) {
	return Array.from( root.querySelectorAll( FOCUSABLE_SELECTOR ) )
		.filter( ( node ) => node.offsetParent !== null );
}

/* ─── Hooks ─── */

/**
 * Tab navigates through block fields, then escapes to the top-level page.
 * Gutenberg renders blocks in an iframe and traps focus — this breaks out.
 */
function useTabNavigation( ref ) {
	useEffect( () => {
		const node = ref.current;
		if ( ! node ) {
			return;
		}

		function onKeyDown( e ) {
			if ( e.key !== 'Tab' ) {
				return;
			}

			e.stopPropagation();

			const inner = getFocusable( node );
			if ( ! inner.length ) {
				return;
			}

			const active  = getActiveElement();
			const first   = inner[ 0 ];
			const last    = inner[ inner.length - 1 ];
			const atEnd   = ! e.shiftKey && ( last === active || last.contains( active ) );
			const atStart = e.shiftKey && ( first === active || first.contains( active ) );

			if ( ! atEnd && ! atStart ) {
				return;
			}

			e.preventDefault();

			const topFocusable = getFocusable( document.body );
			if ( ! topFocusable.length ) {
				return;
			}

			( atEnd ? topFocusable[ 0 ] : topFocusable[ topFocusable.length - 1 ] ).focus();
		}

		node.addEventListener( 'keydown', onKeyDown, true );
		return () => node.removeEventListener( 'keydown', onKeyDown, true );
	}, [ ref ] );
}

/**
 * Ctrl+S / Cmd+S fires the callback stored in the ref.
 */
function useSaveShortcut( callbackRef ) {
	useEffect( () => {
		function onKey( e ) {
			if ( ( e.ctrlKey || e.metaKey ) && e.key === 's' ) {
				e.preventDefault();
				callbackRef.current();
			}
		}
		document.addEventListener( 'keydown', onKey );
		return () => document.removeEventListener( 'keydown', onKey );
	}, [] );
}

/**
 * Focus the first input inside the ref on mount.
 */
function useAutoFocus( ref ) {
	useEffect( () => {
		if ( ! ref.current ) {
			return;
		}
		const input = ref.current.querySelector( 'input' );
		if ( input ) {
			requestAnimationFrame( () => input.focus() );
		}
	}, [] );
}

/* ─── Field renderers ─── */

function renderTextField( label, value, onChange ) {
	return el( 'div', { className: 'wr-sm-slot-fields__section' },
		el( TextControl, {
			label,
			value,
			onChange,
			__next40pxDefaultSize: true,
			__nextHasNoMarginBottom: true,
		} )
	);
}

function renderTextarea( label, value, onChange, rows ) {
	return el( 'div', { className: 'wr-sm-slot-fields__section' },
		el( TextareaControl, {
			label,
			value,
			onChange,
			rows,
			__nextHasNoMarginBottom: true,
		} )
	);
}

function renderNumberField( label, tooltip, help, value, min, max, fallback, setter ) {
	return el( 'div', { className: 'wr-sm-slot-fields__section' },
		el( Tooltip, { text: tooltip },
			el( TextControl, {
				label,
				type: 'number',
				value: value !== 0 ? String( value ) : '',
				onChange: ( val ) => {
					const n = clampNumber( val, min, max, fallback );
					if ( n !== null ) {
						setter( n );
					}
				},
				help,
				__next40pxDefaultSize: true,
				__nextHasNoMarginBottom: true,
			} )
		)
	);
}

function renderImage( imageId, imageUrl, onSelect, onRemove, replaceRef ) {
	if ( imageUrl ) {
		return el( 'div', { className: 'wr-sm-slot-fields__section' },
			el( 'label', { className: 'wr-sm-slot-fields__label' }, __( 'Slot Image', 'wr-slot-manager' ) ),
			el( 'div', { className: 'wr-sm-slot-fields__image-preview' },
				el( 'img', { src: imageUrl, alt: '' } ),
				el( 'div', { className: 'wr-sm-slot-fields__image-actions' },
					el( MediaUploadCheck, {},
						el( MediaUpload, {
							onSelect,
							allowedTypes: [ 'image' ],
							value: imageId,
							render: ( { open } ) => el( Button, { ref: replaceRef, variant: 'secondary', onClick: open }, __( 'Replace', 'wr-slot-manager' ) ),
						} )
					),
					el( Button, { variant: 'link', isDestructive: true, onClick: onRemove }, __( 'Remove', 'wr-slot-manager' ) )
				)
			)
		);
	}

	return el( 'div', { className: 'wr-sm-slot-fields__section' },
		el( 'label', { className: 'wr-sm-slot-fields__label' }, __( 'Slot Image', 'wr-slot-manager' ) ),
		el( MediaUploadCheck, {},
			el( MediaUpload, {
				onSelect,
				allowedTypes: [ 'image' ],
				value: imageId,
				render: ( { open } ) => el( Button, { variant: 'secondary', onClick: open }, __( 'Select Image', 'wr-slot-manager' ) ),
			} )
		)
	);
}

/* ─── Block registration ─── */

registerBlockType( 'wr-slot-manager/slot-fields', {
	edit: function EditSlotFields() {
		const blockRef = useRef( null );
		useTabNavigation( blockRef );
		useAutoFocus( blockRef );

		// Read initial store values once.
		const { meta, title: storeTitle } = useSelect( ( select ) => {
			const editor = select( 'core/editor' );
			return {
				meta: editor.getEditedPostAttribute( 'meta' ) || {},
				title: editor.getEditedPostAttribute( 'title' ) || '',
			};
		}, [] );

		const { editPost, savePost } = useDispatch( 'core/editor' );
		const { createSuccessNotice, createErrorNotice } = useDispatch( 'core/notices' );

		// Local field state.
		const [ title, setTitle ]             = useState( storeTitle );
		const [ description, setDescription ] = useState( meta[ META_PREFIX + 'description' ] || '' );
		const [ starRating, setStarRating ]   = useState( parseFloat( meta[ META_PREFIX + 'star_rating' ] ) || 0 );
		const [ provider, setProvider ]       = useState( meta[ META_PREFIX + 'provider_name' ] || '' );
		const [ rtp, setRtp ]                 = useState( parseFloat( meta[ META_PREFIX + 'rtp' ] ) || 0 );
		const [ minWager, setMinWager ]       = useState( meta[ META_PREFIX + 'min_wager' ] !== undefined ? String( meta[ META_PREFIX + 'min_wager' ] ) : '' );
		const [ maxWager, setMaxWager ]       = useState( meta[ META_PREFIX + 'max_wager' ] !== undefined ? String( meta[ META_PREFIX + 'max_wager' ] ) : '' );
		const [ imageId, setImageId ]         = useState( parseInt( meta[ META_PREFIX + 'image_id' ] ) || 0 );
		const [ saving, setSaving ]           = useState( false );

		// Ref always holds the latest field values so handleSave never goes stale.
		const fieldsRef = useRef();
		fieldsRef.current = { title, description, starRating, provider, rtp, minWager, maxWager, imageId };

		// Resolve image URL from store.
		const imageUrl = useSelect( ( select ) => {
			if ( ! imageId ) {
				return '';
			}
			const img = select( 'core' ).getEntityRecord( 'postType', 'attachment', imageId );
			return img ? ( img.media_details?.sizes?.medium?.source_url || img.source_url || '' ) : '';
		}, [ imageId ] );

		// Save handler — flushes local state to store, then persists.
		const handleSave = useCallback( async () => {
			const f = fieldsRef.current;
			setSaving( true );

			editPost( {
				status: 'private',
				title: f.title,
				meta: {
					[ META_PREFIX + 'description' ]: f.description,
					[ META_PREFIX + 'star_rating' ]: f.starRating,
					[ META_PREFIX + 'image_id' ]: f.imageId,
					[ META_PREFIX + 'provider_name' ]: f.provider,
					[ META_PREFIX + 'rtp' ]: f.rtp,
					[ META_PREFIX + 'min_wager' ]: parseFloat( f.minWager ) || 0,
					[ META_PREFIX + 'max_wager' ]: parseFloat( f.maxWager ) || 0,
				},
			} );

			try {
				await savePost();
				createSuccessNotice( __( 'Slot saved.', 'wr-slot-manager' ), { type: 'snackbar' } );
			} catch {
				createErrorNotice( __( 'Save failed.', 'wr-slot-manager' ), { type: 'snackbar' } );
			} finally {
				setSaving( false );
			}
		}, [ editPost, savePost, createSuccessNotice, createErrorNotice ] );

		// Keyboard shortcut.
		const saveRef = useRef( handleSave );
		saveRef.current = handleSave;
		useSaveShortcut( saveRef );

		// Image callbacks.
		const replaceRef    = useRef( null );
		const justSelected  = useRef( false );

		const onImageSelect = useCallback( ( media ) => {
			justSelected.current = true;
			setImageId( media.id );
		}, [] );

		const onImageRemove = useCallback( () => setImageId( 0 ), [] );

		// Focus Replace button after image URL resolves from the store.
		useEffect( () => {
			if ( justSelected.current && imageUrl && replaceRef.current ) {
				replaceRef.current.focus();
				justSelected.current = false;
			}
		}, [ imageUrl ] );

		// Render.
		return el(
			'div',
			{ className: 'wr-sm-slot-fields', ref: blockRef },

			renderTextField( __( 'Title', 'wr-slot-manager' ), title, setTitle ),
			renderTextarea( __( 'Description', 'wr-slot-manager' ), description, setDescription, 4 ),
			renderNumberField( __( 'Star Rating', 'wr-slot-manager' ), __( 'Value between 0 and 5 (step 0.5)', 'wr-slot-manager' ), __( 'Min: 0, Max: 5', 'wr-slot-manager' ), starRating, 0, 5, 0, setStarRating ),
			renderImage( imageId, imageUrl, onImageSelect, onImageRemove, replaceRef ),
			renderTextField( __( 'Provider Name', 'wr-slot-manager' ), provider, setProvider ),
			renderNumberField( __( 'RTP (%)', 'wr-slot-manager' ), __( 'Value between 0 and 100', 'wr-slot-manager' ), __( 'Min: 0, Max: 100', 'wr-slot-manager' ), rtp, 0, 100, 0, setRtp ),

			el( 'div', { className: 'wr-sm-slot-fields__wager-row' },
				el( TextControl, { label: __( 'Min Wager', 'wr-slot-manager' ), type: 'number', value: minWager, onChange: setMinWager, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true } ),
				el( TextControl, { label: __( 'Max Wager', 'wr-slot-manager' ), type: 'number', value: maxWager, onChange: setMaxWager, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true } )
			),

			el( 'div', { className: 'wr-sm-slot-fields__save' },
				el( Button, {
					variant: 'primary',
					onClick: handleSave,
					isBusy: saving,
					disabled: saving,
				}, saving ? __( 'Saving...', 'wr-slot-manager' ) : __( 'Save Slot', 'wr-slot-manager' ) )
			)
		);
	},

	save: function SaveSlotFields() {
		return null;
	},
} );
