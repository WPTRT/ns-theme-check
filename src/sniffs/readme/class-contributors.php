<?php
/**
 * Validator for contributors list.
 *
 * @package Theme_Sniffer\Sniffs\Readme
 *
 * @since 1.1.0
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Sniffs\Readme;

use Theme_Sniffer\Sniffs\Validate;

/**
 * Validator for contributors list.
 *
 * @package Theme_Sniffer\Sniffs\Readme
 *
 * @since 1.1.0
 */
class Contributors extends Validate {

	/**
	 * Check Contributors field from readme.txt
	 *
	 * @since 1.1.0
	 */
	public function check() {

		// Skip sniff if no contributors parsed.
		if ( ! $this->args ) {
			return;
		}

		// Check each user's profile in list.
		foreach ( $this->args as $contributor ) {
			$profile  = "https://profiles.wordpress.org/{$contributor}/";
			$response = wp_remote_head( $profile, [ 'timeout' => 20 ] );

			// Error with remote request.
			if ( is_wp_error( $response ) ) {
				$this->results[] = [
					'severity' => 'warning',
					'message'  => esc_html__( 'Something went wrong when remotely reaching out to WordPress.org to valid the contributors in readme.txt' ),
				];

				continue;
			}

			$status = wp_remote_retrieve_response_code( $response );

			// Successful validatation.
			if ( $status === 200 ) {
				continue;
			}

			// Profile page redirect.
			if ( $status === 302 ) {
				$this->results[] = [
					'severity' => 'error',
					/* translators: %s: a contributor's username for WordPress.org that wasn't found. */
					'message'  => sprintf( esc_html__( 'The user %s, is not a valid WordPress.org username!', 'theme-sniffer' ), $contributor ),
				];

				continue;
			}

			// Catch all error if something beyond this..
			$this->results[] = [
				'severity' => 'warning',
				'message'  => esc_html__( 'Something went wrong when validating readme.txt\'s contributors list!', 'theme-sniffer' ),
			];
		}
	}
}
