<?php

namespace LkWdwrd\Composer\Tests;

use Composer\Composer;
use Composer\Config;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Package\Package;
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

	public function test_override_plugin_types_sets_type_of_package_on_install(): void {
		$pluginName = 'vendor/wp-plugin';

		$composer = $this->mock_composer(
			[
				'installer-paths' => [
					'/mu-plugins/{$name}' => [
						'type:wordpress-muplugin'
					]
				],
				'force-mu' => [
					$pluginName
				],
			]
		);

		$io = $this->getMockBuilder( IOInterface::class )->getMock();

		$plugin = new MULoaderPlugin();
		$plugin->activate($composer, $io);

		$package = $this->mock_package( $pluginName );
		$package->expects( self::once() )->method( 'setType' )->with( 'wordpress-muplugin' );

		$operation = $this->getMockBuilder( InstallOperation::class )->disableOriginalConstructor()->getMock();
		$operation->method( 'getPackage' )->willReturn( $package );

		$packageEvent = $this->getMockBuilder( PackageEvent::class )->disableOriginalConstructor()->getMock();
		$packageEvent->method( 'getOperation' )->willReturn( $operation );

		$plugin->overridePluginTypes( $packageEvent );
	}

	public function test_override_plugin_types_sets_type_of_package_on_update(): void {
		$pluginName = 'vendor/wp-plugin';

		$composer = $this->mock_composer(
			[
				'installer-paths' => [
					'/mu-plugins/{$name}' => [
						'type:wordpress-muplugin'
					]
				],
				'force-mu' => [
					$pluginName
				],
			]
		);

		$io = $this->getMockBuilder( IOInterface::class )->getMock();

		$plugin = new MULoaderPlugin();
		$plugin->activate($composer, $io);

		$package = $this->mock_package( $pluginName );
		$package->expects( self::once() )->method( 'setType' )->with( 'wordpress-muplugin' );

		$operation = $this->getMockBuilder( UpdateOperation::class )->disableOriginalConstructor()->getMock();
		$operation->method( 'getTargetPackage' )->willReturn( $package );

		$packageEvent = $this->getMockBuilder( PackageEvent::class )->disableOriginalConstructor()->getMock();
		$packageEvent->method( 'getOperation' )->willReturn( $operation );

		$plugin->overridePluginTypes( $packageEvent );
	}

	public function test_override_plugin_types_sets_type_of_package_on_install_for_wpackagist_plugin(): void {
		$pluginName = 'wpackagist-plugin/wp-plugin';

		$composer = $this->mock_composer(
			[
				'installer-paths' => [
					'/mu-plugins/{$name}' => [
						'type:wordpress-muplugin'
					]
				],
				'force-mu' => [
					'wp-plugin' // Don't include wpackagist-plugin here.
				],
			]
		);

		$io = $this->getMockBuilder( IOInterface::class )->getMock();

		$plugin = new MULoaderPlugin();
		$plugin->activate($composer, $io);

		$package = $this->mock_package( $pluginName );
		$package->expects( self::once() )->method( 'setType' )->with( 'wordpress-muplugin' );

		$operation = $this->getMockBuilder( InstallOperation::class )->disableOriginalConstructor()->getMock();
		$operation->method( 'getPackage' )->willReturn( $package );

		$packageEvent = $this->getMockBuilder( PackageEvent::class )->disableOriginalConstructor()->getMock();
		$packageEvent->method( 'getOperation' )->willReturn( $operation );

		$plugin->overridePluginTypes( $packageEvent );
	}

	public function test_override_plugin_types_does_not_set_type_if_force_mu_is_empty(): void {
		$pluginName = 'vendor/wp-plugin';

		$composer = $this->mock_composer(
			[
				'installer-paths' => [
					'/mu-plugins/{$name}' => [
						'type:wordpress-muplugin'
					]
				],
				'force-mu' => [],
			]
		);

		$io = $this->getMockBuilder( IOInterface::class )->getMock();

		$plugin = new MULoaderPlugin();
		$plugin->activate($composer, $io);

		$package = $this->mock_package( $pluginName );
		$package->expects( self::never() )->method( 'setType' )->with( 'wordpress-muplugin' );

		$operation = $this->getMockBuilder( UpdateOperation::class )->disableOriginalConstructor()->getMock();
		$operation->method( 'getTargetPackage' )->willReturn( $package );

		$packageEvent = $this->getMockBuilder( PackageEvent::class )->disableOriginalConstructor()->getMock();
		$packageEvent->method( 'getOperation' )->willReturn( $operation );

		$plugin->overridePluginTypes( $packageEvent );
	}

	public function test_override_plugin_types_does_not_set_type_if_type_is_not_wordpress_plugin(): void {
		$pluginName = 'vendor/wp-plugin';

		$composer = $this->mock_composer(
			[
				'installer-paths' => [
					'/mu-plugins/{$name}' => [
						'type:wordpress-muplugin'
					]
				],
				'force-mu' => [],
			]
		);

		$io = $this->getMockBuilder( IOInterface::class )->getMock();

		$plugin = new MULoaderPlugin();
		$plugin->activate($composer, $io);

		$package = $this->mock_package( $pluginName, 'library' );
		$package->expects( self::never() )->method( 'setType' )->with( 'wordpress-muplugin' );

		$operation = $this->getMockBuilder( UpdateOperation::class )->disableOriginalConstructor()->getMock();
		$operation->method( 'getTargetPackage' )->willReturn( $package );

		$packageEvent = $this->getMockBuilder( PackageEvent::class )->disableOriginalConstructor()->getMock();
		$packageEvent->method( 'getOperation' )->willReturn( $operation );

		$plugin->overridePluginTypes( $packageEvent );
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

	/**
	 * @param string $pluginName Name of plugin name to return for getName call
	 * @param string $pluginType Name of plugin type to return for getType call
	 *
	 * @return Package|\PHPUnit\Framework\MockObject\MockObject
	 */
	private function mock_package( string $pluginName, string $pluginType = 'wordpress-plugin' ) {
		$package = $this->getMockBuilder( Package::class )->disableOriginalConstructor()->getMock();
		$package->method( 'getType' )->willReturn( $pluginType );
		$package->method( 'getName' )->willReturn( $pluginName );

		return $package;
	}
}
