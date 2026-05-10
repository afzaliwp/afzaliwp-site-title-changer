<?php
function afzaliwp_tc_autoload( $class_name ) {
	if ( ! str_contains( $class_name, 'AfzaliWP\TitleChanger' ) ) {
		return;
	}

	$file = str_replace(
		        [
			        '_',
			        strtolower( 'AfzaliWP\TitleChanger' ),
			        '\\',
		        ],
		        [
			        '-',
			        __DIR__,
			        DIRECTORY_SEPARATOR,
		        ],
		        strtolower( $class_name ) ) . '.php';

	require_once $file;
}
