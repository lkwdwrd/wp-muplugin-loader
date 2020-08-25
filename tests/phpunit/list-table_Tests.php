<?php

use PHPUnit\Framework\TestCase;
use LkWdwrd\MU_Loader\List_Table;

class ListTable_Tests extends TestCase {
	/**
	 * Include the necessary files. Run setup scripts.
	 */
	public function setUp(): void {
		define( 'WPMU_PLUGIN_DIR', '/root' );
		require_once __DIR__ . '/tools/WP_Plugins_List_Table.php';
		require_once PROJECT . '/util/list-table.php';
		\WP_Mock::setUp();
		parent::setUp();
	}

	/**
	 * Run tear down scripts.
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Make sure the rel_path function makes relative paths.
	 */
	public function test_list_table(): void {
		// Set up mocks
		WP_Mock::userFunction( 'LkWdwrd\MU_Loader\Loader\get_muplugins', [
			'return' => [
				'random/random.php',
				'testing/notsame.php',
				'lastone/lastone.php'
			]
		] );
		$base = WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR;
		WP_Mock::userFunction( 'get_plugin_data', [
			'args' => [$base . 'random/random.php', false ],
			'return' => [
				'Name' => 'Random MU Plugin'
			]
		] );
		WP_Mock::userFunction( 'get_plugin_data', [
			'args' => [$base . 'testing/notsame.php', false],
			'return' => []
		] );
		WP_Mock::userFunction( 'get_plugin_data', [
			'args' => [$base . 'lastone/lastone.php', false],
			'return' => [
				'Name' => 'The Last One',
				'arbitrary' => [ 1, 2, 3 ],
				'another' => 21
			]
		] );

		// Run the function
		List_Table\list_table();

		// Verify the expected data was passed to the single_row method.
		self::assertEquals(
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
			],
			\WP_Plugins_List_Table::$received
		);
	}
}
