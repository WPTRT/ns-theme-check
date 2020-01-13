<?php
/**
 * File that holds Has_Activation interface
 *
 * @since 1.0.0
 * @package Theme_Sniffer\Core
 */

declare( strict_types = 1 );

namespace Theme_Sniffer\Core;

/**
 * Interface Has_Activation.
 *
 * An object that can be activated.
 *
 * @since 1.0.0
 */
interface Has_Activation {
	/**
	 * Activate the service.
	 *
	 * Used when adding certain capabilities of a service.
	 *
	 * Example: add_role, add_cap, etc.
	 *
	 * @return void
	 */
	public function activate();
}
