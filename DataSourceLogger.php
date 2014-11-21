<?php

class DataSourceLogger
{
	function __construct()
	{
		global $__DataSourceLogger;
		$__DataSourceLogger = $this;
	}

	function debug( $msg )
	{
		
	}
	function notice( $msg )
	{
		
	}
	function warning( $msg )
	{
		
	}
	function error( $msg )
	{
		
	}
	function getInstance()
	{
		global $__DataSourceLogger;
		return $__DataSourceLogger;
	}
	function setInstance($value)
	{
		global $__DataSourceLogger;
		return $__DataSourceLogger = $value;
	}
}
new DataSourceLogger();

