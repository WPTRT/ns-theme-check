<?php
/**
 * Main plugin file
 *
 * @package Theme_Sniffer\Core
 */

declare( strict_types=1 );

namespace Theme_Sniffer\Core;

use Theme_Sniffer\Admin_Menus;
use Theme_Sniffer\Callback;
use Theme_Sniffer\Enqueue;
use Theme_Sniffer\Exception;
use Theme_Sniffer\i18n;

/**
 * Plugins main class that handles plugins object composition,
 * also known as an object graph.
 * This class acts as a controller that hooks the plugin functionality
 * into the WordPress request lifecycle.
 */
final class Plugin implements Registerable, Has_Activation, Has_Deactivation {
	/**
	 * Array of instantiated services.
	 *
	 * @var Service[]
	 */
	private $services = array();

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

		if ( ! is_callable( 'simplexml_load_string' ) || false !== stripos( ini_get( 'disable_functions' ), 'simplexml_load_string' ) ) {
			$error_message = esc_html__( 'Theme Sniffer requires libxml extension to function.', 'theme-sniffer' );
			throw Exception\Plugin_Activation_Failure::activation_message( $error_message );
		};

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			include_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		if ( version_compare( PHP_VERSION_ID, '70000', '<' ) ) {
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

		\flush_rewrite_rules();
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

		\flush_rewrite_rules();
	}

	/**
	 * Register the plugin with the WordPress system.
	 *
	 * @throws Exception\Invalid_Service If a service is not valid.
	 */
	public function register() {
		$this->register_assets_manifest_data();

		add_action( 'plugins_loaded', array( $this, 'register_services' ) );
		add_action( 'plugin_action_links_' . PLUGIN_BASENAME, array( $this, 'plugin_settings_link' ) );
		add_filter( 'extra_theme_headers', array( $this, 'add_headers' ) );
	}

	/**
	 * Register bundled asset manifest
	 *
	 * @throws Exception\Missing_Manifest Throws error if manifest is missing.
	 * @return void
	 */
	public function register_assets_manifest_data() {

		$response = file_get_contents(
			rtrim( plugin_dir_path( __DIR__ ), '/' ) . '/assets/build/manifest.json'
		);

		if ( ! $response ) {
			$error_message = esc_html__( 'manifest.json is missing. Bundle the plugin before using it.', 'developer-portal' );
			throw Exception\Missing_Manifest::message( $error_message );
		}

		define( 'ASSETS_MANIFEST', (string) $response );
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

		$container = new Di_Container();

		$this->services = $container->get_di_services( $this->get_service_classes() );

		array_walk(
			$this->services,
			static function( $class ) {
				if ( ! $class instanceof Registerable ) {
					return;
				}

				$class->register();
			}
		);
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
	 * Get the list of services to register.
	 *
	 * @return array<string> Array of fully qualified class names.
	 */
	private function get_service_classes() : array {
		return array(
			Admin_Menus\Sniff_Page::class,
			Callback\Run_Sniffer_Callback::class,
			Enqueue\Enqueue_Resources::class,
			i18n\Internationalization::class,
		);
	}
}
