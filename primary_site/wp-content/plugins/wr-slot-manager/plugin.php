<?php
/**
 * Plugin initialization: autoloader, activation hook, i18n, and bootstrap.
 *
 * @package WiseRabbit\SlotManager
 */

namespace WiseRabbit\SlotManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * VIP-compliant autoloader.
 * Maps PascalCase namespaces to lowercase-hyphenated includes/ paths.
 *
 * WiseRabbit\SlotManager\Admin\ApiKeyPage  → includes/admin/class-api-key-page.php
 * WiseRabbit\SlotManager\Traits\LoggerTrait → includes/traits/trait-logger.php
 */
spl_autoload_register(
	function ( $class ) {
		$prefix   = 'WiseRabbit\\SlotManager\\';
		$base_dir = __DIR__ . '/includes/';
		$len      = strlen( $prefix );

		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		$parts          = explode( '\\', $relative_class );
		$class_name     = array_pop( $parts );

		$to_hyphen = function ( $segment ) {
			$result = preg_replace( '/([a-z])([A-Z])/', '$1-$2', $segment );
			$result = preg_replace( '/([A-Z]+)([A-Z][a-z])/', '$1-$2', $result );
			return strtolower( $result );
		};

		$dir_path = '';
		if ( ! empty( $parts ) ) {
			$dir_path = implode( '/', array_map( $to_hyphen, $parts ) ) . '/';
		}

		$is_trait = ( ! empty( $parts ) && 'Traits' === $parts[0] );

		if ( $is_trait ) {
			$file_name   = preg_replace( '/Trait$/', '', $class_name );
			$file_prefix = 'trait-';
		} else {
			$file_name   = $class_name;
			$file_prefix = 'class-';
		}

		$file = $base_dir . $dir_path . $file_prefix . $to_hyphen( $file_name ) . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

/**
 * Activation hook.
 */
register_activation_hook(
	WR_SM_PLUGIN_FILE,
	function () {
		require_once __DIR__ . '/activate.php';
		activate();
	}
);

/**
 * Deactivation hook.
 */
register_deactivation_hook(
	WR_SM_PLUGIN_FILE,
	function () {
		require_once __DIR__ . '/deactivate.php';
		deactivate();
	}
);

/**
 * Load translations.
 */
add_action(
	'init',
	function () {
		load_plugin_textdomain( 'wr-slot-manager', false, dirname( plugin_basename( WR_SM_PLUGIN_FILE ) ) . '/languages' );
	}
);

/**
 * Initialize the plugin.
 */
add_action(
	'plugins_loaded',
	function () {
		Plugin::get_instance();
	}
);
