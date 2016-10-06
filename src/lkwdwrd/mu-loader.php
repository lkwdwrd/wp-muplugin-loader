<?php
/**
 * MU-Plugin Autoloader
 *
 * The nice thing about this file, this is really nothing to test. It's purely
 * a loader file, stitching things together.
 *
 * @license MIT
 * @copyright Luke Woodward
 * @package WP_MUPlugin_Loader
 */
namespace LkWdwrd\MU_Loader;

/**
 * Disallow direct access, this should only load through WordPress.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Require utilities.
 */
require_once( __DIR__ . '/util/loader.php' );
require_once( __DIR__ . '/util/util.php' );
require_once( __DIR__ . '/util/list-table.php' );

/**
 * If we are not installing, run the `mu_loader()`
 */
if (! defined('WP_INSTALLING') || ! WP_INSTALLING ) {
	// Run the loader unless installing
	Loader\mu_loader();
}

/**
 * Pretty print the the plugins into the mu-plugins list-table.
 */
add_action(
	'after_plugin_row_mu-require.php',
	'LkWdwrd\MU_Loader\List_Table\list_table',
	10,
	0
);
