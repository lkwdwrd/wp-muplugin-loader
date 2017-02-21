<?php
namespace LkWdwrd\MU_Loader;

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
	/**
	 * Include the necessary files.
	 */
	public function setUp() {
		require_once PROJECT . '/util/util.php';

		parent::setUp();
	}

	/**
	 * Make sure the rel_path function makes relative paths.
	 */
	public function test_rel_path() {
		$abspath1 = '/a/random/path/to/a/place';
		$abspath2 = '/a/random/path/to/another/place';

		$calculatedRel = Util\rel_path( $abspath1, $abspath2, '/' );
		$this->assertEquals( $calculatedRel, '../../another/place' );
	}
}
