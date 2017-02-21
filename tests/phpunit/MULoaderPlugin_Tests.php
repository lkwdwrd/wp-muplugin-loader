<?php
namespace TenUp\ElasticPress_io\Core;

/**
* This is a very basic test case to get things started. You should probably rename this and make
* it work for your project. You can use all the tools provided by WP Mock and Mockery to create
* your tests. Coverage is calculated against your includes/ folder, so try to keep all of your
* functional code self contained in there.
*
* References:
*   - http://phpunit.de/manual/current/en/index.html
*   - https://github.com/padraic/mockery
*   - https://github.com/10up/wp_mock
*/

use WP_Mock;
use PHPUnit\Framework\TestCase;

class MULoaderPlugin_Tests extends TestCase {
	protected $testFiles = [];

	public function setUp() {
		if ( ! empty( $this->testFiles ) ) {
			foreach ( $this->testFiles as $file ) {
				if ( file_exists( PROJECT . $file ) ) {
					require_once( PROJECT . $file );
				}
			}
		}

		parent::setUp();
	}

	/**
	 * Test setup method.
	 */
	public function test_setup() {
		$this->assertTrue( true );
	}
}
