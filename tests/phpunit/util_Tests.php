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

class Util_Tests extends TestCase {
	/**
	 * Include the necessary files.
	 */
	public function setUp() {
		require_once PROJECT . '/util/util.php';

		parent::setUp();
	}

	/**
	 * Make sure the rel_path function makes relative paths.
	 * @dataProvider data_rel_path
	 */
	public function test_rel_path( $path1, $path2, $sep, $expected) {
		$this->assertEquals( Util\rel_path( $path1, $path2, $sep ), $expected );
	}

	public function data_rel_path(){
		return array(
			array(
				'/a/random/path/to/a/place',
				'/a/random/path/to/another/place',
				'/',
				'../../another/place',
			),
			array(
				'/somewhere/over/the/rainbow',
				'/somewhere/over/the/rainbow/bluebirds/sing',
				'/',
				'bluebirds/sing',
			),
			array(
				'/just/some/test',
				'/just\some\mixed\slash\example',
				'/',
				'../mixed/slash/example',
			),
			array(
				'/testing/inverse/directory/separators',
				'/unix/to/windows',
				'\\',
				'..\..\..\..\unix\to\windows',
			),
		);
	}
}
