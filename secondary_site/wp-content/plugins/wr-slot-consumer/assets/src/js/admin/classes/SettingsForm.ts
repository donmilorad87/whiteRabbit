/**
 * Settings form — saves via fetch with dialog mask.
 *
 * @package WiseRabbit\SlotConsumer
 */

import { FetchHandler } from './FetchHandler.ts';

export class SettingsForm {

	private form: HTMLFormElement;
	private fetchHandler: FetchHandler;

	constructor( form: HTMLFormElement, fetchHandler: FetchHandler ) {
		this.form         = form;
		this.fetchHandler = fetchHandler;

		this.form.addEventListener( 'submit', ( e: Event ) => this.handleSubmit( e ) );

		const apiKeyInput = document.getElementById( 'wr_sc_api_key' ) as HTMLInputElement | null;
		if ( apiKeyInput ) {
			apiKeyInput.setAttribute( 'autocomplete', 'off' );
		}
	}

	handleSubmit( e: Event ): void {
		e.preventDefault();

		const formData = new FormData( this.form );
		formData.append( 'action', 'wr_sc_save_settings' );

		this.fetchHandler.fetch( formData );
	}
}
