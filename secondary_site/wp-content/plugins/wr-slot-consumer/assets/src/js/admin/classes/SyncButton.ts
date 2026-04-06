/**
 * Sync button — triggers full data sync via fetch with dialog mask.
 *
 * @package WiseRabbit\SlotConsumer
 */

import { FetchHandler } from './FetchHandler.ts';

export class SyncButton {

	private fetchHandler: FetchHandler;

	constructor( button: HTMLButtonElement, fetchHandler: FetchHandler ) {
		this.fetchHandler = fetchHandler;

		button.addEventListener( 'click', () => this.sync() );
	}

	sync(): void {
		const formData = new FormData();
		formData.append( 'action', 'wr_sc_sync_data' );
		formData.append( 'nonce', window.wrScAdmin.syncNonce );

		this.fetchHandler.fetch( formData );
	}
}
