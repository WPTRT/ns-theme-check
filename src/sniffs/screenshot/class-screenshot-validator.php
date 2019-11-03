<?php
/**
 * Screenshot Validator
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
 * @package Theme_Sniffer\Sniffs\Readme
 *
 * @since 1.2.0 Rewrote the screenshot validator class.
 */
class Screenshot_Validator extends Validate_File {
	/**
	 * Allowed filename.
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
	 * @var string
	 */
	private $theme_root;

	/**
	 * @var string
	 */
	private $theme_slug;

	/**
	 * @var string
	 */
	private $extension;

	/**
	 * The validation method
	 *
	 * @param WP_Theme $theme WP_Theme Theme object.
	 *
	 * @return array Sniff details
	 */
	public function validate( $theme ) : array {
		$this->theme_root;
		$this->theme_slug;
		$this->filename;
		$this->extension;

		$theme->get_screenshot();
		$theme->get_files( $this->extensions );

		if ( ! $this->file_exists( $file ) ) {

			$this->file = implode( '/', [ $this->theme_root, $this->theme_slug, $this->filename . '.' . $this->extensions[0] ] );

			$this->results[] = [
				'severity' => 'error',
				'message'  => sprintf(
					/* translators: 1: the file required including name and extension. */
					esc_html__( 'Themes are required to provide %1$s', 'theme-sniffer' ),
					$this->filename . '.' . $this->extensions[0]
				),
			];
		}

		$this->check_correct_extensions( $file );
		$this->check_mime_type( $file );
		$this->check_screenshot_dimensions( $file );
	}

	private function check_correct_extensions( string $file ) {

	}

	private function check_mime_type( string $file ) {

	}

	private function check_screenshot_dimensions( string $file ) {

	}



}
