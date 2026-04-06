import { describe, it, expect } from 'vitest';
import { SlotCardBuilder } from '../js/blocks/slot-grid/SlotCardBuilder.ts';

const defaultConfig = {
	linkMode: 'page',
	detailUrl: '/slot-detail/',
	showBtn: true,
	starFullColor: '#f0b429',
	starHalfColor: '#e2c773',
	starEmptyColor: '#d4d4d8',
	starBorder: '',
	starSize: '18px',
};

function makeSlot( overrides = {} ) {
	return {
		id: 1,
		title: 'Test Slot',
		featured_image: 'http://example.com/img.jpg',
		star_rating: 4,
		provider_name: 'TestProvider',
		description: 'A test slot.',
		rtp: 96,
		min_wager: 1,
		max_wager: 100,
		...overrides,
	};
}

describe( 'SlotCardBuilder', () => {
	it( 'builds card HTML with all fields', () => {
		const builder = new SlotCardBuilder( defaultConfig );
		const html = builder.build( makeSlot() );

		expect( html ).toContain( 'wr-sc-slot-card' );
		expect( html ).toContain( 'Test Slot' );
		expect( html ).toContain( 'TestProvider' );
		expect( html ).toContain( 'img.jpg' );
		expect( html ).toContain( 'More Info' );
	} );

	it( 'builds card without image when empty', () => {
		const builder = new SlotCardBuilder( defaultConfig );
		const html = builder.build( makeSlot( { featured_image: '' } ) );

		expect( html ).not.toContain( '<img' );
	} );

	it( 'builds popup button when linkMode is popup', () => {
		const builder = new SlotCardBuilder( { ...defaultConfig, linkMode: 'popup' } );
		const html = builder.build( makeSlot() );

		expect( html ).toContain( 'wr-sc-popup-trigger' );
		expect( html ).toContain( 'data-slot' );
		expect( html ).not.toContain( '<a ' );
	} );

	it( 'builds anchor link when linkMode is page', () => {
		const builder = new SlotCardBuilder( defaultConfig );
		const html = builder.build( makeSlot() );

		expect( html ).toContain( '<a ' );
		expect( html ).toContain( 'slot_detail=1' );
	} );

	it( 'builds no button when showBtn is false', () => {
		const builder = new SlotCardBuilder( { ...defaultConfig, showBtn: false } );
		const html = builder.build( makeSlot() );

		expect( html ).not.toContain( 'More Info' );
	} );

	it( 'builds correct star count', () => {
		const builder = new SlotCardBuilder( defaultConfig );
		const html = builder.build( makeSlot( { star_rating: 3.5 } ) );

		// 3 full + 1 half + 1 empty = 5 star spans
		const starCount = ( html.match( /wr-sc-star/g ) || [] ).length;
		expect( starCount ).toBe( 5 );
		expect( html ).toContain( '3.5/5' );
	} );

	it( 'escapes HTML in title to prevent XSS', () => {
		const builder = new SlotCardBuilder( defaultConfig );
		const html = builder.build( makeSlot( { title: '<script>alert(1)</script>' } ) );

		expect( html ).not.toContain( '<script>' );
		expect( html ).toContain( '&lt;script&gt;' );
	} );

	it( 'escapes HTML in provider name', () => {
		const builder = new SlotCardBuilder( defaultConfig );
		const html = builder.build( makeSlot( { provider_name: '<img onerror=alert(1)>' } ) );

		expect( html ).not.toContain( '<img onerror' );
	} );

	it( 'static buildStarsHTML returns stars with rating', () => {
		const html = SlotCardBuilder.buildStarsHTML( 4 );
		expect( html ).toContain( '4/5' );
	} );

	it( 'no star section when star_rating is 0', () => {
		const builder = new SlotCardBuilder( defaultConfig );
		const html = builder.build( makeSlot( { star_rating: 0 } ) );
		expect( html ).not.toContain( 'wr-sc-slot-card__rating' );
	} );

	it( 'no provider span when provider_name is empty', () => {
		const builder = new SlotCardBuilder( defaultConfig );
		const html = builder.build( makeSlot( { provider_name: '' } ) );
		expect( html ).not.toContain( 'wr-sc-slot-card__provider' );
	} );

	it( 'all 5 full stars for rating 5', () => {
		const builder = new SlotCardBuilder( defaultConfig );
		const html = builder.build( makeSlot( { star_rating: 5 } ) );
		expect( html ).toContain( '5/5' );
		const fullStars = ( html.match( /color:#f0b429/g ) || [] ).length;
		expect( fullStars ).toBe( 5 );
	} );

	it( 'detailUrl empty falls back to plain query param', () => {
		const builder = new SlotCardBuilder( { ...defaultConfig, detailUrl: '' } );
		const html = builder.build( makeSlot() );
		expect( html ).toContain( 'href="?slot_detail=1"' );
	} );

	it( 'star border style applied when starBorder is set', () => {
		const builder = new SlotCardBuilder( { ...defaultConfig, starBorder: '#000' } );
		const html = builder.build( makeSlot( { star_rating: 3 } ) );
		expect( html ).toContain( '-webkit-text-stroke:1px #000' );
	} );
} );
