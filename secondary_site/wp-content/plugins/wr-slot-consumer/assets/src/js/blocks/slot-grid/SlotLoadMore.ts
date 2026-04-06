/**
 * Load More / Infinite Scroll handler for the Slot Grid.
 *
 * @package WiseRabbit\SlotConsumer
 */

import { SlotCardBuilder, CardBuilderConfig } from './SlotCardBuilder.ts';

export class SlotLoadMore {

	private wrapper: HTMLElement;
	private grid: HTMLElement;
	private remaining: SlotData[];
	private perPage: number;
	private mode: string | null;
	private cardBuilder: SlotCardBuilder;
	private loading: boolean;

	constructor( wrapper: HTMLElement, grid: HTMLElement ) {
		this.wrapper   = wrapper;
		this.grid      = grid;
		this.remaining = JSON.parse( wrapper.getAttribute( 'data-remaining' ) || '[]' ) as SlotData[];
		this.perPage   = parseInt( wrapper.getAttribute( 'data-per-page' ) as string, 10 ) || 6;
		this.mode      = wrapper.getAttribute( 'data-mode' );
		this.loading   = false;

		this.cardBuilder = new SlotCardBuilder( {
			linkMode:      wrapper.getAttribute( 'data-link-mode' ),
			detailUrl:     wrapper.getAttribute( 'data-detail-url' ) || '',
			showBtn:       wrapper.getAttribute( 'data-show-btn' ) === '1',
			starFullColor: wrapper.getAttribute( 'data-star-full' ),
			starHalfColor: wrapper.getAttribute( 'data-star-half' ),
			starEmptyColor: wrapper.getAttribute( 'data-star-empty' ),
			starBorder:    wrapper.getAttribute( 'data-star-border' ),
			starSize:      wrapper.getAttribute( 'data-star-size' ),
		} as CardBuilderConfig );

		this.init();
	}

	/**
	 * Bind event listeners based on mode.
	 */
	init(): void {
		if ( this.mode === 'button' ) {
			const btn = this.wrapper.querySelector( '.wr-sc-loadmore__btn' ) as HTMLButtonElement | null;
			if ( btn ) {
				btn.addEventListener( 'click', () => this.loadBatch() );
			}
			return;
		}

		this.initInfiniteScroll();
	}

	/**
	 * Set up IntersectionObserver for infinite scroll.
	 */
	initInfiniteScroll(): void {
		const sentinel = this.wrapper.querySelector( '.wr-sc-loadmore__sentinel' ) as HTMLElement | null;

		if ( ! sentinel || ! window.IntersectionObserver ) {
			return;
		}

		this.loading = false;

		const observer = new IntersectionObserver(
			( entries: IntersectionObserverEntry[] ) => {
				if ( entries[0].isIntersecting && ! this.loading && this.remaining.length ) {
					this.loading = true;
					setTimeout( () => {
						this.loadBatch();
						requestAnimationFrame( () => { this.loading = false; } );
					}, 300 );
				}
			},
			{ rootMargin: '300px' }
		);

		observer.observe( sentinel );
	}

	/**
	 * Load the next batch of slots into the grid.
	 */
	loadBatch(): void {
		if ( ! this.remaining.length ) {
			this.hide();
			return;
		}

		const batch: SlotData[] = this.remaining.splice( 0, this.perPage );

		batch.forEach( ( slot: SlotData ) => {
			this.grid.insertAdjacentHTML( 'beforeend', this.cardBuilder.build( slot ) );
		} );

		this.bindPopupTriggers();

		if ( ! this.remaining.length ) {
			this.hide();
		}
	}

	/**
	 * Bind popup triggers on newly added cards.
	 */
	bindPopupTriggers(): void {
		if ( this.cardBuilder.linkMode !== 'popup' ) {
			return;
		}

		this.grid.querySelectorAll<HTMLButtonElement>( '.wr-sc-popup-trigger:not([data-bound])' ).forEach( ( btn ) => {
			btn.setAttribute( 'data-bound', '1' );
			btn.addEventListener( 'click', function ( this: HTMLButtonElement ) {
				const dialog = document.getElementById( 'wr-sc-dialog' ) as HTMLDialogElement | null;
				if ( ! dialog ) {
					return;
				}

				const slot: SlotData = JSON.parse( this.getAttribute( 'data-slot' )! );
				SlotLoadMore.fillDialog( dialog, slot );
			} );
		} );
	}

	/**
	 * Fill the dialog element with slot data.
	 */
	static fillDialog( dialog: HTMLDialogElement, slot: SlotData ): void {
		( dialog.querySelector( '.wr-sc-dialog__title' ) as HTMLElement ).textContent       = slot.title || '';
		( dialog.querySelector( '.wr-sc-dialog__description' ) as HTMLElement ).textContent = slot.description || '';
		const imgContainer = dialog.querySelector( '.wr-sc-dialog__image' ) as HTMLElement;
		imgContainer.innerHTML = '';
		if ( slot.featured_image ) {
			const img = document.createElement( 'img' );
			img.src = slot.featured_image;
			img.alt = '';
			imgContainer.appendChild( img );
		}
		( dialog.querySelector( '.wr-sc-dialog__provider dd' ) as HTMLElement ).textContent = slot.provider_name || '';
		( dialog.querySelector( '.wr-sc-dialog__rtp dd' ) as HTMLElement ).textContent      = slot.rtp ? slot.rtp + '%' : '';
		( dialog.querySelector( '.wr-sc-dialog__wager dd' ) as HTMLElement ).textContent    = ( slot.min_wager || 0 ) + ' – ' + ( slot.max_wager || 0 );

		( dialog.querySelector( '.wr-sc-dialog__rating' ) as HTMLElement ).innerHTML = slot.star_rating
			? SlotCardBuilder.buildStarsHTML( parseFloat( String( slot.star_rating ) ) )
			: '';

		dialog.showModal();
		document.documentElement.style.overflow = 'hidden';
	}

	/**
	 * Hide the load more wrapper.
	 */
	hide(): void {
		this.wrapper.style.display = 'none';
	}
}
