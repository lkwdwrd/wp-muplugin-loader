<?php

use PHPUnit\Framework\TestCase;
use LkWdwrd\MU_Loader\Loader;

class Loader_Tests extends TestCase {
	/**
	 * @var string
	 */
	private $key;

	/**
	 * Include the necessary files. Run setup scripts.
	 */
	public function setUp(): void {
		define( 'WPMU_PLUGIN_DIR', __DIR__ . '/tools' );
		define( 'WP_PLUGIN_DIR', '/other' );
		$this->key = md5( json_encode( scandir( WPMU_PLUGIN_DIR ) ) );
		require_once PROJECT . '/util/loader.php';
		WP_Mock::setUp();
		parent::setUp();
	}
	/**
	 * Run tear down scripts.
	 */
	public function tearDown(): void {
		WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Ensure the loader function includes passed plugin files.
	 */
	public function test_mu_loader(): void {
		// Run control assertions.
		self::assertFalse( defined( '\LkWdwrd\Test\included' ) );
		self::assertFalse( defined( '\LkWdwrd\Test2\included' ) );
		// Run the method to include plugin files.
		Loader\mu_loader( [ 'includable.php', 'includable2.php'] );
		// Make sure the files are now included.
		self::assertTrue( defined( '\LkWdwrd\Test\included' ) );
		self::assertTrue( defined( '\LkWdwrd\Test2\included' ) );
	}

	/**
	 * The test_mu_loader should use call get_muplugins by default.
	 *
	 * We can't test that very well, but we can short-circuit the method pretty
	 * easily by returning an empty array from the transient call. The transient
	 * should be called twice, once to get the cache key, and again to get the
	 * plugin list. By asserting this is called twice we can assume it is
	 * properly calling the get_muplugins function.
	 *
	 * This will likely break on a major refactor, but should catch most of the
	 * possible used cases.
	 */
	public function test_mu_loader_default(): void {
		// Set up mocks
		WP_Mock::userFunction( 'get_site_transient', [
			'times' => 2,
			'return_in_order' => [
				$this->key,
				[]
			]
		] );

		// Run the test
		self::assertNull( Loader\mu_loader() );
	}

	/**
	 * The get_muplugins function will return cached data if available.
	 */
	public function test_get_muplugins_cache(): void {
		// Set up mocks
		WP_Mock::userFunction( 'get_site_transient', [
			'return_in_order' => [
				$this->key,
				[ 'a/plugin.php' ]
			]
		] );
		// Run the test
		$result = Loader\get_muplugins();
		// Verify the results
		self::assertEquals( [ 'a/plugin.php' ], $result );
	}

	/**
	 * If the cache misses get_muplugins will generate a new list of plugins.
	 */
	public function test_get_muplugins_nocache(): void {
		$expected = [ 'random/plugin1.php', 'random/plugin2.php' ];
		// Set up mocks
		WP_Mock::userFunction( 'get_site_transient', [
			'return_in_order' => [
				$this->key,
				false
			]
		] );
		WP_Mock::userFunction( 'LkWdwrd\MU_Loader\Util\rel_path', [
			'return' => 'relpath'
		] );
		// only plugins in directories should pass: rootplugin.php will go away.
		// WP will include root plugins in it's normal course.
		WP_Mock::userFunction( 'get_plugins', [
			'args' => [ DIRECTORY_SEPARATOR . 'relpath' ],
			'return' => [
				'random/plugin1.php' => true,
				'random/plugin2.php' => true,
				'rootplugin.php' => true
			]
		] );
		WP_Mock::userFunction( 'set_site_transient', [
			'times' => 1,
			'args' => [ $this->key, $expected ]
		] );
		// Run the test
		$result = Loader\get_muplugins();
		// Make sure the set cache function was called.
		self::assertEquals( $expected, $result );
	}

	/**
	 * If get_plugins is not defined it will include the admin file.
	 */
	public function test_get_muplugins_admin_require(): void {
		// Set up mocks
		WP_Mock::userFunction( 'get_site_transient', [
			'return_in_order' => [
				$this->key,
				false
			]
		] );
		WP_Mock::userFunction( 'LkWdwrd\MU_Loader\Util\rel_path', [
			'return' => 'relpath'
		] );
		WP_Mock::passthruFunction( 'set_site_transient' );
		// Run the test.
		// This will include `tools/wp-admin/includes/plugin.php` which sets
		// a flag in it's namespace to indicate it has loaded.
		// First run a control assertion, then run a test assertion.
		self::assertFalse( defined( '\LkWdWrd\Test\WP_Admin\included' ) );
		// Run the test
		Loader\get_muplugins();
		// Verify the results
		self::assertTrue( defined( '\LkWdWrd\Test\WP_Admin\included' ) );
	}

	/**
	 * The get_muloader_key function returns the existing key if no changes
	 */
	public function test_get_muloader_key_same(): void {
		//Set up mocks
		WP_Mock::userFunction( 'get_site_transient', [ 'return' => $this->key ] );
		// Run the test
		$result = Loader\get_muloader_key();
		// Verify results
		self::assertEquals( $this->key, $result );
	}

	/**
	 * The get_muloader_key function deletes old transients with change.
	 */
	public function test_get_muloader_key_different(): void {
		//Set up mocks
		WP_Mock::userFunction( 'get_site_transient', [
			'args' => [ 'lkw_mu_loader_key' ],
			'return' => 'non-matching-key'
		] );
		WP_Mock::userFunction( 'delete_site_transient', [
			'times' => 1,
			'args' => [ 'non-matching-key' ]
		] );
		WP_Mock::userFunction( 'set_site_transient', [
			'times' => 1,
			'args' => [ 'lkw_mu_loader_key', $this->key ]
		] );
		// Run the test
		$result = Loader\get_muloader_key();
		// Verify results
		self::assertEquals( $this->key, $result );
	}
}
