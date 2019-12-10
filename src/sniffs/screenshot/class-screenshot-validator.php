<?php
/**
 * Screenshot validator
 *
 * @since   1.2.0
 *
 * @package Theme_Sniffer\Sniffs\Screenshot
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Sniffs\Screenshot;

use Theme_Sniffer\Sniffs\Validate_File;

use WP_Theme;

/**
 * A screenshot validator class
 *
 * @package Theme_Sniffer\Sniffs\Screenshot
 *
 * @since 1.2.0 Rewrote the screenshot validator class.
 */
class Screenshot_Validator extends Validate_File {

	/**
	 * The validation method
	 *
	 * @param WP_Theme $theme WP_Theme Theme object.
	 *
	 * @return array Sniff details
	 */
	public function validate( $theme ) : array {
		$screenshot = $theme->get_screenshot();

		if ( ! $this->file_exists( $screenshot ) ) {
			$this->results[] = [
				'severity' => 'error',
				'message'  => esc_html__( 'Themes are required to provide a screenshot named screenshot.png or screenshot.jpg', 'theme-sniffer' ),
			];

			// If there is no file, there is no need to check the rest (mime type etc.).
			return $this->results;
		}

		$this->check_mime_type( $screenshot );
		$this->check_screenshot_dimensions( $screenshot );
	}

	/**
	 * Check the mime type of the screenshot file
	 *
	 * @param string $file Screenshot file to check mime type of.
	 */
	private function check_mime_type( string $file ) {
		// Image mime.
		$mime_type = wp_get_image_mime( $file );

		// Missing mime type.
		if ( ! $mime_type ) {
			$this->results[] = [
				'severity' => 'error',
				'message'  => esc_html__( 'Screenshot mime type could not be determined, screenshots must have a mime type of "image/png" or "image/jpg".', 'theme-sniffer' ),
			];

			return;
		}

		// Valid mime type returned, but not a png.
		if ( $mime_type !== 'image/png' ) {
			$this->results[] = [
				'severity' => 'warning',
				'message'  => sprintf(
					/* translators: 1: screenshot mime type found */
					esc_html__( 'Screenshot has mime type of "%1$s", but a mimetype of "image/png" is recommended.', 'theme-sniffer' ),
					$mime_type
				),
			];

			return;
		}
	}

	/**
	 * Check the dimensions and aspect ratio of the screenshot
	 *
	 * Props to Otto42(WP.org, Github) for aspect ratio logic from Theme Check.
	 *
	 * @link https://github.com/WordPress/theme-check/blob/master/checks/screenshot.php
	 *
	 * @param string $file Screenshot file to check the dimensions of.
	 */
	private function check_screenshot_dimensions( string $file ) {
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
		if ( $height / $width !== 0.75 ) {
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
