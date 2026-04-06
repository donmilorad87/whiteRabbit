/**
 * Frontend entry point for Slot Grid interactions (load more + popup).
 *
 * @package WiseRabbit\SlotConsumer
 */

import { SlotLoadMore } from './SlotLoadMore.ts';
import { SlotPopup } from './SlotPopup.ts';

( function (): void {
	const loadMoreEl = document.querySelector( '.wr-sc-loadmore' ) as HTMLElement | null;
	if ( loadMoreEl ) {
		const grid = loadMoreEl.parentElement!.querySelector( '.wr-sc-slot-grid' ) as HTMLElement | null;
		if ( grid ) {
			new SlotLoadMore( loadMoreEl, grid );
		}
	}

	const dialog = document.getElementById( 'wr-sc-dialog' ) as HTMLDialogElement | null;
	if ( dialog ) {
		new SlotPopup( dialog );
	}
} )();
