<?php
namespace LkWdwrd\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use LkWdwrd\MU_Loader\Util;

require_once dirname( __DIR__ ) . '/util/util.php';

class MULoaderPlugin implements PluginInterface, EventSubscriberInterface {
	private $extras;
	private $config;
	/**
	 * Apply plugin modifications to composer
	 *
	 * @param Composer    $composer
	 * @param IOInterface $io
	 */
	public function activate( Composer $composer, IOInterface $io ) {
		$this->extras = $composer->getPackage()->getExtra();
		$this->config = $composer->getConfig();
	}

	public static function getSubscribedEvents() {
		return array(
			'pre-autoload-dump' => 'dumpRequireFile',
			'pre-package-install' => 'overridePluginTypes'
		);
	}

	public function overridePluginTypes( $event ) {
		// Only act on wordpress-plugin types
		$package = $event->getOperation()->getPackage();
		if ( 'wordpress-plugin' !== $package->getType() ) {
			return;
		}

		// Only act when there is a force-mu key holding an array in extras
		$extras = $this->extras;
		if( empty( $extras['force-mu'] ) || !is_array( $extras['force-mu'] ) ) {
			return;
		}

		// Check to see if the current package is in the force-mu extra
		// If it is, set it's type to 'wordpress-muplugin'
		$slug = str_replace( 'wpackagist-plugin/', '', $package->getName() );
		if ( in_array( $slug, $extras['force-mu'] ) ) {
			$package->setType( 'wordpress-muplugin' );
		}
	}

	public function dumpRequireFile() {
		$muRelPath = $this->findMURelPath();

		// If we didn't find a relative MU Plugins path, bail.
		if ( ! $muRelPath ) {
			return;
		}

		// Find the relative path from the mu-plugins dir to the mu-loader file.
		$muPath = $this->resolveMURelPath( $muRelPath );
		$loadFile = dirname( __DIR__ ) . DIRECTORY_SEPARATOR . 'mu-loader.php';
		$toLoader = DIRECTORY_SEPARATOR . Util\rel_path( $muPath, $loadFile );

		// Write the boostrapping PHP file.
		file_put_contents(
			$muPath . 'mu-require.php',
			"<?php\nrequire_once __DIR__ . '${toLoader}';\n"
		);
	}

	protected function findMURelPath() {
		$path = false;
		// Only keep going if we have install-paths in extras.
		if ( empty( $this->extras['installer-paths'] ) || ! is_array( $this->extras['installer-paths'] ) ) {
			return false;
		}
		// Find the array to the mu-plugin path.
		foreach( $this->extras['installer-paths'] as $path => $types ) {
			if ( ! is_array( $types ) ) {
				continue;
			}
			if ( ! in_array( 'type:wordpress-muplugin', $types ) ) {
				continue;
			}
			$path = str_replace( '{$name}', '', $path );
			break;
		}
		return $path;
	}

	protected function resolveMURelPath( $relpath ) {
		// Find the actual base path by removing the vendor-dir raw config path.
		if ( $this->config->has( 'vendor-dir' ) ) {
			$tag = $this->config->raw()['config']['vendor-dir'];
		} else {
			$tag = '';
		}
		$basepath = str_replace( $tag, '', $this->config->get('vendor-dir') );
		// Return the abosolute path.
		return $basepath . $relpath;
	}
}
