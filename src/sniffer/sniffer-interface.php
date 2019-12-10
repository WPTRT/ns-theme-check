<?php
/**
 * Sniffer interface file
 *
 * @since 1.2.0
 *
 * @package Theme_Sniffer\Sniffer
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Sniffer;

/**
 * Interface for implementing the sniffer instance
 */
interface Sniffer {

	/**
	 * Init sniffer runner
	 *
	 * @return void
	 */
	public function init_runner();

	/**
	 * Init sniffer config
	 *
	 * @param mixed $config Configuration array.
	 * @return mixed
	 */
	public function init_config( $config );

	/**
	 * Init sniffer reporter
	 *
	 * @param mixed $config Configuration array.
	 * @return mixed
	 */
	public function init_reporter( $config );
}
