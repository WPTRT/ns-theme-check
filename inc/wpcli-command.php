<?php
/**
 * WP-CLI Theme Check Command
 *
 * @package NS_Theme_Check
 */

if ( class_exists( 'WP_CLI' ) ) {

	/**
	 * NS Theme Check Report
	 *
	 * [<theme-slug>]
	 * : Manually pass a theme slug to test, defaults to currently active theme.
	 * ---
	 * [--show_warnings=<show_warnings>]
	 * : Include warnings in the report or only errors, defaults to report all.
	 * ---
	 * default: 1
	 * options:
	 *   - 0
	 *   - 1
	 * ---
	 *
	 * [--minimum_php_version=<minimum_php_version>]
	 * : Minimum PHP version to test against, defaults to minimum WP compat.
	 * ---
	 * default: '5.2'
	 * options:
	 *   - '5.2'
	 *   - '5.3'
	 *   - '5.4'
	 *   - '5.5'
	 *   - '5.6'
	 *   - '7.0'
	 * ---
	 *
	 * [--standard=<standard>]
	 * : Choose a different standard to the default custom theme sniffs.
	 * ---
	 * default: WordPress-Theme
	 * options:
	 *   - WordPress-Theme
	 *   - WordPress-Core
	 *   - WordPress-Extra
	 *   - WordPress-Docs
	 *   - WordPress-VIP
	 * ---
	 *
	 * @when after_theme_setup
	 *
	 * @param  array $slug       Array containing non-named args from command line. Expect a theme slug.
	 * @param  array $assoc_args Array containing the named args from command line as default option overrides.
	 */
	function ns_theme_check_run_wpcli_report( $slug, $assoc_args ) {

		WP_CLI::log( 'Starting NS Theme Check...' );

		// Test for some files only present in the release version of the plugin.
		if ( ! file_exists( NS_THEME_CHECK_DIR . '/vendor/autoload.php' ) ) {
			// translators: 1 - link to proper theme instalation info.
			$message = sprintf( esc_html__( 'It seems you are using GitHub provided zip for the plugin. Visit %1$sInstalling%2$s to find the correct bundled plugin zip.', 'ns-theme-check' ), '<a href="https://github.com/ernilambar/ns-theme-check#installing" target="_blank">', '</a>' );
			$error = new WP_Error( '-1', $message );
			// Log an error message and exit(1).
			WP_CLI::error( $error, true );
		}

		// swap the contents of 'standard' into an array containing the
		// original values for compatibility with expected args array.
		$assoc_args['standard'] = array(
			$assoc_args['standard'],
		);

		if ( strlen( $slug[0] ) <= 1 ) {
			// A theme slug wasn't passed, get currently active theme slug.
			$theme_slug = get_option( 'stylesheet' );
		} else {
			$theme_slug = $slug[0];
		}

		$theme_slug = esc_html( $theme_slug );

		$defaults = array(
			'show_warnings'       => true,
			'raw_output'          => 1,
			'minimum_php_version' => '5.2',
			'standard'            => array(),
			'text_domains'        => array( $theme_slug ),
		);

		$args = wp_parse_args( $assoc_args, $defaults );

		// Bail if we don't have a theme slug or theme doesn't exist for the
		// slug we have.
		if ( empty( $theme_slug ) || ! file_exists( get_theme_root( $theme_slug ) . '/' . $theme_slug . '/style.css' ) ) {
			WP_CLI::error( 'No theme slug in use or no theme exists for that slug.', true );
		}

		$theme = wp_get_theme( $theme_slug );
		$php_files = $theme->get_files( 'php', 4, false );

		// Frameworks.
		foreach ( $php_files as $key => $file ) {
			if ( strrpos( $key, 'hybrid.php' ) ) {
				$pass_args['text_domains'][] = 'hybrid-core';
			}
			if ( strrpos( $key, 'kirki.php' ) ) {
				$pass_args['text_domains'][] = 'kirki';
			}
		}

		$all_files = $theme->get_files( array( 'php', 'css,', 'js' ), -1, false );

		// run the sniffs and store the results.
		WP_CLI::log( 'Running sniffs...' );
		$output = ns_theme_check_do_sniff( $theme_slug, $args, $all_files );

		// decode some entities for better display in terminal.
		$output = wp_kses_decode_entities( (string) $output );

		// clear out the beginning `<pre>` and ending `</pre>` from the output.
		$output = substr( $output, 6 );
		$output = substr( $output, 0, -7 );

		// Display the report.
		WP_CLI::log( $output );

		// issue success message.
		WP_CLI::success( 'COMPLETE' );
	};

	// Add this function as a wp-cli command.
	WP_CLI::add_command( 'ns-theme-check', 'ns_theme_check_run_wpcli_report' );

} // End if().
