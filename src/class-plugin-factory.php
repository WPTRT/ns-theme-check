<?php
/**
 * File containing plugin factory
 *
 * @package Theme_Sniffer\Core
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Core;

/**
 * Class Plugin_Factory
 *
 * Used for creating the plugin instance.
 */
final class Plugin_Factory {
	/**
	 * Create and return an instance of the plugin.
	 *
	 * This always returns a shared instance.
	 *
	 * @return Plugin instance.
	 */
	public static function create() {
		/**
		 * Plugin instance variable
		 *
		 * @var null
		 */
		static $plugin = null;

		if ( $plugin === null ) {
			$plugin = new Plugin();
		}

		return $plugin;
	}
}
