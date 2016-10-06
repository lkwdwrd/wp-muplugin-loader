<?php
/**
 * General utility functions for use throughout the plugin and loader.
 *
 * @license MIT
 * @copyright Luke Woodward
 * @package WP_MUPlugin_Loader
 */
namespace LkWdwrd\MU_Loader\List_Table;

/**
 * Use the necessary namespaces.
 */
use LkWdwrd\MU_Loader\Loader;

// Create some aliases for long-named constants
const PS = DIRECTORY_SEPARATOR;
const LT = WP_Plugins_List_Table;
const MUDIR = WPMU_PLUGIN_DIR;

/**
 * Creates additional list-table to display all loaded Must-Use Plugins.
 *
 * Grabs all of the loaded must-use plugins, gets the data object for each, and
 * then uses the core list-table object to print additional rows for each of
 * the loaded plugins complete with all of the plugin info nomrally available
 * in the list-table.
 *
 * Each name is prefixed with '+  ' to help indicate it was added through the
 * Must-Use Plugins Loader.
 * @param  WP_Plugins_List_Table $lt    The core list table class.
 * @param  String                $ps    The path separator to use.
 * @param  String                $mudir The Must-Use Plugins directory.
 * @return void
 */
function list_table( $lt = LT, $ps = PS, $mudir = MUDIR ) {
	$table = new $lt;
	$spacer = '+&nbsp;&nbsp;';

	foreach ( Loader\get_muplugins() as $plugin_file) {
		$plugin_data = get_plugin_data( $mudir . $ps . $plugin_file, false);
		if ( empty( $plugin_data['Name'] ) ) {
			$plugin_data['Name'] = $plugin_file;
		}
		$plugin_data['Name'] = $spacer . $plugin_data['Name'];
		$table->single_row( array( $plugin_file, $plugin_data ) );
	}
}
