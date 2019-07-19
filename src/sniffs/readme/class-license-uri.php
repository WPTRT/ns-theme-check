<?php
/**
 * Validator for License URI in readme.txt.
 *
 * @package Theme_Sniffer\Sniffs\Readme
 *
 * @since 1.1.0
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Sniffs\Readme;

use Theme_Sniffer\Sniffs\Validate;
use Theme_Sniffer\Helpers\Readme_Helpers;

/**
 * Validator for License URI's.
 *
 * @package Theme_Sniffer\Sniffs\Readme
 *
 * @since 1.1.0
 */
class License_Uri extends Validate {

	use Readme_Helpers {
		Readme_Helpers::init as private license_uri_helpers;
	}

	/**
	 * Initialize license helper data.
	 *
	 * @since 1.1.0
	 */
	public function init() {
		$this->license_uri_helpers();
	}

	/**
	 * Check License URI from readme.txt
	 *
	 * @since 1.1.0
	 */
	public function check() {
		$license = $this->find_license( $this->args->primary );

		// Still report errors when license status is warning (or success of course).
		if ( $license->status !== 'error' ) {
			$uris = $this->license_data[ $license->id ]['uris'];

			// Missing License URI field error.
			if ( empty( $this->args->uri ) ) {
				$this->results[] = [
					'severity' => 'error',
					'message'  => esc_html__( 'All themes are required to provide a License URI in their readme.txt!', 'theme-sniffer' ),
				];
			}

			// URI field is invalid.
			if ( empty( preg_grep( '/^' . preg_quote( $this->args->uri, '/' ) . '$/i', $uris ) ) ) {
				$this->results[] = [
					'severity' => 'error',
					'message'  => wp_kses(
						sprintf(
							/* translators: 1: the user provided License URI in readme.txt 2: the license comparing against in readme.txt 3: a list of suitable license URIs that could be used */
							__( 'The License URI provided: %1$s, is not a known URI reference for the license %2$s.  These are recognized URIs for the license provided:<br/>%3$s', 'theme-sniffer' ),
							$this->args->uri,
							$this->args->primary,
							implode( '<br/>', $uris )
						),
						[
							'br' => [],
						]
					),
				];
			}
		} else {
			// Unable to determine License URI without valid License.
			$this->results[] = [
				'severity' => 'error',
				'message'  => esc_html__( 'Unable to determine License URI with an invalid License supplied in readme.txt!', 'theme-sniffer' ),
			];
		}
	}
}
