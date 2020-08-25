<?php
/**
 * Contains shared utility functions for general use by the plugin.
 *
 * @license MIT
 * @copyright Luke Woodward
 * @package WP_MUPlugin_Loader
 */
namespace LkWdwrd\MU_Loader\Util;

// Create some aliases for long-named constants
const PS = DIRECTORY_SEPARATOR;

/**
 * Finds a relative path between two absolute paths.
 *
 * Note that this does not work between two different drives. It only works for
 * two absolute paths located on the same disk.
 *
 * @param  string $from The absolute path to go from.
 * @param  string $to   The absolute path to go to.
 * @param  string $ps   The string to use as the path separator.
 * @return string       The relative path between $from and $to.
 */
function rel_path( $from, $to, $ps = PS ): string {
	// Turn paths into array.
	$arFrom = explode($ps, rtrim( normalize( $from, $ps ), $ps ) );
	$arTo = explode( $ps, rtrim( normalize( $to, $ps ), $ps ) );
	// Strip the common roots from both arrays.
	while( count( $arFrom ) && count( $arTo ) && ( $arFrom[0] == $arTo[0] ) ) {
		array_shift( $arFrom );
		array_shift( $arTo );
	}
	// for any items left in from, add '../' and then append the remaining
	// to items.
	return str_pad( '', count( $arFrom ) * 3, '..' . $ps ) . implode( $ps, $arTo );
}

/**
 * Convert all different directory separators into one unified style
 *
 * @param  string $path The path to normalize
 * @param  string $ds   The directory separator to standardize on
 * @return string       The normalized path
 */
function normalize( $path, $ds ): string {
	return str_replace( array( '/', "\\" ), $ds, $path );
}
