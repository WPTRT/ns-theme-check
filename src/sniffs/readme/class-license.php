<?php
/**
 * Validator for licenses strings provided.
 *
 * @package Theme_Sniffer\Sniffs\Readme\Validate
 *
 * @since 1.1.0
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Sniffs\Readme;

use Theme_Sniffer\Sniffs\Validate;
use Theme_Sniffer\Helpers\Readme_Helpers;

/**
 * Validator for License String In Readme.
 *
 * @package Theme_Sniffer\Sniffs\Readme\Validate
 *
 * @since 1.1.0
 */
class License extends Validate {

	use Readme_Helpers {
		Readme_Helpers::init as private license_helpers;
	}

	/**
	 * Initialize license helper data.
	 *
	 * @since 1.1.0
	 */
	public function init() {
		$this->license_helpers();
	}

	/**
	 * Check license from readme.txt
	 *
	 * @since 1.1.0
	 */
	public function check() {
		$license_data = $this->find_license( $this->args );

		// Only report errors.
		if ( $license_data->status !== 'success' ) {
			$this->results[] = array(
				'severity' => $license_data->status,
				'message'  => $license_data->message,
			);
		}

		// Check if GPLv2 compatible if no errors found with License Identifier so far.
		if ( $license_data->status !== 'warning' && ! $this->is_gpl2_or_later_compatible( $license_data ) ) {
			$this->results[] = array(
				'severity' => 'warning',
				'message'  => sprintf(
					/* translators: %s: the license specified in readme.txt */
					esc_html__( 'The license specified, %s is not compatible with WordPress\' license of GPL-2.0-or-later.  All themes must meet this requirement!' ),
					$this->args
				),
			);
		}
	}
}
