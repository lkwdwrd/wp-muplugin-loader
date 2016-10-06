<?php
/**
 * All of the methods to properly load any Must-Use plugins into WordPress.
 *
 * @license MIT
 * @copyright Luke Woodward
 * @package WP_MUPlugin_Loader
 */
namespace LkWdwrd\MU_Loader\Loader;

/**
 * Use the necessary namespaces.
 */
use LkWdwrd\MU_Loader\Util;

// Create some aliases for long-named constants
const PS = DIRECTORY_SEPARATOR;
const ABS = ABSPATH;
const PDIR = WP_PLUGIN_DIR;
const MUDIR = WPMU_PLUGIN_DIR;

/**
 * The main loader method to get available Must-Use plugins and require them.
 *
 * @param  Array|Bool $plugins The array of plugins to install, or false. When
 *                             false it will gather plugins using the
 *                             `get_muplugins()` function. Default: false.
 * @param  String     $ps      The path separator to use when combining paths.
 *                             Default: DIRECTORY_SEPARATOR
 * @param  String     $mudir   The aboslute Must-Use Plugins directory string.
 *                             Default: WPMU_PLUGIN_DIR
 * @return void
 */
function mu_loader( $plugins = false, $ps = PS, $mudir = MUDIR ) {
	if ( ! $plugins ) {
		$plugins = get_muplugins();
	}
	foreach( $plugins as $plugin ) {
		require_once $mudir . $ps . $plugin;
	}
}

/**
 * Gets a list of the available plugins in the Must-Use Plugins directory.
 *
 * An attempt is made to load the plugin list from the cache. If the cache is
 * not available, it will load and run the WordPress core `get_plugins` function
 * to gather all plugins with the appropriate headers, compiling them into a
 * an array of fully qualified plugin paths.
 *
 * @param  String $abs   The WordPress Abosolute Path. Default: ABSPATH
 * @param  String $pdir  The WordPress Plugins Directory. Default: WP_PLUGIN_DIR
 * @param  String $mudir The WordPress MU Plugins Directory. Default:
 *                       WPMU_PLUGIN_DIR
 * @param  String $ps    The path seperator to use. Default: DIRECTORY_SEPARATOR
 * @return array         An array of aboslute paths to the plugin files.
 */
function get_muplugins( $abs = ABS, $pdir = PDIR, $mudir = MUDIR, $ps = PS ) {
	$key = get_muloader_key( $mudir );
	//Try to get the plugin list from the cache
	$plugins = get_site_transient( $key );
	// If the cache missed, regenerate it.
	if ( $plugins === false ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			// get_plugins is not included by default
			require $abs . 'wp-admin/includes/plugin.php';
		}
		$plugins = array();
		$rel_path = Util\rel_path( $pdir, $mudir );
		foreach ( get_plugins( $ps . $rel_path ) as $plugin_file => $data ) {
			// skip files directly at root
			if ( dirname( $plugin_file ) !== '.' ) {
				$plugins[] = $plugin_file;
			}
		}
		set_site_transient( $key, $plugins );
	}
	return $plugins;
}

// Generate a unique cache key that will change when MU directory changes.
// If the key changes, make sure transients don't stack up in the
// options table.
/**
 * Gets a unique key to use in caching the MU-Plugins list.
 *
 * Because this uses transients, we can't simply let the key change for
 * invalidation. To that end, we store the used key as a transient and then
 * pull that transient. We then create the cache key using an MD5 hash of The
 * files in the Must-Use plugins directory. If the files change, the key will
 * also change. If it does not match the old key, the previous cache entry is
 * removed and the new key is stored for future comparisons.
 *
 * Doing this ensures as the MU-Plugins directory changes, regaurdless of the
 * caching mechanism, even the options table, the data will not build up over
 * time. Especially important when the options table is used.
 * @param  String $mudir The MU Plugins Directory. Default: WPMU_PLUGIN_DIR
 * @return String        An MD5 cache key to use.
 */
function get_muloader_key( $mudir = MUDIR ) {
	$old_key = get_site_transient( 'lkw_mu_loader_key' );
	$key = md5( json_encode( scandir( $mudir ) ) );
	if ( $old_key !== $key ) {
		if ( $old_key ) {
			delete_site_transient( $old_key );
		}
		set_site_transient( 'lkw_mu_loader_key', $key );
	}
	return $key;
}
