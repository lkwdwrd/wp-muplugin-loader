<?php
namespace LkWdwrd\MU_Loader\Util;

// Create some aliases for long-named constants
const PS = DIRECTORY_SEPARATOR;

function rel_path( $from, $to, $ps = PS ) {
	$arFrom = explode($ps, rtrim( $from, $ps ) );
	$arTo = explode( $ps, rtrim( $to, $ps ) );
	while( count( $arFrom ) && count( $arTo ) && ( $arFrom[0] == $arTo[0] ) ) {
		array_shift( $arFrom );
		array_shift( $arTo );
	}
	return str_pad( '', count( $arFrom ) * 3, '..' . $ps ) . implode( $ps, $arTo );
}
