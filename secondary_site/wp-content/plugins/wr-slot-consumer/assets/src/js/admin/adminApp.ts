/**
 * WR Slot Consumer — Admin entry point.
 *
 * @package WiseRabbit\SlotConsumer
 */

import '../../scss/admin/admin.scss';
import { FetchHandler } from './classes/FetchHandler.ts';
import { SettingsForm } from './classes/SettingsForm.ts';
import { SyncButton } from './classes/SyncButton.ts';

class AdminApp {

	private fetchHandler: FetchHandler;

	constructor() {
		const dialog = document.getElementById( 'wr-sc-loading-dialog' ) as HTMLDialogElement;
		this.fetchHandler = new FetchHandler( dialog );

		this.initTabs();
		this.initSettingsForms();
		this.initSyncButton();
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

		const hash = window.location.hash.replace( '#', '' );
		if ( hash ) {
			const tab = document.querySelector<HTMLAnchorElement>( `.wr-tab[data-tab="${ hash }"]` );
			if ( tab ) tab.click();
		}
	}

	initSettingsForms(): void {
		document.querySelectorAll<HTMLFormElement>( 'form[id^="wr-sc-"]' ).forEach( ( form ) => {
			new SettingsForm( form, this.fetchHandler );
		} );
	}

	initSyncButton(): void {
		const btn = document.getElementById( 'wr-sc-sync-btn' ) as HTMLButtonElement | null;
		if ( btn ) {
			new SyncButton( btn, this.fetchHandler );
		}
	}
}

document.addEventListener( 'DOMContentLoaded', () => {
	new AdminApp();
} );
