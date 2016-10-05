<?php
/**
 * MU-Plugin Autoloader
 *
 * @license MIT
 * @copyright Luke Woodward
 */
namespace LKW\MU_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( __DIR__ . '/lkw-util/util.php' );
require_once( __DIR__ . '/lkw-util/list-table.php' );

if (! defined('WP_INSTALLING') || ! WP_INSTALLING ) {
	// Run the loader unless installing
	Util\mu_loader();
}

/**
* Add rows for each subplugin under this plugin when listing mu-plugins in wp-admin
*/
add_action(
	'after_plugin_row_lkw-mu-loader.php',
	'LKW\MU_Loader\List_Table\list_table',
	10,
	0
);
