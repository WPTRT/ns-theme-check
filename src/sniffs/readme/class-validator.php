<?php
/**
 * Readme Validator
 *
 * @since   1.1.0
 *
 * @package Theme_Sniffer\Sniffs\Readme
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Sniffs\Readme;

use Theme_Sniffer\Sniffs\Validate_File;

/**
 * Responsible for initiating validators.
 *
 * @package Theme_Sniffer\Sniffs\Readme
 *
 * @since   1.1.0
 */
class Validator extends Validate_File {

	/**
	 * Filename allowed.
	 *
	 * @var string $filename
	 *
	 * @since 1.1.0
	 */
	public $filename = 'readme';

	/**
	 * Extensions allowed.
	 *
	 * @var array $extensions
	 *
	 * @since 1.1.0
	 */
	public $extensions = [ 'txt', 'md' ];

	/**
	 * Sniff results for the readme.txt.
	 *
	 * @var array $results
	 *
	 * @since 1.1.0
	 */
	public $results = [];

	/**
	 * Set defaults that are necessary for any validators if needed.
	 *
	 * @since 1.1.0
	 *
	 * @param Object $parser Populated parser object.
	 */
	public function set_defaults( $parser ) {
		if ( ! empty( $parser->license ) && ! empty( $parser->license_uri ) ) {
			$parser->license_uri = (object) [
				'primary' => $parser->license,
				'uri'     => $parser->license_uri,
			];
		}

		return $parser;
	}

	/**
	 * Runs any existing validators set on parser.
	 *
	 * @since 1.1.0
	 *
	 * @param string $file file to validate.
	 */
	public function validate( $file ) {

		// Validate file.
		parent::validate( $file );

		// No need to continue if file validation contains error for file not existing.
		if ( ! empty( $this->results ) && in_array( 'error', array_column( $this->results, 'severity' ), true ) ) {
			return;
		}

		$parser       = new Parser( $this->file );
		$this->parser = $this->set_defaults( $parser );

		foreach ( $this->parser as $name => $args ) {
			$class = __NAMESPACE__ . '\\' . ucwords( $name, '_' );

			if ( class_exists( $class ) ) {
				$validator = new $class( $args );
				$results   = $validator->get_results();

				if ( is_array( $results ) ) {
					$this->results = array_merge( $this->results, $results );
				}
			}
		}
	}
}
