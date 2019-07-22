<?php
/**
 * Validatable interface file
 *
 * @since 1.1.0
 *
 * @package Theme_Sniffer\Sniffs
 */

namespace Theme_Sniffer\Sniffs;

/**
 * Interface of object that can be validated.
 */
interface Validatable {

	/**
	 * Check to perform validation.
	 *
	 * @return void
	 */
	public function check();
}
