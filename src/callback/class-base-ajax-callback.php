<?php
/**
 * File that holds base abstract class for ajax callbacks
 *
 * @since 1.0.0
 * @package Theme_Sniffer\Callback
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Callback;

use Theme_Sniffer\Core\Service;


/**
 * Abstract class Base_Ajax_Callback.
 *
 * @since 1.0.0
 */
abstract class Base_Ajax_Callback implements Invokable, Service {

	/**
	 * Register ajax callback
	 */
	public function register() {

		$callback_name = $this->get_action_name();

		add_action( "wp_ajax_{$callback_name}", [ $this, 'callback' ] );

		// Add nopriv action.
		if ( $this->is_public() ) {
			add_action( "wp_ajax_nopriv_{$callback_name}", [ $this, 'callback' ] );
		}
	}

	/**
	 * Callback method for the specific class.
	 */
	abstract public function callback();

	/**
	 * Returns true if the callback should be public
	 *
	 * @return boolean true if callback is public.
	 */
	abstract protected function is_public() : bool;

	/**
	 * Get name of the callback action
	 *
	 * @return string Name of the callback action.
	 */
	abstract protected function get_action_name() : string;
}
