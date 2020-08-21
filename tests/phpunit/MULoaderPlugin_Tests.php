<?php
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

namespace LkWdwrd\MU_Loader;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use LkWdwrd\Composer\MULoaderPlugin;
use WP_Mock;
use PHPUnit\Framework\TestCase;

class MULoaderPlugin_Tests extends TestCase {
	private const TMP_DIR = __DIR__ . '/tmp';

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

	public function tearDown() {
		parent::tearDown();

		// Delete anything in self::TMP_DIR
		array_map( 'unlink', glob( self::TMP_DIR . '/mu-plugins/*' ) );
	}

	/**
	 * Test setup method.
	 */
	public function test_dump_require_file_dumps_expected_file() {
		$package = $this->getMockBuilder( PackageInterface::class )->getMock();
		$config = $this->getMockBuilder( Config::class )->getMock();
		$composer = $this->getMockBuilder( Composer::class )->getMock();
		$io = $this->getMockBuilder( IOInterface::class )->getMock();

		$package->method( 'getExtra' )->willReturn(
			[
				'installer-paths' => [
					'/mu-plugins/{$name}' => [
						"type:wordpress-muplugin"
					]
				]
			]
		);

		$config->method( 'has' )->with( 'vendor-dir' )->willReturn( false );
		$config->method( 'get' )->with( 'vendor-dir' )->willReturn( self::TMP_DIR );

		$composer->method( 'getPackage' )->willReturn( $package );
		$composer->method( 'getConfig' )->willReturn( $config );

		$plugin = new MULoaderPlugin();
		$plugin->activate($composer, $io);

		$plugin->dumpRequireFile();

		self::assertFileExists( self::TMP_DIR . '/mu-plugins/mu-require.php' );
		self::assertFileEquals( __DIR__ .'/tools/mu-plugins/mu-require.php', self::TMP_DIR . '/mu-plugins/mu-require.php' );
	}
}
