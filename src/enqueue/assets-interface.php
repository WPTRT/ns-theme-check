<?php
/**
 * File that holds the assets interface
 *
 * Used for assets enqueueing
 *
 * @since 1.2.0
 * @package Theme_Sniffer\Enqueue
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Enqueue;

use Theme_Sniffer\Core\Registerable;

/**
 * Assets interface.
 *
 * Interface used for enqueueing assets
 *
 * @since 1.2.0
 */
interface Assets extends Registerable {

	/**
	 * Enqueue styles
	 *
	 * @since 1.2.0
	 *
	 * @param string $hook Hook suffix for the current admin page.
	 */
	public function enqueue_styles( $hook );

	/**
	 * Enqueue scripts
	 *
	 * @since 1.2.0
	 * @param string $hook Hook suffix for the current admin page.
	 */
	public function enqueue_scripts( $hook );
}
