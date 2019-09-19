<?php
/**
 * Class Plugin tests
 *
 * @package Theme_Sniffer\Tests\Integration\Src
 */

use Theme_Sniffer\Core\Plugin;
use Theme_Sniffer\Core\Plugin_Factory;

use Theme_Sniffer\Exception;

/**
 * Class that tests the Main plugin functionality.
 */
class Main_Plugin_Functionality extends WP_UnitTestCase {

	/**
	 * Plugin class instance
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Test suite setUp method
	 */
	public function setUp() {
		parent::setUp();

		$this->plugin = Plugin_Factory::create();
		$this->plugin->register();
	}

	/**
	 * Test suite tearDown method
	 */
	public function tearDown() {
		$this->plugin = '';

		parent::tearDown();
	}

	/**
	 * Test if all the hooks were registered.
	 */
	public function test_hooks_are_registered() {
		$this->assertEquals( \has_action( 'plugins_loaded', [ $this->plugin, 'register_services' ] ), 10 );
		$this->assertEquals( \has_action( 'init', [ $this->plugin, 'register_assets_handler' ] ), 10 );
		$this->assertEquals( \has_action( 'plugin_action_links_' . PLUGIN_BASENAME, [ $this->plugin, 'plugin_settings_link' ] ), 10 );
		$this->assertEquals( \has_filter( 'extra_theme_headers', [ $this->plugin, 'add_headers' ] ), 10 );
	}

	/**
	 * Method that tests the activate() method that runs when plugin is activated.
	 */
	public function test_plugin_activate_method_is_called() {
		$this->user_id = $this->factory()->user->create(
			[
				'role' => 'administrator',
			]
		);

		wp_set_current_user( $this->user_id );

		$this->plugin->activate();

		$this->assertInstanceOf( 'Theme_Sniffer\Core\Registerable', $this->plugin );
		$this->assertInstanceOf( 'Theme_Sniffer\Core\Has_Activation', $this->plugin );
		$this->assertInstanceOf( 'Theme_Sniffer\Core\Has_Deactivation', $this->plugin );
	}

	/**
	 * Method that tests the deactivate() method that runs when plugin is deactivated.
	 */
	public function test_plugin_deactivate_method_is_called() {
		$this->user_id = $this->factory()->user->create(
			[
				'role' => 'administrator',
			]
		);

		wp_set_current_user( $this->user_id );

		$this->plugin->deactivate();

		$this->assertInstanceOf( 'Theme_Sniffer\Core\Registerable', $this->plugin );
		$this->assertInstanceOf( 'Theme_Sniffer\Core\Has_Activation', $this->plugin );
		$this->assertInstanceOf( 'Theme_Sniffer\Core\Has_Deactivation', $this->plugin );
	}

	/**
	 * Test register asset manifest failure.
	 */
	public function test_assets_manifest_is_registered() {
		$this->plugin->register_assets_manifest_data();

		$this->assertTrue( defined( 'ASSETS_MANIFEST' ) );
	}

	/**
	 * Test for plugin settings link
	 */
	public function test_plugin_settings_link_works() {
		$links = $this->plugin->plugin_settings_link( [] );

		$this->assertTrue( gettype( $links ) === 'array' );
		$this->assertEquals( '<a href="http://example.org/wp-admin/admin.php?page=theme-sniffer">Theme Sniffer Page</a>', $links[0] );
	}

	/**
	 * Test for extra headers added
	 */
	public function test_extra_headers_are_set() {
		$headers = $this->plugin->add_headers( [] );

		$theme_object = wp_get_theme();

		// Trick to get private properties. We do this because the added headers will be empty in
		// the test theme.
		$reflected_object = new ReflectionClass( $theme_object );
		$private_headers  = $reflected_object->getProperty( 'headers' );
		$private_headers->setAccessible( true );

		$theme_headers_added = $private_headers->getValue( $theme_object );

		$this->assertTrue( gettype( $headers ) === 'array' );
		$this->assertContains( 'License', $headers );
		$this->assertContains( 'License URI', $headers );
		$this->assertContains( 'Template Version', $headers );
		$this->assertArrayHasKey( 'License', $theme_headers_added );
		$this->assertArrayHasKey( 'License URI', $theme_headers_added );
		$this->assertArrayHasKey( 'Template Version', $theme_headers_added );
	}

	/**
	 * Test the getter for the assets handler instance
	 */
	public function test_assets_handler_getter() {
		$assets_handler = $this->plugin->get_assets_handler();

		$this->assertTrue( class_exists( get_class( $assets_handler ) ) );
	}

	/**
	 * Test the setter for the assets handler instance
	 */
	public function test_assets_handler_setter() {
		$this->plugin->register_assets_handler();
		$assets_handler = $this->plugin->get_assets_handler();

		$this->assertTrue( class_exists( get_class( $assets_handler ) ) );
	}
}
