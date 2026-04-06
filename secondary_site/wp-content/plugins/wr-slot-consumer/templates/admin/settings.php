<?php
/**
 * Unified settings page — all tabs on one page, JS-based tab switching.
 *
 * @package WiseRabbit\SlotConsumer
 * @var string $source_url   Current source URL.
 * @var string $api_key      Current API key.
 * @var int    $cache_expiry Cache expiry in minutes.
 * @var int    $rate_limit   API rate limit per minute per IP.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wr-sc-admin">
	<h1><?php esc_html_e( 'Slot Consumer Settings', 'wr-slot-consumer' ); ?></h1>

	<nav class="nav-tab-wrapper wr-tabs">
		<a href="#connection" class="nav-tab nav-tab-active wr-tab" data-tab="connection"><?php esc_html_e( 'Connection', 'wr-slot-consumer' ); ?></a>
		<a href="#api" class="nav-tab wr-tab" data-tab="api"><?php esc_html_e( 'API Settings', 'wr-slot-consumer' ); ?></a>
		<a href="#cache" class="nav-tab wr-tab" data-tab="cache"><?php esc_html_e( 'Cache', 'wr-slot-consumer' ); ?></a>
		<a href="#sync" class="nav-tab wr-tab" data-tab="sync"><?php esc_html_e( 'Manual Sync', 'wr-slot-consumer' ); ?></a>
	</nav>

	<!-- Tab: Connection -->
	<div class="wr-tab-panel" id="tab-connection">
		<div class="wr-card">
			<h2><?php esc_html_e( 'Primary Site Connection', 'wr-slot-consumer' ); ?></h2>
			<form method="post" id="wr-sc-settings-form">
				<?php wp_nonce_field( 'wr_sc_settings_save', 'wr_sc_settings_nonce' ); ?>
				<input type="hidden" name="wr_sc_settings_action" value="save" />
				<table class="form-table">
					<tr>
						<th><label for="wr_sc_source_url"><?php esc_html_e( 'Source Site URL', 'wr-slot-consumer' ); ?></label></th>
						<td>
							<input type="url" id="wr_sc_source_url" name="wr_sc_source_url" value="<?php echo esc_attr( $source_url ); ?>" class="regular-text" placeholder="http://local.wiserabbit.com" />
							<p class="description"><?php esc_html_e( 'Full URL of the primary site running WR Slot Manager.', 'wr-slot-consumer' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save Settings', 'wr-slot-consumer' ) ); ?>
			</form>
		</div>
	</div>

	<!-- Tab: API Settings -->
	<div class="wr-tab-panel" id="tab-api" hidden>
		<div class="wr-card">
			<h2><?php esc_html_e( 'API Settings', 'wr-slot-consumer' ); ?></h2>
			<form method="post" id="wr-sc-api-form">
				<?php wp_nonce_field( 'wr_sc_settings_save', 'wr_sc_settings_nonce' ); ?>
				<input type="hidden" name="wr_sc_settings_action" value="save" />
				<table class="form-table">
					<tr>
						<th><label for="wr_sc_api_key"><?php esc_html_e( 'API Key', 'wr-slot-consumer' ); ?></label></th>
						<td>
							<input type="text" id="wr_sc_api_key" name="wr_sc_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Must match the API key configured on the primary site.', 'wr-slot-consumer' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="wr_sc_rate_limit"><?php esc_html_e( 'Rate Limit (requests/minute)', 'wr-slot-consumer' ); ?></label></th>
						<td>
							<input type="number" id="wr_sc_rate_limit" name="wr_sc_rate_limit" value="<?php echo esc_attr( $rate_limit ); ?>" class="small-text" min="1" />
							<p class="description"><?php esc_html_e( 'Maximum webhook requests per IP address per minute.', 'wr-slot-consumer' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save Settings', 'wr-slot-consumer' ) ); ?>
			</form>
		</div>
	</div>

	<!-- Tab: Cache -->
	<div class="wr-tab-panel" id="tab-cache" hidden>
		<div class="wr-card">
			<h2><?php esc_html_e( 'Cache Configuration', 'wr-slot-consumer' ); ?></h2>
			<form method="post" id="wr-sc-cache-form">
				<?php wp_nonce_field( 'wr_sc_settings_save', 'wr_sc_settings_nonce' ); ?>
				<input type="hidden" name="wr_sc_settings_action" value="save" />
				<table class="form-table">
					<tr>
						<th><label for="wr_sc_cache_expiry"><?php esc_html_e( 'Cache Expiry (minutes)', 'wr-slot-consumer' ); ?></label></th>
						<td>
							<input type="number" id="wr_sc_cache_expiry" name="wr_sc_cache_expiry" value="<?php echo esc_attr( $cache_expiry ); ?>" class="small-text" min="1" />
							<p class="description"><?php esc_html_e( 'How long slot data is cached before refreshing from the primary site.', 'wr-slot-consumer' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save Settings', 'wr-slot-consumer' ) ); ?>
			</form>
		</div>
	</div>

	<!-- Tab: Manual Sync -->
	<div class="wr-tab-panel" id="tab-sync" hidden>
		<div class="wr-card">
			<h2><?php esc_html_e( 'Manual Sync', 'wr-slot-consumer' ); ?></h2>
			<p><?php esc_html_e( 'Fetch all slot data from the primary site and rebuild the local cache.', 'wr-slot-consumer' ); ?></p>
			<button type="button" id="wr-sc-sync-btn" class="button button-primary">
				<?php esc_html_e( 'Sync Data', 'wr-slot-consumer' ); ?>
			</button>
		</div>
	</div>

	<dialog id="wr-sc-loading-dialog">
		<div class="wr-ripple"><div></div><div></div></div>
	</dialog>
</div>
