/**
 * Centralized fetch handler with dialog loading mask and Toastify messages.
 * Toastify is loaded as a global via wp_enqueue_script.
 *
 * @package WiseRabbit\SlotConsumer
 */

interface AjaxResult {
	success: boolean;
	data: { message?: string } & Record<string, unknown>;
}

export class FetchHandler {

	public dialog: HTMLDialogElement;

	constructor( dialog: HTMLDialogElement ) {
		this.dialog = dialog;
	}

	fetch( data: FormData | URLSearchParams, callback: ( ( data: Record<string, unknown> ) => void ) | null = null, reload: boolean = false ): void {
		this.dialog.showModal();

		let willReload = false;

		window.fetch( window.wrScAdmin.ajaxUrl, {
			method: 'POST',
			body: data,
			credentials: 'same-origin',
		} )
			.then( ( response: Response ) => response.json() )
			.then( ( result: AjaxResult ) => {
				if ( result.success ) {
					this.toast( result.data.message || 'Success.', '#00a32a' );

					if ( callback ) {
						callback( result.data );
					}

					if ( reload ) {
						willReload = true;
						setTimeout( () => location.reload(), 800 );
						return;
					}
				} else {
					this.toast( result.data.message || 'Operation failed.', '#d63638' );
				}
			} )
			.catch( ( error: Error ) => {
				this.toast( 'Network error: ' + error.message, '#d63638' );
			} )
			.finally( () => {
				if ( ! willReload ) {
					this.dialog.close();
				}
			} );
	}

	toast( text: string, background: string ): void {
		Toastify( {
			text,
			style: { background },
			duration: background === '#d63638' ? 4000 : 3000,
			gravity: 'top',
			position: 'right',
			offset: { x: 0, y: 32 },
		} ).showToast();
	}
}
