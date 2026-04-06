<?php
/**
 * Unified settings page — all tabs on one page, JS-based tab switching.
 *
 * @package WiseRabbit\SlotManager
 * @var array  $sites        Connected site URLs.
 * @var string $api_key      Current API key.
 * @var int    $cache_expiry Cache expiry in minutes.
 * @var int    $rate_limit   API rate limit per minute per IP.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wr-sm-admin">
	<h1><?php esc_html_e( 'Slot Manager Settings', 'wr-slot-manager' ); ?></h1>

	<nav class="nav-tab-wrapper wr-tabs">
		<a href="#connected-sites" class="nav-tab nav-tab-active wr-tab" data-tab="connected-sites"><?php esc_html_e( 'Connected Sites', 'wr-slot-manager' ); ?></a>
		<a href="#api" class="nav-tab wr-tab" data-tab="api"><?php esc_html_e( 'API Settings', 'wr-slot-manager' ); ?></a>
		<a href="#cache" class="nav-tab wr-tab" data-tab="cache"><?php esc_html_e( 'Cache Configuration', 'wr-slot-manager' ); ?></a>
	</nav>

	<!-- Tab: Connected Sites -->
	<div class="wr-tab-panel" id="tab-connected-sites">
		<div class="wr-card">
			<h2><?php esc_html_e( 'Add New Site', 'wr-slot-manager' ); ?></h2>
			<form method="post" id="wr-sm-add-site-form">
				<?php wp_nonce_field( 'wr_sm_sites_save', 'wr_sm_sites_nonce' ); ?>
				<input type="hidden" name="wr_sm_sites_action" value="add" />
				<table class="form-table">
					<tr>
						<th><label for="wr_sm_site_url"><?php esc_html_e( 'Site URL', 'wr-slot-manager' ); ?></label></th>
						<td>
							<input type="url" id="wr_sm_site_url" name="wr_sm_site_url" class="regular-text" placeholder="https://example.com" required />
							<p class="description"><?php esc_html_e( 'Full URL of the secondary site (include port if needed).', 'wr-slot-manager' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Add Site', 'wr-slot-manager' ), 'primary', 'submit', false ); ?>
			</form>
		</div>

		<div class="wr-card">
			<h2><?php esc_html_e( 'Registered Sites', 'wr-slot-manager' ); ?></h2>
			<?php if ( empty( $sites ) ) : ?>
				<p><?php esc_html_e( 'No connected sites yet.', 'wr-slot-manager' ); ?></p>
			<?php else : ?>
				<table class="wp-list-table widefat striped wr-sites-table">
					<thead>
						<tr>
							<th class="wr-sites-table__col-num"><?php esc_html_e( '#', 'wr-slot-manager' ); ?></th>
							<th class="wr-sites-table__col-url"><?php esc_html_e( 'Site URL', 'wr-slot-manager' ); ?></th>
							<th class="wr-sites-table__col-actions"><?php esc_html_e( 'Actions', 'wr-slot-manager' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $sites as $index => $site_url ) : ?>
							<tr>
								<td><?php echo esc_html( $index + 1 ); ?></td>
								<td><?php echo esc_url( $site_url ); ?></td>
								<td>
									<div class="wr-sites-table__actions">
										<form method="post" class="wr-sm-inline-form wr-sites-table__edit-form">
											<?php wp_nonce_field( 'wr_sm_sites_save', 'wr_sm_sites_nonce' ); ?>
											<input type="hidden" name="wr_sm_sites_action" value="edit" />
											<input type="hidden" name="wr_sm_site_index" value="<?php echo esc_attr( $index ); ?>" />
											<input type="url" name="wr_sm_site_url" value="<?php echo esc_attr( $site_url ); ?>" class="wr-sites-table__url-input" required />
											<?php submit_button( __( 'Update', 'wr-slot-manager' ), 'small', 'submit', false ); ?>
										</form>
										<form method="post" class="wr-sm-inline-form wr-sm-remove-form">
											<?php wp_nonce_field( 'wr_sm_sites_save', 'wr_sm_sites_nonce' ); ?>
											<input type="hidden" name="wr_sm_sites_action" value="remove" />
											<input type="hidden" name="wr_sm_site_index" value="<?php echo esc_attr( $index ); ?>" />
											<?php submit_button( __( 'Remove', 'wr-slot-manager' ), 'delete small', 'submit', false ); ?>
										</form>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>

	<!-- Tab: API Settings -->
	<div class="wr-tab-panel" id="tab-api" hidden>
		<div class="wr-card">
			<h2><?php esc_html_e( 'API Key', 'wr-slot-manager' ); ?></h2>
			<?php if ( ! empty( $api_key ) ) : ?>
				<p>
					<code id="wr-sm-api-key"><?php echo esc_html( $api_key ); ?></code>
				</p>
				<p class="description">
					<?php esc_html_e( 'Share this key with secondary sites for authentication. Keep it secure.', 'wr-slot-manager' ); ?>
				</p>
			<?php else : ?>
				<p><?php esc_html_e( 'No API key has been generated yet.', 'wr-slot-manager' ); ?></p>
			<?php endif; ?>

			<form method="post" id="wr-sm-api-key-form">
				<?php wp_nonce_field( 'wr_sm_api_key_save', 'wr_sm_api_key_nonce' ); ?>
				<input type="hidden" name="wr_sm_api_key_action" value="generate" />
				<?php
				submit_button(
					empty( $api_key )
						? __( 'Generate API Key', 'wr-slot-manager' )
						: __( 'Regenerate API Key', 'wr-slot-manager' ),
					'primary',
					'submit',
					false
				);
				?>
			</form>
		</div>

		<div class="wr-card">
			<h2><?php esc_html_e( 'API Rate Limit', 'wr-slot-manager' ); ?></h2>
			<form method="post" id="wr-sm-settings-form">
				<?php wp_nonce_field( 'wr_sm_settings_save', 'wr_sm_settings_nonce' ); ?>
				<input type="hidden" name="wr_sm_cache_action" value="save" />
				<table class="form-table">
					<tr>
						<th><label for="wr_sm_rate_limit"><?php esc_html_e( 'Requests per minute', 'wr-slot-manager' ); ?></label></th>
						<td>
							<input type="number" id="wr_sm_rate_limit" name="wr_sm_rate_limit" value="<?php echo esc_attr( $rate_limit ); ?>" class="small-text" min="1" />
							<p class="description"><?php esc_html_e( 'Maximum REST API requests per IP address per minute.', 'wr-slot-manager' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save Settings', 'wr-slot-manager' ) ); ?>
			</form>
		</div>
	</div>

	<!-- Tab: Cache Configuration -->
	<div class="wr-tab-panel" id="tab-cache" hidden>
		<div class="wr-card">
			<h2><?php esc_html_e( 'Redis Cache', 'wr-slot-manager' ); ?></h2>
			<form method="post" id="wr-sm-cache-form">
				<?php wp_nonce_field( 'wr_sm_settings_save', 'wr_sm_settings_nonce' ); ?>
				<input type="hidden" name="wr_sm_cache_action" value="save" />
				<table class="form-table">
					<tr>
						<th><label for="wr_sm_cache_expiry"><?php esc_html_e( 'Cache Expiry (minutes)', 'wr-slot-manager' ); ?></label></th>
						<td>
							<input type="number" id="wr_sm_cache_expiry" name="wr_sm_cache_expiry" value="<?php echo esc_attr( $cache_expiry ); ?>" class="small-text" min="1" />
							<p class="description"><?php esc_html_e( 'How long slot data is stored in Redis before rebuilding from the database.', 'wr-slot-manager' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save Settings', 'wr-slot-manager' ) ); ?>
			</form>
		</div>
	</div>

	<dialog id="wr-sm-loading-dialog">
		<div class="wr-ripple"><div></div><div></div></div>
	</dialog>
</div>
