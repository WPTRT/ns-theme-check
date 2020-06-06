<?php
/**
 * Templated view class file
 *
 * @since 1.0.0
 * @package Theme_Sniffer\View
 */

declare( strict_types=1 );

namespace Theme_Sniffer\View;

use Theme_Sniffer\Exception\Invalid_URI;

/**
 * Class Templated_View.
 *
 * Looks within the child theme and parent theme folders first for a view,
 * before defaulting to the plugin folder.
 */
final class Templated_View extends Base_View {

	/**
	 * Validate an URI.
	 *
	 * @param string $uri URI to validate.
	 *
	 * @return string Validated URI.
	 * @throws Invalid_URI If an invalid URI was passed into the View.
	 */
	protected function validate( $uri ) : string {
		$uri = $this->check_extension( $uri, static::VIEW_EXTENSION );

		foreach ( $this->get_locations( $uri ) as $location ) {
			if ( is_readable( $location ) ) {
				return $location;
			}
		}

		if ( ! is_readable( $uri ) ) {
			throw Invalid_URI::from_uri( $uri );
		}

		return $uri;
	}

	/**
	 * Get the possible locations for the view.
	 *
	 * @param string $uri URI of the view to get the locations for.
	 *
	 * @return array Array of possible locations.
	 */
	protected function get_locations( $uri ) : array {
		return array(
			trailingslashit( \get_stylesheet_directory() ) . $uri,
			trailingslashit( \get_template_directory() ) . $uri,
			trailingslashit( dirname( __DIR__, 2 ) ) . $uri,
		);
	}
}
