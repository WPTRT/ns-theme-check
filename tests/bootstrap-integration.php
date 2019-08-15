<?php
/**
 * PHPUnit bootstrap file
 *
 * Integration tests bootstrap file.
 *
 * @package Theme_Sniffer
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
tests_add_filter(
	'muplugins_loaded',
	function() {
		require dirname( __FILE__, 2 ) . '/theme-sniffer.php';
	}
);

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
