<?php
/**
 * View class file
 *
 * @since 1.0.0
 * @package Theme_Sniffer\View
 */

declare( strict_types=1 );

namespace Theme_Sniffer\View;

use Theme_Sniffer\Exception\Failed_To_Load_View;
use Theme_Sniffer\Exception\Invalid_URI;

/**
 * Class Escaped_View.
 *
 * This is a Decorator that decorates a given View with escaping meant for
 * HTML output.
 */
final class Escaped_View implements View {

	/**
	 * Tags that are allowed to be rendered.
	 */
	const ALLOWED_TAGS = array(
		'form'   => array(
			'id'       => true,
			'class'    => true,
			'action'   => true,
			'method'   => true,
			'tabindex' => true,
		),
		'input'  => array(
			'id'       => true,
			'class'    => true,
			'type'     => true,
			'name'     => true,
			'value'    => true,
			'tabindex' => true,
			'checked'  => true,
		),
		'select' => array(
			'id'       => true,
			'class'    => true,
			'type'     => true,
			'name'     => true,
			'value'    => true,
			'tabindex' => true,
		),
		'option' => array(
			'id'       => true,
			'class'    => true,
			'type'     => true,
			'name'     => true,
			'value'    => true,
			'selected' => true,
			'tabindex' => true,
		),
		'label'  => array(
			'for' => true,
		),
		'div'    => array(
			'class' => true,
		),
		'svg'    => array(
			'class'   => true,
			'style'   => true,
			'width'   => true,
			'height'  => true,
			'viewbox' => true,
			'xmlns'   => true,
		),
		'g'      => array(
			'fill'      => true,
			'fill-rule' => true,
			'transform' => true,
		),
		'path'   => array(
			'd'            => true,
			'id'           => true,
			'fill'         => true,
			'style'        => true,
			'stroke'       => true,
			'stroke-width' => true,
		),
		'mask'   => array(
			'id'   => true,
			'fill' => true,
		),
		'rect'   => array(
			'transform' => true,
			'fill'      => true,
			'width'     => true,
			'height'    => true,
			'rx'        => true,
			'ry'        => true,
			'x'         => true,
			'y'         => true,
		),
		'xmlns'  => array(
			'xlink' => true,
		),
		'defs'   => array(),
	);

	/**
	 * View instance to decorate.
	 *
	 * @var View
	 */
	private $view;

	/**
	 * Tags that are allowed to pass through the escaping function.
	 *
	 * @var array
	 */
	private $allowed_tags = array();

	/**
	 * Instantiate a Escaped_View object.
	 *
	 * @param View       $view         View instance to decorate.
	 * @param array|null $allowed_tags Optional. Array of allowed tags to let
	 *                                 through escaping functions. Set to sane
	 *                                 defaults if none provided.
	 */
	public function __construct( View $view, $allowed_tags = null ) {
		$this->view         = $view;
		$this->allowed_tags = null === $allowed_tags ?
		$this->prepare_allowed_tags( wp_kses_allowed_html( 'post' ) ) :
		$allowed_tags;
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
		return wp_kses( $this->view->render( $context ), $this->allowed_tags );
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
		return wp_kses(
			$this->view->render_partial( $uri, $context ),
			$this->allowed_tags
		);
	}

	/**
	 * Prepare an array of allowed tags by adding form elements to the existing
	 * array.
	 *
	 * This makes sure that the basic form elements always pass through the
	 * escaping functions.
	 *
	 * @param array $allowed_tags Allowed tags as fetched from the WordPress
	 *                            defaults.
	 *
	 * @return array Modified tags array.
	 */
	private function prepare_allowed_tags( $allowed_tags ) : array {
		return array_replace_recursive( $allowed_tags, self::ALLOWED_TAGS );
	}
}
