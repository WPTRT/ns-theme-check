<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since   1.0.0 Moved to new namespace and modified the class
 * @package Theme_Sniffer\i18n
 */

namespace Theme_Sniffer\i18n;

use Theme_Sniffer\Core\Service;


/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since   2.0.0 Added register method
 * @since   0.1.0
 * @since   1.0.0 Updated name.
 * @package Theme_Sniffer\i18n
 */
class Internationalization implements Service {
	/**
	 * Plugin text domain
	 */
	const TEXT_DOMAIN = 'theme-sniffer';

	/**
	 * Register the textdomain.
	 */
	public function register() {
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 0.1.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			self::TEXT_DOMAIN,
			false,
			dirname( plugin_basename( __FILE__ ), 3 ) . '/languages/'
		);
	}
}
