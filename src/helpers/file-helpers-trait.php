<?php
/**
 * File System Helpers
 *
 * @since   1.1.0
 *
 * @package Theme_Sniffer\Helpers
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Helpers;

/**
 * File helpers trait
 *
 * This trait contains some helper methods for filesystem.
 *
 * @since 1.1.0
 */
trait File_Helpers {

	/**
	 * Performs file_exists checks case-insensitively.
	 *
	 * @since 1.1.0
	 *
	 * @param  string $file File to check if it exists.
	 *
	 * @return string|false Path to file or false if not found.
	 */
	public function file_exists( $file ) {
		if ( file_exists( $file ) ) {
			return $file;
		}

		$lowerfile = strtolower( $file );

		foreach ( glob( dirname( $file ) . '/*' ) as $file ) {
			if ( strtolower( $file ) === $lowerfile ) {
				return $file;
			}
		}

		return false;
	}
}
