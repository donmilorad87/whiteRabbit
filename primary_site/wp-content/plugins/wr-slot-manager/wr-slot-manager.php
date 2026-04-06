<?php
/**
 * Plugin Name: WR Slot Manager
 * Plugin URI:  https://wiserabbit.com
 * Description: Primary slot management plugin - CRUD, REST API, webhook push to connected sites.
 * Version:     1.0.0
 * Author:      WiseRabbit
 * Author URI:  https://wiserabbit.com
 * License:     GPL-2.0-or-later
 * Text Domain: wr-slot-manager
 * Domain Path: /languages
 *
 * @package WiseRabbit\SlotManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WR_SM_VERSION', '1.0.0' );
define( 'WR_SM_PLUGIN_FILE', __FILE__ );
define( 'WR_SM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WR_SM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WR_SM_OPTION_PREFIX', 'wr_sm_' );
define( 'WR_SM_CACHE_GROUP', 'wr_slot_manager' );
define( 'WR_SM_CACHE_EXPIRY', YEAR_IN_SECONDS );

require __DIR__ . '/plugin.php';
