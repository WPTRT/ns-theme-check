<?php
/**
 * Plugin Name:       NS Theme Check
 * Plugin URI:        https://github.com/ernilambar/ns-theme-check
 * Description:       Theme Check using sniffs.
 * Version:           0.1.2
 * Author:            Nilambar Sharma
 * Author URI:        http://nilambar.net
 * Text Domain:       ns-theme-check
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package NS_Theme_Check
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'NS_THEME_CHECK_BASENAME', basename( dirname( __FILE__ ) ) );
define( 'NS_THEME_CHECK_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'NS_THEME_CHECK_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );

// Load helpers.
require_once NS_THEME_CHECK_DIR . '/inc/helpers.php';

// Load admin.
require_once NS_THEME_CHECK_DIR . '/inc/admin.php';

/**
 * Add go to theme check page link on plugin page.
 *
 * @since 0.1.3
 */
function ns_theme_check_plugin_settings_link( $links ) {
	$theme_check_link = '<a href="themes.php?page=ns-theme-check">' . esc_attr( 'Theme Check Page', 'ns-theme-check' ) . '</a>';
	array_unshift( $links, $theme_check_link );
	return $links;
}

$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'ns_theme_check_plugin_settings_link' );
