<?php
/**
 * Plugin Name: WR Slot Consumer
 * Plugin URI:  https://wiserabbit.com
 * Description: Secondary slot consumer - receives webhooks, syncs data, displays slots via Gutenberg block.
 * Version:     1.0.0
 * Author:      WiseRabbit
 * Author URI:  https://wiserabbit.com
 * License:     GPL-2.0-or-later
 * Text Domain: wr-slot-consumer
 * Domain Path: /languages
 *
 * @package WiseRabbit\SlotConsumer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WR_SC_VERSION', '1.0.0' );
define( 'WR_SC_PLUGIN_FILE', __FILE__ );
define( 'WR_SC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WR_SC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WR_SC_OPTION_PREFIX', 'wr_sc_' );
define( 'WR_SC_CACHE_EXPIRY', HOUR_IN_SECONDS );

require __DIR__ . '/plugin.php';
