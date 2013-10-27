<?php

function error_handler( $errno, $errstr, $errfile, $errline, $errcontext)
 {
 	$trace = debug_backtrace();
 	print_r( "In {$errfile}:{$errline}: {$errstr}" );

 	$msg = "Trace: \n";
 	foreach( $trace as $item ) {
 		if ( !array_key_exists( 'file', $item ) ) continue;

 		$file = @$item['file'];
 		$line = @$item['line'];
 		$function = @$item['function'];

 		$msg .= "{$file}:{$line}: In {$function}()\n";
 	}
 	print_r( $msg . "\n" );
 }
set_error_handler( "error_handler", E_WARNING | E_NOTICE | E_ERROR);
?>