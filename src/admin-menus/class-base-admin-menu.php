<?php
/**
 * Admin Menus abstract class file
 *
 * @since 1.0.0
 * @package Theme_Sniffer\Admin_Menus
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Admin_Menus;

use Theme_Sniffer\Core\Renderable;
use Theme_Sniffer\Core\Service;
use Theme_Sniffer\View\Escaped_View;
use Theme_Sniffer\View\Templated_View;

/**
 * Abstract class Base_Admin_Menus.
 *
 * This abstract class can be extended to add new admin menus
 */
abstract class Base_Admin_Menu implements Renderable, Service {
	/**
	 * Register the admin menu.
	 */
	public function register() {
		add_action(
			'admin_menu',
			function() {
				add_menu_page(
					$this->get_title(),
					$this->get_menu_title(),
					$this->get_capability(),
					$this->get_menu_slug(),
					array( $this, 'process_admin_menu' ),
					$this->get_icon(),
					$this->get_position()
				);
			}
		);
	}

	/**
	 * Process the admin menu attributes and prepare rendering.
	 *
	 * The echo doesn't need to be escaped since it's escaped
	 * in the render method.
	 *
	 * @param array|string $atts Attributes as passed to the admin menu.
	 *
	 * @return void The rendered content needs to be echoed.
	 */
	public function process_admin_menu( $atts ) {
		$atts                  = $this->process_attributes( $atts );
		$atts['admin_menu_id'] = $this->get_menu_slug();
		$atts['nonce_field']   = $this->render_nonce();

	echo $this->render( (array) $atts ); // phpcs:ignore
	}

	/**
	 * Render the current Renderable.
	 *
	 * @param array $context Context in which to render.
	 *
	 * @return string Rendered HTML.
	 */
	public function render( array $context = array() ) : string {
		try {
			$view = new Escaped_View(
				new Templated_View( $this->get_view_uri() )
			);

			return $view->render( $context );
		} catch ( \Exception $exception ) {
			// Don't let exceptions bubble up. Just render the exception message into the admin menu.
			return sprintf(
				'<pre>%s</pre>',
				$exception->getMessage()
			);
		}
	}

	/**
	 * Render the nonce.
	 *
	 * @return string Hidden field with a nonce.
	 */
	protected function render_nonce() : string {
		ob_start();

		wp_nonce_field(
			$this->get_nonce_action(),
			$this->get_nonce_name()
		);

		return ob_get_clean();
	}

	/**
	 * Verify the nonce and return the result.
	 *
	 * @return bool Whether the nonce could be successfully verified.
	 */
	protected function verify_nonce() : bool {
		$nonce_name = $this->get_nonce_name();

		if ( ! isset( $_POST[ $nonce_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			return false;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ $nonce_name ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification

		$result = wp_verify_nonce(
			$nonce,
			$this->get_nonce_action()
		);

		return false !== $result;
	}

	/**
	 * Get the action of the nonce to use.
	 *
	 * @return string Action of the nonce.
	 */
	protected function get_nonce_action() : string {
		return "{$this->get_menu_slug()}_action";
	}

	/**
	 * Get the name of the nonce to use.
	 *
	 * @return string Name of the nonce.
	 */
	protected function get_nonce_name() : string {
		return "{$this->get_menu_slug()}_nonce";
	}

	/**
	 * Get the title to use for the admin page.
	 *
	 * @return string The text to be displayed in the title tags of the page when the menu is selected.
	 */
	abstract protected function get_title() : string;

	/**
	 * Get the menu title to use for the admin menu.
	 *
	 * @return string The text to be used for the menu.
	 */
	abstract protected function get_menu_title() : string;

	/**
	 * Get the capability required for this menu to be displayed.
	 *
	 * @return string The capability required for this menu to be displayed to the user.
	 */
	abstract protected function get_capability() : string;

	/**
	 * Get the menu slug.
	 *
	 * @return string The slug name to refer to this menu by. Should be unique for this menu page and only include lowercase alphanumeric, dashes, and underscores characters to be compatible with sanitize_key().
	 */
	abstract protected function get_menu_slug() : string;

	/**
	 * Get the URL to the icon to be used for this menu
	 *
	 * @return string The URL to the icon to be used for this menu.
	 *                * Pass a base64-encoded SVG using a data URI, which will be colored to match
	 *                  the color scheme. This should begin with 'data:image/svg+xml;base64,'.
	 *                * Pass the name of a Dashicons helper class to use a font icon,
	 *                  e.g. 'dashicons-chart-pie'.
	 *                * Pass 'none' to leave div.wp-menu-image empty so an icon can be added via CSS.
	 */
	protected function get_icon() : string {
		return 'none';
	}

	/**
	 * Get the position of the menu.
	 *
	 * @return int Number that indicates the position of the menu.
	 * 5   - below Posts
	 * 10  - below Media
	 * 15  - below Links
	 * 20  - below Pages
	 * 25  - below comments
	 * 60  - below first separator
	 * 65  - below Plugins
	 * 70  - below Users
	 * 75  - below Tools
	 * 80  - below Settings
	 * 100 - below second separator
	 */
	protected function get_position() : int {
		return 100;
	}

	/**
	 * Get the View URI to use for rendering the admin menu.
	 *
	 * @return string View URI.
	 */
	abstract protected function get_view_uri() : string;

	/**
	 * Process the admin menu attributes.
	 *
	 * @param array|string $atts Raw admin menu attributes passed into the
	 *                           admin menu function.
	 *
	 * @return array Processed admin menu attributes.
	 */
	abstract protected function process_attributes( $atts ) : array;
}
