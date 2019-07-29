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
abstract class Validate implements Validatable, Has_Results {

	/**
	 * Sniff results.
	 *
	 * @var array $results
	 *
	 * @since 1.1.0
	 */
	protected $results = [];

	/**
	 * Initialize class and set class properties.
	 *
	 * @since 1.1.0
	 *
	 * @param Mixed $args Arguments used for validation.
	 */
	public function __construct( $args ) {
		$this->args = $args;
		$this->init();
		$this->check();
	}

	/**
	 * Additional initialization logic before check.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function init() {}

	/**
	 * Classes should check to perform validation in check().
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	abstract public function check();

	/**
	 * Retrieve the results from the validate check method.
	 *
	 * @since 1.1.0
	 *
	 * @return array $results Results from validation check.
	 */
	public function get_results() {
		return $this->results;
	}
}
