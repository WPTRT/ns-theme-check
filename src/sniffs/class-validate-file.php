<?php
/**
 * Readme Validator
 *
 * @since   1.1.0
 *
 * @package Theme_Sniffer\Sniffs\Readme
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Sniffs;

use Theme_Sniffer\Helpers\File_Helpers;

/**
 * Responsible for initiating validators.
 *
 * @package Theme_Sniffer\Sniffs\Readme
 *
 * @since   1.1.0
 */
abstract class Validate_File implements Has_Results {

	use File_Helpers;

	/**
	 * Case-insensitive filename.
	 *
	 * @var string $filename
	 *
	 * @since 1.1.0
	 */
	public $filename = '';

	/**
	 * Extensions allowed.
	 *
	 * @var array $extension
	 *
	 * @since 1.1.0
	 */
	public $extension = [];

	/**
	 * Sniff results for the readme.txt.
	 *
	 * @var array $results
	 *
	 * @since 1.1.0
	 */
	public $results = [];

	/**
	 * The file to reference.
	 *
	 * @var array $file
	 *
	 * @since 1.1.0
	 */
	public $file = '';

	/**
	 * Instantiate class and set class properties.
	 *
	 * @since 1.1.0
	 *
	 * @param string $slug Theme's slug.
	 */
	public function __construct( $slug ) {
		$this->theme_slug = $slug;
		$this->theme_root = get_theme_root( $this->theme_slug );
		$this->file       = $this->set_file();
		$this->validate( $this->file );
	}

	/**
	 * Set file class property.
	 *
	 * @since 1.1.0
	 *
	 * @return string $file Found file.
	 */
	public function set_file() {
		$file = false;
		foreach ( $this->extensions as $extenstion ) {
			$file = implode( '/', [ $this->theme_root, $this->theme_slug, $this->filename . '.' . $extenstion ] );
			$file = $this->file_exists( $file );
			if ( $file !== false ) {
				break;
			}
		}

		return $file;
	}

	/**
	 * Runs validation.
	 *
	 * @since 1.1.0
	 *
	 * @param string $file File to validate.
	 */
	public function validate( $file ) {

		// No file.
		if ( $file === false ) {

			// Set file class property for result output.
			$this->file = implode( '/', [ $this->theme_root, $this->theme_slug, $this->filename . '.' . $this->extensions[0] ] );

			$this->results[] = [
				'severity' => 'error',
				'message'  => sprintf(
					/* translators: 1: the file required including name and extension. */
					esc_html__( 'Themes are required to provide %1$s', 'theme-sniffer' ),
					$this->filename . '.' . $this->extensions[0]
				),
			];

			return;
		}

		// Valid file but not recommended extension.
		$pathinfo = pathinfo( $file );

		if ( $pathinfo['extension'] !== $this->extensions[0] ) {
			$this->results[] = [
				'severity' => 'warning',
				'message'  => sprintf(
					/* translators: 1: filename being validated 2: file extension found 3: recommended file extension to use */
					esc_html__( '%1$s.%2$s is valid, but %1$s.%3$s is recommended.', 'theme-sniffer' ),
					$this->filename,
					$pathinfo['extension'],
					$this->extensions[0]
				),
			];

			return;
		}
	}

	/**
	 * Return results from all validator parts ran.
	 *
	 * @since 1.1.0
	 *
	 * @return array $results Validator errors/warnings/messages.
	 */
	public function get_results() {
		$result = new Result( $this->file, $this->results );
		return $result->get_results();
	}
}
