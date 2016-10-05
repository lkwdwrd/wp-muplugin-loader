<?php
namespace LkWdwrd\MU_Loader\Loader;
use LkWdwrd\MU_Loader\Util;

// Create some aliases for long-named constants
const PS = DIRECTORY_SEPARATOR;
const ABS = ABSPATH;
const PDIR = WP_PLUGIN_DIR;
const MUDIR = WPMU_PLUGIN_DIR;

function mu_loader( $plugins = false, $ps = PS, $mudir = MUDIR ) {
	if ( ! $plugins ) {
		$plugins = get_muplugins();
	}
	foreach( $plugins as $plugin ) {
		require_once $mudir . $ps . $plugin;
	}
}

function get_muplugins( $abs = ABS, $pdir = PDIR, $mudir = MUDIR, $ps = PS ) {
	$key = get_muloader_key( $mudir );
	//Try to get the plugin list from the cache
	delete_site_transient( $key );
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
