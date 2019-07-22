<?php
/**
 * Result Class
 *
 * Returns a formatted result object to pass to reporter output.
 *
 * @since   1.1.0
 *
 * @package Theme_Sniffer\Sniffs
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Sniffs;

use Theme_Sniffer\Sniffs\Has_Results;

/**
 * Responsible for initiating validators.
 *
 * @package Theme_Sniffer\Sniffs\Readme
 *
 * @since   1.1.0
 */
class Result implements Has_Results {

	/**
	 * The errors key value.
	 *
	 * @var string
	 */
	const ERRORS = 'errors';

	/**
	 * The warnings key value.
	 *
	 * @var string
	 */
	const WARNINGS = 'warnings';

	/**
	 * The fixable key value.
	 *
	 * @var string
	 */
	const FIXABLE = 'fixable';

	/**
	 * The success key value.
	 *
	 * @var string
	 */
	const SUCCESS = 'success';

	/**
	 * The messages key value.
	 *
	 * @var string
	 */
	const MESSAGES = 'messages';

	/**
	 * The filePath key value.
	 *
	 * @var string
	 */
	const FILE_PATH = 'filePath';

	/**
	 * The message key value.
	 *
	 * @var string
	 */
	const MESSAGE = 'message';

	/**
	 * The severity key value.
	 *
	 * @var string
	 */
	const SEVERITY = 'severity';

	/**
	 * The error key value.
	 *
	 * @var string
	 */
	const ERROR = 'error';

	/**
	 * The warning key value.
	 *
	 * @var string
	 */
	const WARNING = 'warning';

	/**
	 * The type key value.
	 *
	 * @var string
	 */
	const TYPE = 'type';

	/**
	 * The message source key value.
	 *
	 * @var string
	 */
	const SOURCE = 'source';


	/**
	 * Sniff results for the readme.txt.
	 *
	 * @var array $results
	 *
	 * @since 1.1.0
	 */
	public $results = [];

	/**
	 * Instantiate class and set class properties.
	 *
	 * @since 1.1.0
	 *
	 * @param string $file File results are for.
	 * @param array  $results Results for sniffs done on $file.
	 */
	public function __construct( $file, $results ) {
		$this->file    = $file;
		$this->results = $this->set_format();
		$this->results = $this->set_results( $results );
	}

	/**
	 * Set results class property.
	 *
	 * @since 1.1.0
	 *
	 * @param string $results Results of sniffs.
	 *
	 * @return string $results Formatted results of sniffs.
	 */
	private function set_results( $results ) {
		if ( $results ) {

			// Loop results and assign.
			foreach ( $results as $result ) {
				if ( $result[ self::SEVERITY ] === self::ERROR ) {
					$this->results[ $this->file ][ self::ERRORS ]++;
				}

				if ( $result[ self::SEVERITY ] === self::WARNING ) {
					$this->results[ $this->file ][ self::WARNINGS ]++;
				}

				$this->results[ $this->file ][ self::MESSAGES ][] = [
					self::MESSAGE  => esc_html( $result[ self::MESSAGE ] ?? '' ),
					self::SOURCE   => $result[ self::SOURCE ] ?? null,
					self::SEVERITY => $result[ self::SEVERITY ] ?? null,
					self::FIXABLE  => $result[ self::FIXABLE ] ?? false,
					self::TYPE     => strtoupper( $result[ self::SEVERITY ] ?? '' ),
				];
			}
		}

		return $this->results;
	}

	/**
	 * Set format for results class property.
	 *
	 * @since 1.1.0
	 *
	 * @return array Formatted result.
	 */
	private function set_format() {
		return [
			$this->file => [
				self::ERRORS   => 0,
				self::WARNINGS => 0,
				self::MESSAGES => [],
			],
		];
	}

	/**
	 * Return results from all validator parts ran.
	 *
	 * @since 1.1.0
	 *
	 * @return array $results Validator warnings/messages.
	 */
	public function get_results() {
		$result = empty( $this->results[ $this->file ][ self::MESSAGES ] ) ? false : $this->results;
		return $this->results;
	}
}
