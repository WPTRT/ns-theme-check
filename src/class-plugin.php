<?php
/**
 * Main plugin file
 *
 * @package Theme_Sniffer\Core
 */

namespace Theme_Sniffer\Core;

use Theme_Sniffer\Assets\Assets_Aware;
use Theme_Sniffer\Assets\Assets_Handler;

use Theme_Sniffer\Api;
use Theme_Sniffer\Admin_Menus;
use Theme_Sniffer\i18n;
use Theme_Sniffer\Callback;

use Theme_Sniffer\Exception;

/**
 * Plugins main class that handles plugins object composition,
 * also known as an object graph.
 * This class acts as a controller that hooks the plugin functionality
 * into the WordPress request lifecycle.
 */
final class Plugin implements Registerable, Has_Activation, Has_Deactivation {
	/**
	 * Assets handler instance.
	 *
	 * @var Assets_Handler
	 */
	private $assets_handler;

	/**
	 * Array of instantiated services.
	 *
	 * @var Service[]
	 */
	private $services = [];

	/**
	 * Instantiate a Plugin object.
	 *
	 * @param Assets_Handler|null $assets_handler Optional. Instance of the
	 *                                           assets handler to use.
	 */
	public function __construct( Assets_Handler $assets_handler = null ) {
		$this->assets_handler = $assets_handler ?: new Assets_Handler();
	}
	/**
	 * Activate the plugin.
	 *
	 * @throws Exception\Plugin_Activation_Failure If a condition for plugin activation isn't met.
	 */
	public function activate() {
		if ( ! is_callable( 'shell_exec' ) || false !== stripos( ini_get( 'disable_functions' ), 'shell_exec' ) ) {
			$error_message = esc_html__( 'Theme Sniffer requires shell_exec to be enabled to function.', 'theme-sniffer' );
			throw Exception\Plugin_Activation_Failure::activation_message( $error_message );
		};

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			include_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
			\deactivate_plugins( PLUGIN_BASENAME );

			$error_message = esc_html__( 'Theme Sniffer requires PHP 7.0 or greater to function.', 'theme-sniffer' );
			throw Exception\Plugin_Activation_Failure::activation_message( $error_message );
		}

		$this->register_services();

		// Activate that which can be activated.
		foreach ( $this->services as $service ) {
			if ( $service instanceof Has_Activation ) {
				$service->activate();
			}
		}

		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin.
	 */
	public function deactivate() {
		$this->register_services();

		// Deactivate that which can be deactivated.
		foreach ( $this->services as $service ) {
			if ( $service instanceof Has_Deactivation ) {
				$service->deactivate();
			}
		}

		flush_rewrite_rules();
	}

	/**
	 * Register the plugin with the WordPress system.
	 *
	 * @throws Exception\Invalid_Service If a service is not valid.
	 */
	public function register() {
		$this->register_assets_manifest_data();

		add_action( 'plugins_loaded', [ $this, 'register_services' ] );
		add_action( 'init', [ $this, 'register_assets_handler' ] );
		add_action( 'plugin_action_links_' . PLUGIN_BASENAME, [ $this, 'plugin_settings_link' ] );
		add_filter( 'extra_theme_headers', [ $this, 'add_headers' ] );
	}

	/**
	 * Register the individual services of this plugin.
	 *
	 * @throws Exception\Invalid_Service If a service is not valid.
	 */
	public function register_services() {
		// Bail early so we don't instantiate services twice.
		if ( ! empty( $this->services ) ) {
			return;
		}

		$classes = $this->get_service_classes();

		$this->services = array_map(
			[ $this, 'instantiate_service' ],
			$classes
		);

		array_walk(
			$this->services,
			function( Service $service ) {
				$service->register();
			}
		);
	}

	/**
	 * Register the assets handler.
	 */
	public function register_assets_handler() {
		$this->assets_handler->register();
	}

	/**
	 * Return the instance of the assets handler in use.
	 *
	 * @return Assets_Handler
	 */
	public function get_assets_handler() {
		return $this->assets_handler;
	}
	/**
	 * Add go to theme check page link on plugin page.
	 *
	 * @since 1.0.0 Moved to main plugin class file.
	 * @since 0.1.3
	 *
	 * @param  array $links Array of plugin action links.
	 * @return array Modified array of plugin action links.
	 */
	public function plugin_settings_link( array $links ) : array {
		$settings_page_link = '<a href="' . admin_url( 'admin.php?page=theme-sniffer' ) . '">' . esc_attr__( 'Theme Sniffer Page', 'theme-sniffer' ) . '</a>';
		array_unshift( $links, $settings_page_link );

		return $links;
	}

	/**
	 * Allow fetching custom headers.
	 *
	 * @since 0.1.3
	 *
	 * @param array $extra_headers List of extra headers.
	 *
	 * @return array List of extra headers.
	 */
	public static function add_headers( array $extra_headers ) : array {
		$extra_headers[] = 'License';
		$extra_headers[] = 'License URI';
		$extra_headers[] = 'Template Version';

		return $extra_headers;
	}

	/**
	 * Register bundled asset manifest
	 *
	 * @throws Exception\Missing_Manifest Throws error if manifest is missing.
	 * @return void
	 */
	public function register_assets_manifest_data() {

		// phpcs:disable
		$response = file_get_contents(
			rtrim( plugin_dir_path( __DIR__ ), '/' ) . '/assets/build/manifest.json'
		);

		// phpcs:enable

		if ( ! $response ) {
			$error_message = esc_html__( 'manifest.json is missing. Bundle the plugin before using it.', 'developer-portal' );
			throw Exception\Missing_Manifest::message( $error_message );
		}

		define( 'ASSETS_MANIFEST', (string) $response );
	}

	/**
	 * Instantiate a single service.
	 *
	 * @param string $class Service class to instantiate.
	 *
	 * @return Service
	 * @throws Exception\Invalid_Service If the service is not valid.
	 */
	private function instantiate_service( $class ) {
		if ( ! class_exists( $class ) ) {
			throw Exception\Invalid_Service::from_service( $class );
		}

		$service = new $class();

		if ( ! $service instanceof Service ) {
			throw Exception\Invalid_Service::from_service( $service );
		}

		if ( $service instanceof Assets_Aware ) {
			$service->with_assets_handler( $this->assets_handler );
		}

		return $service;
	}

	/**
	 * Get the list of services to register.
	 *
	 * @return array<string> Array of fully qualified class names.
	 */
	private function get_service_classes() : array {
		return [
			Api\Template_Tags_Request::class,
			i18n\Internationalization::class,
			Admin_Menus\Sniff_Page::class,
			Callback\Run_Sniffer_Callback::class,
		];
	}
}
