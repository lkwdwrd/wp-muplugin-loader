<?php

namespace LkWdwrd\Composer\Tests;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use LkWdwrd\Composer\MULoaderPlugin;
use PHPUnit\Framework\TestCase;

class MULoaderPluginTest extends TestCase {
	private const TMP_DIR = __DIR__ . '/tmp';

	public function tearDown(): void {
		parent::tearDown();

		// Delete anything in self::TMP_DIR
		array_map( 'unlink', glob( self::TMP_DIR . '/mu-plugins/*' ) );
	}

	public function test_dump_require_file_dumps_expected_file(): void {
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
