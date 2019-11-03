<?php
/**
 * Validator interface file
 *
 * @since 1.2.0
 *
 * @package Theme_Sniffer\Sniffs
 */

namespace Theme_Sniffer\Sniffs;

/**
 * Interface of object that can be validated.
 */
interface Validator {

	/**
	 * Validate a file.
	 *
	 * @param object $theme WP_Theme Theme object.
	 *
	 * @return array
	 */
	public function validate( $theme ) : array;
}
