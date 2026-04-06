import { test, expect } from '@playwright/test';

test.describe( 'Slot Grid Pagination', () => {

	test( 'renders slot cards on test page', async ( { page } ) => {
		await page.goto( '/test-page/' );
		const cards = page.locator( '.wr-sc-slot-card' );
		await expect( cards.first() ).toBeVisible();
		const count = await cards.count();
		expect( count ).toBe( 3 );
	} );

	test( 'shows pagination links', async ( { page } ) => {
		await page.goto( '/test-page/' );
		const nav = page.locator( '.wr-sc-pagination' );
		await expect( nav ).toBeVisible();
	} );

	test( 'navigates to next page', async ( { page } ) => {
		await page.goto( '/test-page/' );
		const nextLink = page.locator( '.wr-sc-pagination__link', { hasText: '»' } );
		await expect( nextLink ).toBeVisible();
		await nextLink.click();
		await expect( page ).toHaveURL( /sg_page=2/ );
		const cards = page.locator( '.wr-sc-slot-card' );
		await expect( cards.first() ).toBeVisible();
	} );

	test( 'navigates back with prev link', async ( { page } ) => {
		await page.goto( '/test-page/?sg_page=2' );
		const prevLink = page.locator( '.wr-sc-pagination__link', { hasText: '«' } );
		await expect( prevLink ).toBeVisible();
		await prevLink.click();
		await expect( page ).not.toHaveURL( /sg_page=2/ );
	} );

	test( 'clicking a page number navigates directly', async ( { page } ) => {
		await page.goto( '/test-page/' );
		const page2 = page.locator( '.wr-sc-pagination__link', { hasText: '2' } );
		if ( await page2.isVisible() ) {
			await page2.click();
			await expect( page ).toHaveURL( /sg_page=2/ );
		}
	} );

	test( 'active page is highlighted', async ( { page } ) => {
		await page.goto( '/test-page/' );
		const active = page.locator( '.wr-sc-pagination__link--active' );
		await expect( active ).toBeVisible();
		await expect( active ).toHaveText( '1' );
	} );
} );
