<?php
/**
 * File that holds the renderable interface.
 *
 * @since 1.0.0
 * @package Theme_Sniffer\Core
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Core;

/**
 * Interface Renderable.
 *
 * An object that can be rendered.
 */
interface Renderable {
	/**
	 * Render the current Renderable.
	 *
	 * @param array $context Context in which to render.
	 *
	 * @return string Rendered HTML.
	 */
	public function render( array $context = array() ) : string;
}
