import { describe, it, expect, vi, beforeEach } from 'vitest';
import { FetchHandler } from '../js/admin/classes/FetchHandler.ts';

globalThis.Toastify = vi.fn( () => ( { showToast: vi.fn() } ) );
globalThis.wrScAdmin = { ajaxUrl: '/wp-admin/admin-ajax.php' };

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
		Toastify.mockClear();
	} );

	it( 'stores dialog reference', () => {
		expect( handler.dialog ).toBe( dialog );
	} );

	it( 'opens dialog on fetch', () => {
		globalThis.fetch = vi.fn( () =>
			Promise.resolve( { json: () => Promise.resolve( { success: true, data: { message: 'OK' } } ) } )
		);
		handler.fetch( new FormData() );
		expect( dialog.showModal ).toHaveBeenCalledOnce();
	} );

	it( 'closes dialog after successful non-reload fetch', async () => {
		globalThis.fetch = vi.fn( () =>
			Promise.resolve( { json: () => Promise.resolve( { success: true, data: { message: 'OK' } } ) } )
		);
		handler.fetch( new FormData() );
		await vi.waitFor( () => expect( dialog.close ).toHaveBeenCalled() );
	} );

	it( 'closes dialog on network error', async () => {
		globalThis.fetch = vi.fn( () => Promise.reject( new Error( 'fail' ) ) );
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

	it( 'calls Toastify with red on error response', async () => {
		globalThis.fetch = vi.fn( () =>
			Promise.resolve( { json: () => Promise.resolve( { success: false, data: { message: 'Fail' } } ) } )
		);
		handler.fetch( new FormData() );
		await vi.waitFor( () => {
			const call = Toastify.mock.calls[0][0];
			expect( call.style.background ).toBe( '#d63638' );
		} );
	} );

	it( 'calls callback with data on success', async () => {
		const callback = vi.fn();
		globalThis.fetch = vi.fn( () =>
			Promise.resolve( { json: () => Promise.resolve( { success: true, data: { message: 'OK', extra: 42 } } ) } )
		);
		handler.fetch( new FormData(), callback );
		await vi.waitFor( () => expect( callback ).toHaveBeenCalledWith( { message: 'OK', extra: 42 } ) );
	} );

	it( 'closes dialog on error when reload=true', async () => {
		globalThis.fetch = vi.fn( () =>
			Promise.resolve( { json: () => Promise.resolve( { success: false, data: { message: 'Fail' } } ) } )
		);
		handler.fetch( new FormData(), null, true );
		await vi.waitFor( () => expect( dialog.close ).toHaveBeenCalled() );
	} );
} );
