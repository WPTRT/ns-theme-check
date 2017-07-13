<?php
/**
 * Plugin Name:       Theme Sniffer
 * Plugin URI:        https://github.com/ernilambar/theme-sniffer
 * Description:       Theme Sniffer plugin which uses phpcs for automatic theme checking.
 * Version:           0.1.4
 * Author:            Nilambar Sharma
 * Author URI:        http://nilambar.net
 * Text Domain:       theme-sniffer
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package Theme_Sniffer
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'admin_init', 'theme_sniffer_check_php' );

/**
 * Check php function hook
 *
 * Hooks to the init hook, checks for the php version - if lower than 5.3
 * will disable the plugin and add a notice.
 *
 * @return void
 */
function theme_sniffer_check_php() {
	// If php version is lower than 5.3, abort.
	if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
		$plugin = plugin_basename( __FILE__ );

		if ( is_plugin_active( $plugin ) ) {
			deactivate_plugins( $plugin );
			add_action( 'admin_notices', 'theme_sniffer_error_activation_notice' );
			remove_filter( 'plugin_action_links_' . $plugin, 'theme_sniffer_plugin_settings_link' );
			unset( $_GET['activate'] );
		}
	}
}

/**
 * Activation error message hook.
 *
 * Hooks to admin_notices hook and outputs the message on activation error.
 *
 * @return void
 */
function theme_sniffer_error_activation_notice() {
	?>
	<div class="error">
		<p><?php _e( 'Theme Sniffer requires PHP 5.3 or greater to function.', 'theme-sniffer' ); ?></p>
	</div>
	<?php
}

define( 'THEME_SNIFFER_BASENAME', plugin_basename( __FILE__ ) );
define( 'THEME_SNIFFER_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'THEME_SNIFFER_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );

// Load helpers.
require_once THEME_SNIFFER_DIR . '/inc/helpers.php';

// Load admin.
require_once THEME_SNIFFER_DIR . '/inc/admin.php';
require_once THEME_SNIFFER_DIR . '/inc/checks.php';
