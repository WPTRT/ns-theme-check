<?php
/**
 * Readme Validator
 *
 * @since   1.1.0
 *
 * @package Theme_Sniffer\Sniffs\Readme
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Sniffs\Screenshot;

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
	public $filename = 'screenshot';

	/**
	 * Extensions allowed.
	 *
	 * @var array $extensions
	 *
	 * @since 1.1.0
	 */
	public $extensions = [ 'png', 'jpg' ];

	/**
	 * Runs screenshot validators
	 *
	 * @since 1.1.0
	 *
	 * @param string $file File to validate.
	 */
	public function validate( $file ) {

		// Validate file.
		parent::validate( $file );

		// No need to continue if file validation contains error for file not existing.
		if ( ! empty( $this->results ) && in_array( 'error', array_column( $this->results, 'severity' ), true ) ) {
			return;
		}

		// Image mime.
		$mime_type = wp_get_image_mime( $file );

		// Missing mime type.
		if ( ! $mime_type ) {
			$this->results[] = [
				'severity' => 'error',
				'message'  => sprintf(
					esc_html__( 'Screenshot mime type could not be determined, screenshots must have a mime type of "img/png" or "img/jpg".', 'theme-sniffer' ),
					$mime_type
				),
			];

			return;
		}

		// Valid mime type returned, but not a png.
		if ( $mime_type !== 'image/png' ) {
			$this->results[] = [
				'severity' => 'warning',
				'message'  => sprintf(
					/* translators: 1: screenshot mime type found */
					esc_html__( 'Screenshot has mime type of "%1$s", but a mimetype of "img/png" is recommended.', 'theme-sniffer' ),
					$mime_type
				),
			];

			return;
		}

		// Screenshot validated at this point, so check dimensions - no need for fileinfo.
		// props @Otto42(WP.org, Github) for aspect ratio logic from Theme Check: https://github.com/WordPress/theme-check/blob/master/checks/screenshot.php.
		list( $width, $height ) = getimagesize( $file );

		// Screenshot too big.
		if ( $width > 1200 || $height > 900 ) {
			$this->results[] = [
				'severity' => 'error',
				'message'  => sprintf(
					/* translators: 1: screenshot width 2: screenshot height */
					esc_html__( 'The size of your screenshot should not exceed 1200x900, but screenshot.png is currently %1$dx%2$d.', 'theme-sniffer' ),
					$width,
					$height
				),
			];

			return;
		}

		// Aspect Ratio.
		if ( $width / $height !== 0.75 ) {
			$this->results[] = [
				'severity' => 'error',
				'message'  => esc_html__( 'Screenshot aspect ratio must be 4:3!', 'theme-sniffer' ),
			];

			return;
		}

		// Recommended size.
		if ( $width !== 1200 || $height !== 900 ) {
			$this->results[] = [
				'severity' => 'warning',
				'message'  => esc_html__( 'Screenshot size of 1200x900 is recommended to account for HiDPI displays.', 'theme-sniffer' ),
			];

			return;
		}
	}
}
