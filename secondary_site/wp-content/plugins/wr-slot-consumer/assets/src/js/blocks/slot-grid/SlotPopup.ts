/**
 * Popup (dialog) handler for slot grid "More Info" buttons.
 *
 * @package WiseRabbit\SlotConsumer
 */

import { SlotCardBuilder } from './SlotCardBuilder.ts';
import { SlotLoadMore } from './SlotLoadMore.ts';

export class SlotPopup {

	private dialog: HTMLDialogElement;

	constructor( dialog: HTMLDialogElement ) {
		this.dialog = dialog;
		this.init();
	}

	/**
	 * Set up event listeners.
	 */
	init(): void {
		this.bindTriggers();
		this.bindClose();
		this.bindBackdropClick();
	}

	/**
	 * Bind all .wr-sc-popup-trigger buttons to open the dialog.
	 */
	bindTriggers(): void {
		document.querySelectorAll<HTMLButtonElement>( '.wr-sc-popup-trigger' ).forEach( ( btn ) => {
			btn.addEventListener( 'click', () => {
				const slot: SlotData = JSON.parse( btn.getAttribute( 'data-slot' )! );
				this.open( slot );
			} );
		} );
	}

	/**
	 * Open the dialog with slot data.
	 */
	open( slot: SlotData ): void {
		const d = this.dialog;

		( d.querySelector( '.wr-sc-dialog__title' ) as HTMLElement ).textContent       = slot.title || '';
		( d.querySelector( '.wr-sc-dialog__description' ) as HTMLElement ).textContent = slot.description || '';
		const imgContainer = d.querySelector( '.wr-sc-dialog__image' ) as HTMLElement;
		imgContainer.innerHTML = '';
		if ( slot.featured_image ) {
			const img = document.createElement( 'img' );
			img.src = slot.featured_image;
			img.alt = '';
			imgContainer.appendChild( img );
		}
		( d.querySelector( '.wr-sc-dialog__provider dd' ) as HTMLElement ).textContent = slot.provider_name || '';
		( d.querySelector( '.wr-sc-dialog__rtp dd' ) as HTMLElement ).textContent      = slot.rtp ? slot.rtp + '%' : '';
		( d.querySelector( '.wr-sc-dialog__wager dd' ) as HTMLElement ).textContent    = ( slot.min_wager || 0 ) + ' – ' + ( slot.max_wager || 0 );

		( d.querySelector( '.wr-sc-dialog__provider' ) as HTMLElement ).style.display = slot.provider_name ? '' : 'none';
		( d.querySelector( '.wr-sc-dialog__rtp' ) as HTMLElement ).style.display      = slot.rtp ? '' : 'none';
		( d.querySelector( '.wr-sc-dialog__wager' ) as HTMLElement ).style.display    = ( slot.min_wager || slot.max_wager ) ? '' : 'none';

		( d.querySelector( '.wr-sc-dialog__rating' ) as HTMLElement ).innerHTML = slot.star_rating
			? SlotCardBuilder.buildStarsHTML( parseFloat( String( slot.star_rating ) ) )
			: '';

		d.showModal();
		document.documentElement.style.overflow = 'hidden';
	}

	/**
	 * Bind the dialog close event to restore scroll.
	 */
	bindClose(): void {
		this.dialog.addEventListener( 'close', () => {
			document.documentElement.style.overflow = '';
		} );
	}

	/**
	 * Close dialog on backdrop click.
	 */
	bindBackdropClick(): void {
		this.dialog.addEventListener( 'click', ( e: MouseEvent ) => {
			if ( e.target === this.dialog ) {
				this.dialog.close();
			}
		} );
	}
}
