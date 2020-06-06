<?php
/**
 * Dependency injection container
 *
 * @package Theme_Sniffer\Core
 * @link    https://github.com/WPTRT/theme-sniffer
 * @license https://opensource.org/licenses/MIT MIT
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Core;

use \DI\Container;
use \DI\ContainerBuilder;

/**
 * A dependency injection container class
 *
 * @package Theme_Sniffer\Core
 *
 * @since 1.2.0
 */
final class Di_Container {

	/**
	 * Returns a prepared list of services with definitions for DI container
	 *
	 * @param array $services List of service classes.
	 *
	 * @return array Definition list of services for DI container.
	 *
	 * @throws \Exception Throws exception if container cannot be created.
	 * @since 1.2.0
	 */
	public function get_di_services( array $services ) : array {
		$di_services = $this->get_prepared_service_array( $services );
		$container   = $this->get_di_container( $di_services );

		return array_map(
			static function( $class ) use ( $container ) {
				return $container->get( $class );
			},
			array_keys( $di_services )
		);
	}

	/**
	 * Return a DI container
	 *
	 * Build and return a DI container.
	 * Wire all the dependencies automatically, based on the provided array of
	 * class => dependencies from the get_di_services().
	 *
	 * @param array $services Array of service.
	 *
	 * @return Container
	 *
	 * @throws \Exception Throws exception if container cannot be created.
	 * @since 1.2.0
	 */
	private function get_di_container( array $services ) {
		$builder = new ContainerBuilder();

		$builder->enableCompilation( __DIR__ );

		$definitions = array();

		foreach ( $services as $service_name => $service_dependencies ) {
			$definitions[ $service_name ] = \DI\create()->constructor( ...$this->get_di_dependencies( $service_dependencies ) );
		}

		return $builder->addDefinitions( $definitions )->build();
	}

	/**
	 * Get dependencies from PHP-DI
	 *
	 * Return prepared Dependency Injection objects.
	 * If you pass a class use PHP-DI to prepare if not just output it.
	 *
	 * @since 1.2.0
	 *
	 * @param array $dependencies Array of classes/parameters to inject in constructor.
	 *
	 * @return array
	 */
	private function get_di_dependencies( array $dependencies ) : array {
		return array_map(
			static function( $dependency ) {
				if ( class_exists( $dependency ) ) {
					return \DI\get( $dependency );
				}
				return $dependency;
			},
			$dependencies
		);
	}

	/**
	 * Prepare services array
	 *
	 * Takes an argument of services, which is a multidimensional array,
	 * that has a class name for a  key, and a list of dependencies as a value, or no value at all.
	 * It then loops though this array, and if the dependencies are an array it will just add this to
	 * the value of the $prepared_services array, and the key will be the class name.
	 * In case that there is no dependency.
	 *
	 * @since 1.2.0
	 *
	 * @param array $services A list of classes with optional dependencies.
	 *
	 * @return array
	 */
	private function get_prepared_service_array( array $services ) : array {
		$prepared_services = array();

		foreach ( $services as $class => $dependencies ) {
			if ( ! is_array( $dependencies ) ) {
				$prepared_services[ $dependencies ] = array();
			} else {
				$prepared_services[ $class ] = $dependencies;
			}
		}

		return $prepared_services;
	}
}
