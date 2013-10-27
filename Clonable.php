<?php

if (  version_compare( PHP_VERSION, "5.0.0" ) == +1) {
	require_once( dirname( __FILE__ ) . "/Clonable_php5.php" );
}
else {
	require_once( dirname( __FILE__ ) . "/Clonable_php4.php" );
}


define( "CLASS_Clonable", get_class( new Clonable() ) );
?>