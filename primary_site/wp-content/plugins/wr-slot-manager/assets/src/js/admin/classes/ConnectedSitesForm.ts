/**
 * Connected Sites forms — all operations via AJAX with dialog mask + Toastify.
 * Reloads page after success to reflect table changes.
 *
 * @package WiseRabbit\SlotManager
 */

import { FetchHandler } from './FetchHandler.ts';

export class ConnectedSitesForm {

	private fetchHandler: FetchHandler;

	constructor( fetchHandler: FetchHandler ) {
		this.fetchHandler = fetchHandler;
		this.initAddForm();
		this.initInlineForms();
	}

	initAddForm(): void {
		const form = document.getElementById( 'wr-sm-add-site-form' ) as HTMLFormElement | null;
		if ( ! form ) {
			return;
		}

		form.addEventListener( 'submit', ( e: Event ) => {
			e.preventDefault();
			const formData = new FormData( form );
			formData.append( 'action', 'wr_sm_sites_action' );
			this.fetchHandler.fetch( formData, null, true );
		} );
	}

	initInlineForms(): void {
		document.querySelectorAll<HTMLFormElement>( '.wr-sm-inline-form' ).forEach( ( form ) => {
			const isRemove = form.classList.contains( 'wr-sm-remove-form' );

			form.addEventListener( 'submit', ( e: Event ) => {
				e.preventDefault();

				if ( isRemove && ! window.confirm( 'Are you sure you want to remove this site?' ) ) {
					return;
				}

				const formData = new FormData( form );
				formData.append( 'action', 'wr_sm_sites_action' );
				this.fetchHandler.fetch( formData, null, true );
			} );
		} );
	}
}
