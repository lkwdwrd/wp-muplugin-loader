<?php
/**
 * A helper for testing to make sure this file is includable.
 */
namespace LkWdWrd\Test\WP_Admin;
use WP_Mock;

// Set the included flag.
const included = true;

// Mock the get_plugins function to return an empty array.
WP_Mock::userFunction( 'get_plugins', [ 'return' => [] ] );
