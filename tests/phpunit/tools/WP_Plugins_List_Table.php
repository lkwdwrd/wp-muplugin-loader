<?php
/**
 * Mocks out the WP_Plugins_List_Table class.
 */

/**
 * Mock for the core WP_Plugins_List_Table class.
 */
class WP_Plugins_List_Table {
	/**
	 * Provides access to anything received by the single_row method.
	 * @var array
	 */
	public static $received = array();

	/**
	 * Push any data recieved into the static recieved array.
	 */
	function single_row( $data ) {
		array_push( self::$received, $data );
	}
}
