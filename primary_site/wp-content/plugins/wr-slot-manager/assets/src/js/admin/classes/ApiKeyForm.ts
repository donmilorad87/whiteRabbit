/**
 * API Key form — generates key via AJAX with dialog mask + Toastify.
 *
 * @package WiseRabbit\SlotManager
 */

import { FetchHandler } from './FetchHandler.ts';

export class ApiKeyForm {

	private form: HTMLFormElement;
	private fetchHandler: FetchHandler;
	private keyEl: HTMLElement | null;

	constructor( form: HTMLFormElement, fetchHandler: FetchHandler ) {
		this.form         = form;
		this.fetchHandler = fetchHandler;
		this.keyEl        = document.getElementById( 'wr-sm-api-key' );

		this.addCopyButton();
		this.form.addEventListener( 'submit', ( e: Event ) => this.handleSubmit( e ) );
	}

	handleSubmit( e: Event ): void {
		e.preventDefault();

		const formData = new FormData( this.form );
		formData.append( 'action', 'wr_sm_generate_api_key' );

		this.fetchHandler.fetch( formData, ( data: Record<string, unknown> ) => {
			if ( this.keyEl && data.key ) {
				this.keyEl.textContent = data.key as string;
			}
		} );
	}

	addCopyButton(): void {
		if ( ! this.keyEl ) {
			return;
		}

		const btn       = document.createElement( 'button' );
		btn.type        = 'button';
		btn.className   = 'button button-small';
		btn.textContent = 'Copy';
		btn.style.marginLeft = '8px';

		btn.addEventListener( 'click', () => {
			const text = this.keyEl!.textContent || '';

			if ( navigator.clipboard && window.isSecureContext ) {
				navigator.clipboard.writeText( text ).then( () => {
					btn.textContent = 'Copied!';
					setTimeout( () => { btn.textContent = 'Copy'; }, 2000 );
				} );
			} else {
				const textarea = document.createElement( 'textarea' );
				textarea.value = text;
				textarea.style.position = 'fixed';
				textarea.style.opacity = '0';
				document.body.appendChild( textarea );
				textarea.select();
				document.execCommand( 'copy' );
				document.body.removeChild( textarea );
				btn.textContent = 'Copied!';
				setTimeout( () => { btn.textContent = 'Copy'; }, 2000 );
			}
		} );

		this.keyEl.parentNode!.insertBefore( btn, this.keyEl.nextSibling );
	}
}
