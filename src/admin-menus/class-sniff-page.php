<?php
/**
 * Theme sniffer menu class file
 *
 * @since 1.0.0
 * @package Theme_Sniffer\Admin_Menus
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Admin_Menus;

use Theme_Sniffer\Helpers\Sniffer_Helpers;
use Theme_Sniffer\Exception\No_Themes_Present;

/**
* Theme sniffer page menu class
*
* Class that renders the Theme sniffer menu page
*/
final class Sniff_Page extends Base_Admin_Menu {

	use Sniffer_Helpers;

	const CAPABILITY = 'create_users';
	const MENU_SLUG  = 'theme-sniffer';
	const MENU_ICON  = 'dashicons-list-view';

	const VIEW_URI = 'views/theme-sniffer-page';

	/**
	 * Get the title to use for the admin page.
	 *
	 * @return string The text to be displayed in the title tags of the page when the menu is selected.
	 */
	protected function get_title() : string {
		return esc_html__( 'Theme Sniffer', 'theme-sniffer' );
	}

	/**
	 * Get the menu title to use for the admin menu.
	 *
	 * @return string The text to be used for the menu.
	 */
	protected function get_menu_title() : string {
		return esc_html__( 'Theme Sniffer', 'theme-sniffer' );
	}

	/**
	 * Get the capability required for this menu to be displayed.
	 *
	 * @return string The capability required for this menu to be displayed to the user.
	 */
	protected function get_capability() : string {
		return self::CAPABILITY;
	}

	/**
	 * Get the menu slug.
	 *
	 * @return string The slug name to refer to this menu by. Should be unique for this menu page and only include lowercase alphanumeric, dashes, and underscores characters to be compatible with sanitize_key().
	 */
	protected function get_menu_slug() : string {
		return self::MENU_SLUG;
	}

	/**
	 * Get the URL to the icon to be used for this menu
	 *
	 * @return string The URL to the icon to be used for this menu.
	 */
	protected function get_icon() : string {
		return self::MENU_ICON;
	}

	/**
	 * Get the View URI to use for rendering the admin menu.
	 *
	 * @return string View URI.
	 */
	protected function get_view_uri() : string {
		return self::VIEW_URI;
	}

	/**
	 * Process the admin menu attributes.
	 *
	 * @param array|string $atts Raw admin menu attributes passed into the
	 *                           admin menu function.
	 *
	 * @return array Processed admin menu attributes.
	 */
	protected function process_attributes( $atts ) : array {
		$atts = (array) $atts;

		try {
			$atts['themes'] = $this->get_active_themes();
		} catch ( No_Themes_Present $e ) {
			$atts['error'] = esc_html( $e->getMessage() );
		}

		$atts['standards']           = $this->get_wpcs_standards();
		$atts['php_versions']        = $this->get_php_versions();
		$atts['minimum_php_version'] = $this->get_minimum_php_version();
		$atts['current_theme']       = \get_stylesheet();
		$atts['standard_status']     = wp_list_pluck( $this->get_wpcs_standards(), 'default' );

		return $atts;
	}
}
