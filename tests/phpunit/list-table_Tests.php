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
use Mockery;
use PHPUnit\Framework\TestCase;

class List_Table_Tests extends TestCase {
	/**
	 * Include the necessary files. Run setup scripts.
	 */
	public function setUp() {
		define( 'WPMU_PLUGIN_DIR', '/root' );
		require_once __DIR__ . '/tools/WP_Plugins_List_Table.php';
		require_once PROJECT . '/util/list-table.php';
		\WP_Mock::setUp();
		parent::setUp();
	}
	/**
	 * Run tear down scripts.
	 * @return [type] [description]
	 */
	public function tearDown() {
		\WP_Mock::tearDown();
		parent::tearDown();
	}
	/**
	 * Make sure the rel_path function makes relative paths.
	 */
	public function test_list_table() {
		// Set up mocks
		WP_Mock::wpFunction( 'LkWdwrd\MU_Loader\Loader\get_muplugins', [
			'return' => [
				'random/random.php',
				'testing/notsame.php',
				'lastone/lastone.php'
			]
		] );
		WP_Mock::wpFunction( 'get_plugin_data', [
			'args' => ['/root/random/random.php', false ],
			'return' => [
				'Name' => 'Random MU Plugin'
			]
		] );
		WP_Mock::wpFunction( 'get_plugin_data', [
			'args' => ['/root/testing/notsame.php', false],
			'return' => []
		] );
		WP_Mock::wpFunction( 'get_plugin_data', [
			'args' => ['/root/lastone/lastone.php', false],
			'return' => [
				'Name' => 'The Last One',
				'arbitrary' => [ 1, 2, 3 ],
				'another' => 21
			]
		] );

		// Run the function
		List_Table\list_table();

		// Verify the expected data was passed to the single_row method.
		$this->assertEquals(
			\WP_Plugins_List_Table::$received,
			[
				[
					'random/random.php',
					[ 'Name' => '+&nbsp;&nbsp;Random MU Plugin' ],
				],
				[
					'testing/notsame.php',
					[ 'Name' => '+&nbsp;&nbsp;testing/notsame.php'],
				],
				[
					'lastone/lastone.php',
					[
						'Name' => '+&nbsp;&nbsp;The Last One',
						'arbitrary' => [ 1, 2, 3 ],
						'another' => 21
					]
				]
			]
		);
	}
}
