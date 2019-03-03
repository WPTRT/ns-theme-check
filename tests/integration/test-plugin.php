<?php
/**
 * Class Plugin tests
 *
 * @package Theme_Sniffer\Tests\Integration\Src
 */

use Theme_Sniffer\Core\Plugin;
use Theme_Sniffer\Core\Plugin_Factory;

/**
 * Class that tests the Main plugin functionality.
 */
class Plugin_Integration_Test extends WP_UnitTestCase {

	/**
	 * Plugin class instance
	 *
	 * @var object
	 */
	private $plugin;

	/**
	 * Test suite setUp method
	 */
	public function setUp() {
		parent::setUp();

		$this->plugin = new Plugin();
	}

	/**
	 * Test suite tearDown method
	 */
	public function tearDown() {
		$this->plugin = '';

		parent::tearDown();
	}

	/**
	 * Method that tests the activate() method that runs when plugin is activated
	 */
	public function test_plugin_activate() {
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
	 * Method that tests the deactivate() method that runs when ACF plugin is missing
	 */
	public function test_plugin_deactivate() {
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
	 * Tests that register method was called
	 */
	public function test_plugin_register() {
		$this->plugin->register();

		$result = has_action( 'plugins_loaded', 'register_services' );

		$this->assertNotFalse( 'integer', $result );
	}

	/**
	 * Test register asset manifest failure
	 */
	public function test_register_assets_manifest() {
		$this->plugin->register_assets_manifest_data();

		$this->assertTrue( defined( 'ASSETS_MANIFEST' ) );
	}

	/**
	 * Test for plugin settings link
	 */
	public function test_plugin_settings_link() {
		$links = $this->plugin->plugin_settings_link( [] );

		$this->assertTrue( gettype( $links ) === 'array' );
		$this->assertEquals( '<a href="http://example.org/wp-admin/admin.php?page=theme-sniffer">Theme Sniffer Page</a>', $links[0] );
	}

	/**
	 * Test for extra headers added
	 */
	public function test_extra_headers() {
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
}
