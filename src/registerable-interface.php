<?php
/**
 * Registrable interface file
 *
 * @since 1.0.0
 * @package Theme_Sniffer\Core
 */

namespace Theme_Sniffer\Core;

/**
 * Interface of object that can be registered.
 */
interface Registerable {
	/**
	 * Register the registrable element with the system (hooks for instance).
	 *
	 * @return void
	 */
	public function register();
}
