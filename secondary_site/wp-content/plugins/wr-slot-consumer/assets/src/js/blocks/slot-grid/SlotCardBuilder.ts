/**
 * Builds HTML markup for slot cards (used by load-more and popup).
 *
 * @package WiseRabbit\SlotConsumer
 */

export interface CardBuilderConfig {
	linkMode: string;
	detailUrl: string;
	showBtn: boolean;
	starFullColor: string;
	starHalfColor: string;
	starEmptyColor: string;
	starBorder: string;
	starSize: string;
}

function esc( str: string ): string {
	const el = document.createElement( 'span' );
	el.textContent = str || '';
	return el.innerHTML;
}

export class SlotCardBuilder {

	public linkMode: string;
	public detailUrl: string;
	public showBtn: boolean;
	public starFullColor: string;
	public starHalfColor: string;
	public starEmptyColor: string;
	public starBorder: string;
	public starSize: string;

	constructor( config: CardBuilderConfig ) {
		this.linkMode       = config.linkMode;
		this.detailUrl      = config.detailUrl;
		this.showBtn        = config.showBtn;
		this.starFullColor  = config.starFullColor;
		this.starHalfColor  = config.starHalfColor;
		this.starEmptyColor = config.starEmptyColor;
		this.starBorder     = config.starBorder;
		this.starSize       = config.starSize;
	}

	build( slot: SlotData ): string {
		const image = slot.featured_image
			? `<div class="wr-sc-slot-card__image"><img src="${ esc( slot.featured_image ) }" alt="${ esc( slot.title ) }" loading="lazy"></div>`
			: '';

		const stars = slot.star_rating
			? `<div class="wr-sc-slot-card__rating" aria-label="${ esc( slot.star_rating + ' stars' ) }">${ this.buildStars( parseFloat( String( slot.star_rating ) ) ) }</div>`
			: '';

		const provider = slot.provider_name
			? `<span class="wr-sc-slot-card__provider">${ esc( slot.provider_name ) }</span>`
			: '';

		const btn = this.buildButton( slot );

		return `<article class="wr-sc-slot-card">${ image }<div class="wr-sc-slot-card__content"><h3 class="wr-sc-slot-card__title">${ esc( slot.title ) }</h3>${ stars }${ provider }${ btn }</div></article>`;
	}

	buildButton( slot: SlotData ): string {
		if ( ! this.showBtn ) {
			return '';
		}

		if ( this.linkMode === 'popup' ) {
			const escaped = JSON.stringify( slot ).replace( /'/g, '&#39;' );
			return `<button type="button" class="wr-sc-slot-card__btn wr-sc-popup-trigger" data-slot='${ escaped }'>More Info</button>`;
		}

		const href = this.detailUrl
			? this.detailUrl.replace( /\/$/, '' ) + '/?slot_detail=' + slot.id
			: '?slot_detail=' + slot.id;

		return `<a href="${ href }" class="wr-sc-slot-card__btn">More Info</a>`;
	}

	buildStars( rating: number ): string {
		let html = '';
		const border = this.starBorder ? `-webkit-text-stroke:1px ${ this.starBorder };` : '';

		for ( let i = 1; i <= 5; i++ ) {
			const color    = i <= rating ? this.starFullColor : ( i - 0.5 <= rating ? this.starHalfColor : this.starEmptyColor );
			const charCode = ( i <= rating || i - 0.5 <= rating ) ? '&#9733;' : '&#9734;';
			html += `<span class="wr-sc-star" style="font-size:${ this.starSize };color:${ color };${ border }">${ charCode }</span>`;
		}

		return html + `<span class="wr-sc-slot-card__rating-number">${ rating }/5</span>`;
	}

	static buildStarsHTML( rating: number ): string {
		let html = '';

		for ( let i = 1; i <= 5; i++ ) {
			if ( i <= rating ) {
				html += '<span style="color:#f0b429">&#9733;</span>';
			} else if ( i - 0.5 <= rating ) {
				html += '<span style="color:#e2c773">&#9733;</span>';
			} else {
				html += '<span style="color:#d4d4d8">&#9734;</span>';
			}
		}

		return html + ` <span>${ rating }/5</span>`;
	}
}
