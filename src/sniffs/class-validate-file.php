<?php
/**
 * Validate file abstract class
 *
 * @since   1.2.0
 *
 * @package Theme_Sniffer\Sniffs
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Sniffs;

use RuntimeException;

/**
 * Responsible for initiating validators.
 *
 * @package Theme_Sniffer\Sniffs
 *
 * @since 1.2.0
 */
abstract class Validate_File implements Validator {

	/**
	 * Sniff results
	 *
	 * @var array
	 *
	 * @since 1.1.0
	 */
	public $results = [];

	/**
	 * Check if the file to check exists
	 *
 	 * @since 1.2.0
	 *
	 * @param string $file
	 *
	 * @return bool
	 */
	protected function file_exists( string $file ) : bool {
		try {
			$this->get_file_path($file);

			return true;
		} catch ( RuntimeException $e ) {
			return false;
		}
	}

	/**
	 * Performs file_exists checks case-insensitively.
	 *
	 * @since 1.1.0
	 * @since 1.2.0 Added to abstract class from the trait. Renamed to make it more in line with its functionality.
	 *
	 * @param string $file File to check if it exists.
	 *
	 * @return string Path to file.
	 * @throws RuntimeException
	 */
	protected function get_file_path( string $file ) : string {
		if ( file_exists( $file ) ) {
			return $file;
		}

		$lowerfile = strtolower( $file );

		foreach( glob( dirname( $file ) . '/*' ) as $new_file ) {
			if ( strtolower( $new_file ) === $lowerfile ) {
				return $new_file;
			}
		}

		throw new RuntimeException( sprintf( esc_html__( 'File %s does not exist', 'theme-sniffer' ), $file ) );
	}
}
