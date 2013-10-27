<?php

class DataSourceLoggerImpl extends Singleton
{
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
	static function init()
	{
		$obj = new DataSourceLoggerImpl();
		$obj->createFrontend("DataSourceLogger");
	}
}
DataSourceLoggerImpl::init();

if ( defined("ECLIPSE")) {
	class DataSourceLogger extends DataSourceLoggerImpl{}; 
}


