import { test, expect } from '@playwright/test';

// Popup tests require /etc/hosts: 127.0.0.1 sec.wiserabbit.com
// so the browser can load WP-enqueued JS assets from sec.wiserabbit.com:81.
test.describe( 'Slot Grid Popup', () => {

	test( 'more info button opens dialog', async ( { page } ) => {
		await page.goto( '/test-page/' );
		const btn = page.locator( '.wr-sc-popup-trigger' ).first();
		await expect( btn ).toBeVisible();
		await btn.click();

		const dialog = page.locator( '#wr-sc-dialog' );
		await expect( dialog ).toBeVisible();
	} );

	test( 'dialog shows slot title', async ( { page } ) => {
		await page.goto( '/test-page/' );
		await page.locator( '.wr-sc-popup-trigger' ).first().click();

		const title = page.locator( '.wr-sc-dialog__title' );
		await expect( title ).not.toBeEmpty();
	} );

	test( 'dialog close button works', async ( { page } ) => {
		await page.goto( '/test-page/' );
		await page.locator( '.wr-sc-popup-trigger' ).first().click();

		const dialog = page.locator( '#wr-sc-dialog' );
		await expect( dialog ).toBeVisible();

		await page.locator( '.wr-sc-dialog__close' ).click();
		await expect( dialog ).not.toBeVisible();
	} );

	test( 'dialog shows provider and rtp', async ( { page } ) => {
		await page.goto( '/test-page/' );
		await page.locator( '.wr-sc-popup-trigger' ).first().click();

		const provider = page.locator( '.wr-sc-dialog__provider dd' );
		await expect( provider ).not.toBeEmpty();
	} );
} );
