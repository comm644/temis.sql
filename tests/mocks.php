<?php

if ( !function_exists('mysql_real_escape_string')) {
	//function required for default MySql DataSource
	function mysql_real_escape_string($s)
	{
		 return $s;
	}
}
