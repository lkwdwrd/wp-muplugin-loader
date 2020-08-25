<?php

namespace LkWdwrd\Composer\Tests;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use LkWdwrd\Composer\MULoaderPlugin;
use PHPUnit\Framework\TestCase;

class MULoaderPluginTest extends TestCase {
	private const TMP_DIR = PROJECT_TESTS . 'tmp';
	private const TOOLS_DIR = PROJECT_TESTS . 'tools';

	public function tearDown(): void {
		parent::tearDown();

		// Delete anything in self::TMP_DIR
		array_map( 'unlink', glob( self::TMP_DIR . '/mu-plugins/*' ) );
	}

	public function test_dump_require_file_dumps_expected_file(): void {
		$composer = $this->mock_composer(
			[
				'installer-paths' => [
					'/mu-plugins/{$name}' => [
						"type:wordpress-muplugin"
					]
				]
			]
		);

		$io = $this->getMockBuilder( IOInterface::class )->getMock();

		$plugin = new MULoaderPlugin();
		$plugin->activate($composer, $io);

		$plugin->dumpRequireFile();

		self::assertFileExists( self::TMP_DIR . '/mu-plugins/mu-require.php' );
		self::assertFileEquals( self::TOOLS_DIR . '/mu-plugins/mu-require.php', self::TMP_DIR . '/mu-plugins/mu-require.php' );
	}

	public function test_dump_require_file_dumps_expected_file_with_set_file(): void {
		$muRequireFile = 'zzz-mu-require.php';
		$composer = $this->mock_composer(
			[
				'installer-paths' => [
					'/mu-plugins/{$name}' => [
						"type:wordpress-muplugin"
					]
				],
				'mu-require-file' => $muRequireFile,
			]
		);

		$io = $this->getMockBuilder( IOInterface::class )->getMock();

		$plugin = new MULoaderPlugin();
		$plugin->activate($composer, $io);

		$plugin->dumpRequireFile();

		self::assertFileExists( self::TMP_DIR . '/mu-plugins/' . $muRequireFile );
		self::assertFileEquals( self::TOOLS_DIR . '/mu-plugins/mu-require.php', self::TMP_DIR . '/mu-plugins/' . $muRequireFile );
	}

	public function test_dump_require_file_does_not_dump_if_mu_require_file_set_to_false(): void {
		$composer = $this->mock_composer(
			[
				'installer-paths' => [
					'/mu-plugins/{$name}' => [
						"type:wordpress-muplugin"
					]
				],
				'mu-require-file' => false,
			]
		);

		$io = $this->getMockBuilder( IOInterface::class )->getMock();

		$plugin = new MULoaderPlugin();
		$plugin->activate($composer, $io);

		$plugin->dumpRequireFile();

		self::assertFileNotExists( self::TMP_DIR . '/mu-plugins/mu-require.php' );
	}

	/**
	 * @param array $extraConfig Config for the extra section you want returned from getExtra()
	 *
	 * @return Composer|\PHPUnit\Framework\MockObject\MockObject
	 */
	private function mock_composer( array $extraConfig = [] ) {
		$package = $this->getMockBuilder( PackageInterface::class )->getMock();
		$config = $this->getMockBuilder( Config::class )->getMock();
		$composer = $this->getMockBuilder( Composer::class )->getMock();

		$package->method( 'getExtra' )->willReturn( $extraConfig);

		$config->method( 'has' )->with( 'vendor-dir' )->willReturn( false );
		$config->method( 'get' )->with( 'vendor-dir' )->willReturn( self::TMP_DIR );

		$composer->method( 'getPackage' )->willReturn( $package );
		$composer->method( 'getConfig' )->willReturn( $config );

		return $composer;
	}
}
