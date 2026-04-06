import { describe, it, expect, vi, beforeEach } from 'vitest';
import { FetchHandler } from '../js/admin/classes/FetchHandler.ts';

// Mock Toastify global.
globalThis.Toastify = vi.fn( () => ( { showToast: vi.fn() } ) );
globalThis.wrSmAdmin = { ajaxUrl: '/wp-admin/admin-ajax.php' };

function makeDialog() {
	const dialog = document.createElement( 'dialog' );
	dialog.showModal = vi.fn();
	dialog.close = vi.fn();
	return dialog;
}

describe( 'FetchHandler', () => {
	let handler;
	let dialog;

	beforeEach( () => {
		dialog = makeDialog();
		handler = new FetchHandler( dialog );
		vi.restoreAllMocks();
	} );

	it( 'stores dialog reference', () => {
		expect( handler.dialog ).toBe( dialog );
	} );

	it( 'opens dialog on fetch', async () => {
		globalThis.fetch = vi.fn( () =>
			Promise.resolve( { json: () => Promise.resolve( { success: true, data: { message: 'OK' } } ) } )
		);

		const data = new FormData();
		handler.fetch( data );

		expect( dialog.showModal ).toHaveBeenCalledOnce();
	} );

	it( 'closes dialog after successful non-reload fetch', async () => {
		globalThis.fetch = vi.fn( () =>
			Promise.resolve( { json: () => Promise.resolve( { success: true, data: { message: 'OK' } } ) } )
		);

		handler.fetch( new FormData() );
		await vi.waitFor( () => expect( dialog.close ).toHaveBeenCalled() );
	} );

	it( 'closes dialog on error', async () => {
		globalThis.fetch = vi.fn( () => Promise.reject( new Error( 'Network fail' ) ) );

		handler.fetch( new FormData() );
		await vi.waitFor( () => expect( dialog.close ).toHaveBeenCalled() );
	} );

	it( 'calls Toastify on success', async () => {
		globalThis.fetch = vi.fn( () =>
			Promise.resolve( { json: () => Promise.resolve( { success: true, data: { message: 'Saved' } } ) } )
		);

		handler.fetch( new FormData() );
		await vi.waitFor( () => expect( Toastify ).toHaveBeenCalled() );
	} );

	it( 'calls callback on success', async () => {
		const callback = vi.fn();
		globalThis.fetch = vi.fn( () =>
			Promise.resolve( { json: () => Promise.resolve( { success: true, data: { message: 'OK', extra: 42 } } ) } )
		);

		handler.fetch( new FormData(), callback );
		await vi.waitFor( () => expect( callback ).toHaveBeenCalledWith( { message: 'OK', extra: 42 } ) );
	} );
} );
