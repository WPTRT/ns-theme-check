<?php
/**
 * The helpers trait file
 *
 * @since   1.0.0
 * @package Theme_Sniffer\Helpers
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Helpers;

use Theme_Sniffer\Api\Template_Tags_Request;
use Theme_Sniffer\Exception\No_Themes_Present;

/**
 * Sniffer helpers trait
 *
 * This trait contains some helper methods
 */
trait Sniffer_Helpers {
	/**
	 * Returns standards
	 *
	 * Includes a 'theme_sniffer_add_standards' filter, so that user can add their own standard. The standard has to be added
	 * in the composer before bundling the plugin.
	 *
	 * @since 1.0.0 Added filter so that user can add their own standards. Moved to a trait.
	 * @since 0.1.3
	 *
	 * @return array Standards details.
	 */
	public function get_wpcs_standards() : array {
		$standards = [
			'wordpress-theme' => [
				'label'       => 'WPThemeReview',
				'description' => esc_html__( 'Ruleset for WordPress theme review requirements (Required)', 'theme-sniffer' ),
				'default'     => 1,
			],
			'wordpress-core'  => [
				'label'       => 'WordPress-Core',
				'description' => esc_html__( 'Main ruleset for WordPress core coding standards (Optional)', 'theme-sniffer' ),
				'default'     => 0,
			],
			'wordpress-extra' => [
				'label'       => 'WordPress-Extra',
				'description' => esc_html__( 'Extended ruleset for recommended best practices (Optional)', 'theme-sniffer' ),
				'default'     => 0,
			],
			'wordpress-docs'  => [
				'label'       => 'WordPress-Docs',
				'description' => esc_html__( 'Additional ruleset for WordPress inline documentation standards (Optional)', 'theme-sniffer' ),
				'default'     => 0,
			],
			'wordpress-vip'   => [
				'label'       => 'WordPress-VIP',
				'description' => esc_html__( 'Extended ruleset for WordPress VIP coding requirements (Optional)', 'theme-sniffer' ),
				'default'     => 0,
			],
		];

		if ( has_filter( 'theme_sniffer_add_standards' ) ) {
			$standards = apply_filters( 'theme_sniffer_add_standards', $standards );
		}

		return $standards;
	}

	/**
	 * Return all the active themes
	 *
	 * @since  1.0.0 Moved to a trait.
	 *
	 * @throws Api_Response_Error If API is down.
	 * @return array Array of active themes.
	 */
	public function get_active_themes() : array {
		$all_themes   = wp_get_themes();
		$active_theme = ( wp_get_theme() )->get( 'Name' );

		if ( empty( $all_themes ) ) {
			throw No_Themes_Present::message(
				esc_html__( 'No themes present in the themes directory.', 'theme-sniffer' )
			);
		}

		$themes = [];
		foreach ( $all_themes as $theme_slug => $theme ) {
			$theme_name    = $theme->get( 'Name' );
			$theme_version = $theme->get( 'Version' );

			if ( $theme_name === $active_theme ) {
				$theme_name = "(Active) $theme_name";
			}

			$themes[ $theme_slug ] = "$theme_name - v$theme_version";

		}

		return $themes;
	}

	/**
	 * Returns PHP versions.
	 *
	 * @since 1.0.0 Added PHP 7.x versions. Moved to a trait.
	 * @since 0.1.3
	 *
	 * @return array PHP versions.
	 */
	public function get_php_versions() : array {
		return [
			'5.2',
			'5.3',
			'5.4',
			'5.5',
			'5.6',
			'7.0',
			'7.1',
			'7.2',
			'7.3',
		];
	}

	/**
	 * Returns theme tags.
	 *
	 * @since 1.0.0 Moved to a trait and refactored to use tags from API.
	 * @since 0.1.3
	 *
	 * @return array Theme tags array.
	 */
	public function get_theme_tags() : array {

		return get_transient( Template_Tags_Request::TEMPLATE_TRANSIENT );
	}

	/**
	 * Helper method that returns the default stnadard
	 *
	 * @since 1.0.0
	 * @return string Name of the default standard.
	 */
	public function get_default_standard() : string {
		return 'WPThemeReview';
	}

	/**
	 * Helper method to get a list of required headers
	 *
	 * @since 1.0.0
	 * @return array List of required headers.
	 */
	public function get_required_headers() {
		return [
			'Name',
			'Description',
			'Author',
			'Version',
			'License',
			'License URI',
			'TextDomain',
		];
	}

	/**
	 * Check WP Core's Required PHP Version
	 *
	 * The functionality to check WP core wasn't added until 5.1.0, so this will
	 * address users who are on older WP versions and fetch from the API.  The
	 * code is copied from the core function wp_check_php_version.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_check_php_version/
	 *
	 * @since 1.1.0
	 *
	 * @return string|false $response String containing minimum PHP version required for user's install of WP. False on failure.
	 */
	public function get_wp_minimum_php_version() {
		if ( function_exists( 'wp_check_php_version' ) ) {
			$response = wp_check_php_version();
		} else {
			$version = phpversion();
			$key     = md5( $version );

			$response = get_site_transient( 'php_check_' . $key );
			if ( false === $response ) {
				$url = 'http://api.wordpress.org/core/serve-happy/1.0/';
				if ( wp_http_supports( array( 'ssl' ) ) ) {
					$url = set_url_scheme( $url, 'https' );
				}

				$url = add_query_arg( 'php_version', $version, $url );

				$response = wp_remote_get( $url );

				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
					return false;
				}

				/**
				 * Response should be an array with:
				 *  'recommended_version' - string - The PHP version recommended by WordPress.
				 *  'is_supported' - boolean - Whether the PHP version is actively supported.
				 *  'is_secure' - boolean - Whether the PHP version receives security updates.
				 *  'is_acceptable' - boolean - Whether the PHP version is still acceptable for WordPress.
				 */
				$response = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( ! is_array( $response ) ) {
					return false;
				}

				set_site_transient( 'php_check_' . $key, $response, WEEK_IN_SECONDS );
			}

			if ( isset( $response['is_acceptable'] ) && $response['is_acceptable'] ) {
				/**
				 * Filters whether the active PHP version is considered acceptable by WordPress.
				 *
				 * Returning false will trigger a PHP version warning to show up in the admin dashboard to administrators.
				 *
				 * This filter is only run if the wordpress.org Serve Happy API considers the PHP version acceptable, ensuring
				 * that this filter can only make this check stricter, but not loosen it.
				 *
				 * @since 5.1.1
				 *
				 * @param bool   $is_acceptable Whether the PHP version is considered acceptable. Default true.
				 * @param string $version       PHP version checked.
				 */
				$response['is_acceptable'] = (bool) apply_filters( 'wp_is_php_version_acceptable', true, $version );
			}
		}

		if ( ! isset( $response['minimum_version'] ) ) {
			return false;
		}

		return $response['minimum_version'];
	}

	/**
	 * Helper method to get the minimum PHP version supplied by theme
	 * or the WP core default.
	 *
	 * @since 1.1.0
	 *
	 * @return string Minimum PHP Version String.
	 */
	public function get_minimum_php_version() {

		// WP Core minimum PHP version - only used for fallback if API fails, and no transient stored.
		$minimum_php_version = '5.2';

		// Check API for minimum WP core version supported.
		$php_check = $this->get_wp_minimum_php_version();

		// Checks response success or transient data.
		if ( $php_check !== false ) {
			$minimum_php_version = $php_check;
		}

		$readme = wp_normalize_path( get_template_directory() . '/readme.txt' );

		if ( file_exists( $readme ) ) {

			// Check if theme has set minimum PHP version in it's readme.txt file.
			$theme_php_version = get_file_data( $readme, [ 'minimum_php_version' => 'Requires PHP' ] );

			// Theme has provided an override to minimum PHP version defined by WP Core.
			if ( ! empty( $theme_php_version['minimum_php_version'] ) ) {
				$minimum_php_version = $theme_php_version['minimum_php_version'];
			}
		}

		// Theme Sniffer's supported PHP version strings are X.X format.
		$minimum_php_version = substr( $minimum_php_version, 0, 3 );

		// Check Theme Sniffer's supported PHP Versions and find closest PHP version.
		$supported_php_version  = null;
		$theme_sniffer_versions = $this->get_php_versions();

		foreach ( $theme_sniffer_versions as $php_version ) {
			if ( $supported_php_version === null || abs( $minimum_php_version - $supported_php_version ) > abs( $php_version - $minimum_php_version ) ) {
				$supported_php_version = $php_version;
			}
		}

		// Ensure a supported version was found or just use the minimum PHP version determined appropriate.
		if ( $supported_php_version !== null ) {
			$minimum_php_version = $supported_php_version;
		}

		return $minimum_php_version;
	}
}
