<?php
/**
 * PHPUnit bootstrap for WR Slot Manager standalone tests.
 * Loads only the autoloader, not WordPress or the full plugin init.
 */

define( 'ABSPATH', '/tmp/fake-wp/' );
define( 'WR_SM_PLUGIN_FILE', dirname( __DIR__, 2 ) . '/wr-slot-manager.php' );
define( 'WR_SM_PLUGIN_DIR', dirname( __DIR__, 2 ) . '/' );
define( 'WR_SM_PLUGIN_URL', 'http://localhost/wp-content/plugins/wr-slot-manager/' );
define( 'WR_SM_OPTION_PREFIX', 'wr_sm_' );
define( 'WR_SM_CACHE_GROUP', 'wr_slot_manager' );
define( 'WR_SM_CACHE_EXPIRY', 3600 );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'YEAR_IN_SECONDS', 31536000 );
define( 'ENVIRONMENT', 'dev' );

require dirname( __DIR__, 2 ) . '/vendor/autoload.php';

// Stub WP_Error if not already defined.
if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		private $code;
		private $message;
		private $data;
		public function __construct( $code = '', $message = '', $data = '' ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}
		public function get_error_code()    { return $this->code; }
		public function get_error_message() { return $this->message; }
		public function get_error_data()    { return $this->data; }
	}
}

// Stub WP_REST_Request if not already defined.
if ( ! class_exists( 'WP_REST_Request' ) ) {
	class WP_REST_Request {
		private $headers = array();
		public function set_header( $key, $value ) { $this->headers[ strtolower( $key ) ] = $value; }
		public function get_header( $key )          { return $this->headers[ strtolower( $key ) ] ?? null; }
	}
}

// Stub hash_equals if not available.
if ( ! function_exists( 'hash_equals' ) ) {
	function hash_equals( $known, $user ) { return $known === $user; }
}

// Stub untrailingslashit if not available.
if ( ! function_exists( 'untrailingslashit' ) ) {
	function untrailingslashit( $string ) { return rtrim( $string, '/\\' ); }
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) { return $thing instanceof WP_Error; }
}

// Register the VIP autoloader manually (same as plugin.php but without WP hooks).
spl_autoload_register(
	function ( $class ) {
		$prefix   = 'WiseRabbit\\SlotManager\\';
		$base_dir = dirname( __DIR__, 2 ) . '/includes/';
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
