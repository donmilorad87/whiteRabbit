/**
 * WR Slot Manager — Admin entry point.
 *
 * @package WiseRabbit\SlotManager
 */

import '../../scss/admin/admin.scss';
import { FetchHandler } from './classes/FetchHandler.ts';
import { ApiKeyForm } from './classes/ApiKeyForm.ts';
import { ConnectedSitesForm } from './classes/ConnectedSitesForm.ts';
import { SettingsForm } from './classes/SettingsForm.ts';

class AdminApp {

	private fetchHandler: FetchHandler;

	constructor() {
		const dialog = document.getElementById( 'wr-sm-loading-dialog' ) as HTMLDialogElement;
		this.fetchHandler = new FetchHandler( dialog );

		this.initTabs();
		this.initApiKeyForm();
		this.initConnectedSites();
		this.initSettingsForm();
	}

	initTabs(): void {
		document.querySelectorAll<HTMLAnchorElement>( '.wr-tab' ).forEach( ( tab ) => {
			tab.addEventListener( 'click', ( e: Event ) => {
				e.preventDefault();
				const target = ( tab as HTMLAnchorElement ).dataset.tab;
				if ( ! target ) return;

				document.querySelectorAll( '.wr-tab' ).forEach( ( t ) => t.classList.remove( 'nav-tab-active' ) );
				tab.classList.add( 'nav-tab-active' );

				document.querySelectorAll<HTMLElement>( '.wr-tab-panel' ).forEach( ( p ) => { p.hidden = true; } );
				const panel = document.getElementById( 'tab-' + target );
				if ( panel ) panel.hidden = false;

				history.replaceState( null, '', tab.href );
			} );
		} );

		// Activate tab from URL hash on load.
		const hash = window.location.hash.replace( '#', '' );
		if ( hash ) {
			const tab = document.querySelector<HTMLAnchorElement>( `.wr-tab[data-tab="${ hash }"]` );
			if ( tab ) tab.click();
		}
	}

	initApiKeyForm(): void {
		const form = document.getElementById( 'wr-sm-api-key-form' ) as HTMLFormElement | null;
		if ( form ) {
			new ApiKeyForm( form, this.fetchHandler );
		}
	}

	initConnectedSites(): void {
		if ( document.getElementById( 'wr-sm-add-site-form' ) ) {
			new ConnectedSitesForm( this.fetchHandler );
		}
	}

	initSettingsForm(): void {
		const form = document.getElementById( 'wr-sm-settings-form' ) as HTMLFormElement | null;
		if ( form ) {
			new SettingsForm( form, this.fetchHandler );
		}
	}
}

document.addEventListener( 'DOMContentLoaded', () => {
	new AdminApp();
} );
