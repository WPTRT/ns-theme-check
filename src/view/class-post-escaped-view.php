<?php
/**
 * Post Escaped view class file
 *
 * @since 1.0.0
 * @package Theme_Sniffer\View
 */

declare( strict_types=1 );

namespace Theme_Sniffer\View;

use Theme_Sniffer\Exception\Failed_To_Load_View;
use Theme_Sniffer\Exception\Invalid_URI;
/**
 * Class Post_Escaped_View.
 *
 * This is a Decorator that decorates a given View with escaping meant for
 * standard HTML post output.
 */
final class Post_Escaped_View implements View {
	/**
	 * View instance to decorate.
	 *
	 * @var View
	 */
	private $view;
	/**
	 * Instantiate a Post_Escaped_View object.
	 *
	 * @param View $view View instance to decorate.
	 */
	public function __construct( View $view ) {
		$this->view = $view;
	}
	/**
	 * Render a given URI.
	 *
	 * @param array $context Context in which to render.
	 *
	 * @return string Rendered HTML.
	 * @throws Failed_To_Load_View If the View URI could not be loaded.
	 */
	public function render( array $context = array() ) : string {
		return wp_kses_post( $this->view->render( $context ) );
	}
	/**
	 * Render a partial view.
	 *
	 * This can be used from within a currently rendered view, to include
	 * nested partials.
	 *
	 * The passed-in context is optional, and will fall back to the parent's
	 * context if omitted.
	 *
	 * @param string     $uri     URI of the partial to render.
	 * @param array|null $context Context in which to render the partial.
	 *
	 * @return string Rendered HTML.
	 * @throws Invalid_URI If the provided URI was not valid.
	 * @throws Failed_To_Load_View If the view could not be loaded.
	 */
	public function render_partial( $uri, array $context = null ) : string {
		return wp_kses_post( $this->view->render_partial( $uri, $context ) );
	}
}
