<?php

function adoIsNamspaceEnabled()
{
	return ( version_compare( PHP_VERSION, "5.3.0" ) == +1);
}

if ( !adoIsNamspaceEnabled())
{
	define( "namespace", "echo");
}
?>
