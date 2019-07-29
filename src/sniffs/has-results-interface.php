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
interface Has_Results {

	/**
	 * Retrieves results from validation.
	 *
	 * @return void
	 */
	public function get_results();
}
