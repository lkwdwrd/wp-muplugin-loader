<?php
namespace LKW\MU_Loader\List_Table;
use LKW\MU_Loader\Util;

// Create some aliases for long-named constants
const PS = DIRECTORY_SEPARATOR;
const LT = WP_Plugins_List_Table;
const MUDIR = WPMU_PLUGIN_DIR;

function list_table( $lt = LT, $ps = PS, $mudir = MUDIR ) {
	$table = new $lt;
	$spacer = '+&nbsp;&nbsp;';

	foreach ( Util\get_muplugins() as $plugin_file) {
		$plugin_data = get_plugin_data( $mudir . $ps . $plugin_file, false);
		if ( empty( $plugin_data['Name'] ) ) {
			$plugin_data['Name'] = $plugin_file;
		}
		$plugin_data['Name'] = $spacer . $plugin_data['Name'];
		$table->single_row( array( $plugin_file, $plugin_data ) );
	}
}
