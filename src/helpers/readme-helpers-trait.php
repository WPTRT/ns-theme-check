<?php
/**
 * The readme.txt helpers trait file.
 *
 * @since   1.1.0
 *
 * @package Theme_Sniffer\Helpers
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Helpers;

/**
 * Sniffer helpers trait
 *
 * This trait contains some helper methods.
 *
 * @since 1.1.0
 */
trait Readme_Helpers {

	/**
	 * License data from imported from json.
	 *
	 * @var array $license_data
	 *
	 * @since 1.1.0
	 */
	protected $license_data;

	/**
	 * Initialize class and set class properties.
	 *
	 * @since 1.1.0
	 */
	public function init() {
		$this->license_data = $this->set_license_data();
	}

	/**
	 * Gets license validation data.
	 *
	 * @since 1.1.0
	 *
	 * @return array License validation data.
	 */
	public function set_license_data() : array {
		$licenses_path = WP_PLUGIN_DIR . plugin_dir_path( '/theme-sniffer/assets/build/licenses.json' );
		$licenses_file = file_get_contents( $licenses_path . 'licenses.json' );

		return json_decode( $licenses_file, true );
	}

	/**
	 * Find license matches.
	 *
	 * @since 1.1.0
	 *
	 * @param string $id License identifier.
	 *
	 * @return object $reponse Object containing license criteria and match information.
	 */
	public function find_license( $id ) {
		$response             = (object) array();
		$response->provided   = $id;
		$response->deprecated = array();
		$response->live       = array();

		// SPDX ID exact match, so skip loop.
		$found = $this->license_data[ $response->provided ] ?? array();

		// Check if license is deprecated.  Set data.
		if ( $found ) {
			$response->id = $found['licenseId'];
			if ( $found['isDeprecatedLicenseId'] ) {
				$response->deprecated[ $response->provided ] = $found;
			} else {
				$response->live[ $response->provided ] = $found;
			}
		} else {
			foreach ( $this->license_data as $license => $details ) {
				if ( in_array( $response->provided, $details['names'], true ) || in_array( $response->provided, $details['ids'], true ) ) {
					if ( $this->license_data[ $license ]['isDeprecatedLicenseId'] ) {
						$response->deprecated[ $license ] = $details;
					} else {
						$response->live[ $license ] = $details;
					}
				}
			}
		}

		return $this->get_response_message( $response );
	}

	/**
	 * Get license match messages.
	 *
	 * @since 1.1.0
	 *
	 * @param Object $response Object containing license criteria and match information.
	 *
	 * @return Object $response Object containing license criteria and match information.
	 */
	public function get_response_message( $response ) {

		// Non-deprecated license matches found - check here.  Names can match deprecated licenses as well.
		if ( ! empty( $response->live ) ) {

			// Single live license match.
			if ( count( $response->live ) === 1 ) {
				$details = current( $response->live );

				// Set the ID for easier lookup in other checks.
				$response->id = $details['licenseId'];

				// Provided a SPDX name that matched.
				if ( $response->provided === $details['name'] ) {
					$response->status = 'warning';
					/* translators: 1: a SPDX license name. 2: the recommended SPDX ID to use instead. */
					$response->message = sprintf( esc_html__( 'Found a valid SPDX name, %1$s, but it is better to use the SPDX ID: %2$s', 'theme-sniffer' ), $response->provided, $details['licenseId'] );
				} elseif ( $response->provided === $details['licenseId'] ) { // A Valid SPDX ID was found, no message required.
					$response->status  = 'success';
					$response->message = null;
				} else { // A single match was found for FSF criteria.
					$response->status = 'warning';
					/* translators: 1: a SPDX license name. 2: the recommended SPDX ID to use instead. */
					$response->message = sprintf( esc_html__( 'Found valid license information based on FSF naming: %1$s, but it is better to use the SPDX ID: %2$s', 'theme-sniffer' ), $response->provided, $details['licenseId'] );
				}
			} else { // Multiple matches returned, so it's FSF provided criteria.
				$matches          = array_keys( $response->live );
				$response->status = 'warning';
				/* translators: %s: listing of license IDs matched. */
				$response->message = sprintf( esc_html__( 'Found multiple records matching these licenses: %s, it\'s required to use a single SPDX Idenitfier!', 'theme-sniffer' ), implode( ', ', $matches ) );
			}
		} elseif ( ! empty( $response->deprecated ) ) { // Deprecated match found.
			$response->status = 'warning';
			/* translators: %s: User provided license identifier. */
			$response->message = sprintf( esc_html__( 'The license identification provided, $s, indicates a deprecated license!  Please use a valid SPDX Identifier!', 'theme-sniffer' ), $response->provided );
		} else { // No matches found.
			$response->status = 'warning';
			/* translators: %s: unrecognized user provided license identifier */
			$response->message = sprintf( esc_html__( 'No matching license criteria could be determined from: %s!', 'theme-sniffer' ), $response->provided );
		}

		return $response;
	}

	/**
	 * Check if a license criteria object is gpl compatible.
	 *
	 * @since 1.1.0
	 *
	 * @param Object $license A license criteria object.
	 *
	 * @return bool License criteria is GPLv2 compatible.
	 */
	public function is_gpl2_or_later_compatible( $license ) {
		$gpl = false;

		if ( ! empty( $license->id ) ) {

			// Check if license is flagged as GPLv2.0 Compatible.
			$gpl = $this->license_data[ $license->id ]['isGpl2Compatible'] || $this->license_data[ $license->id ]['isGpl3Compatible'];
		}

		return $gpl;
	}

	/**
	 * Get blacklisted resources.
	 *
	 * @since 1.1.0
	 *
	 * @return array Blacklisted resource urls.
	 */
	public function get_blacklist() : array {
		return array(
			'unsplash',
			'sxc.hu',
			'photopin.com',
			'publicdomainpictures.net',
			'splitshire.com',
			'pixabay.com',
		);
	}
}
