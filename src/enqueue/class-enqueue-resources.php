<?php
/**
 * Enqueue resources class file
 *
 * @since 1.2.0
 * @package Theme_Sniffer\Enqueue
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Enqueue;

use Developer_Portal\Custom_Post_Type\Documentation;

/**
 * Enqueue resources class
 *
 * Class that will handle enqueueing and registering scripts and styles
 */
class Enqueue_Resources implements Assets {

	const JS_HANDLE = 'theme-sniffer-js';
	const JS_URI    = 'themeSniffer.js';

	const CSS_HANDLE = 'theme-sniffer-css';
	const CSS_URI    = 'themeSniffer.css';

	const VERSION   = false;
	const IN_FOOTER = false;

	const MEDIA_ALL    = 'all';
	const MEDIA_PRINT  = 'print';
	const MEDIA_SCREEN = 'screen';

	const LOCALIZATION_HANDLE = 'themeSnifferLocalization';

	/**
	 * Register the enqueue actions
	 */
	public function register() {
		add_action(
			'admin_enqueue_scripts',
			array( $this, 'enqueue_styles' )
		);

		add_action(
			'admin_enqueue_scripts',
			array( $this, 'enqueue_scripts' )
		);
	}

	/**
	 * Register admin area styles
	 *
	 * @since 1.2.0
	 *
	 * @param string $hook Hook suffix for the current admin page.
	 */
	public function enqueue_styles( $hook ) {
		if ( $hook !== 'toplevel_page_theme-sniffer' ) {
			return;
		}

		wp_register_style(
			self::CSS_HANDLE,
			$this->get_manifest_assets_data( self::CSS_URI ),
			array(),
			self::VERSION,
			self::MEDIA_ALL
		);

		wp_enqueue_style( self::CSS_HANDLE );
	}

	/**
	 * Register admin area scripts
	 *
	 * @since 1.2.0
	 *
	 * @param string $hook Hook suffix for the current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		if ( $hook !== 'toplevel_page_theme-sniffer' ) {
			return;
		}

		wp_register_script(
			self::JS_HANDLE,
			$this->get_manifest_assets_data( self::JS_URI ),
			$this->get_js_dependencies(),
			self::VERSION,
			self::IN_FOOTER
		);

		wp_enqueue_script( self::JS_HANDLE );

		foreach ( $this->get_localizations() as $localization_name => $localization_data ) {
			wp_localize_script( self::JS_HANDLE, $localization_name, $localization_data );
		}
	}

	/**
	 * Get script dependencies
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_enqueue_script/#default-scripts-included-and-registered-by-wordpress
	 *
	 * @return array List of all the script dependencies
	 */
	protected function get_js_dependencies() : array {
		return array(
			'jquery',
			'esprima',
		);
	}

	/**
	 * Get script localizations
	 *
	 * @return array Key value pair of different localizations
	 */
	protected function get_localizations() : array {
		return array(
			self::LOCALIZATION_HANDLE => array(
				'sniffError'      => esc_html__( 'The check has failed. This could happen due to running out of memory. Either reduce the file length or increase PHP memory.', 'theme-sniffer' ),
				'checkCompleted'  => esc_html__( 'Check is completed. The results are below.', 'theme-sniffer' ),
				'checkInProgress' => esc_html__( 'Check in progress', 'theme-sniffer' ),
				'errorReport'     => esc_html__( 'Error', 'theme-sniffer' ),
				'ajaxAborted'     => esc_html__( 'Checking stopped', 'theme-sniffer' ),
				'copySuccess'     => esc_attr__( 'Copied!', 'theme-sniffer' ),
			),
		);
	}

	/**
	 * Return full path for specific asset from manifest.json
	 * This is used for cache busting assets.
	 *
	 * @param string $key File name key you want to get from manifest.
	 * @return string     Full path to asset.
	 */
	private function get_manifest_assets_data( string $key = null ) : string {
		$data = ASSETS_MANIFEST;

		if ( ! $key || $data === null ) {
			return '';
		}

		$data = json_decode( $data, true );

		if ( empty( $data ) ) {
			return '';
		}

		$asset = $data[ $key ] ?? '';

		return plugin_dir_url( dirname( __DIR__ ) ) . '/assets/build/' . $asset;
	}
}
