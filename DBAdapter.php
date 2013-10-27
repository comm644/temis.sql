<?php
/******************************************************************************
 Copyright (c) 2005 by Alexei V. Vasilyev.  All Rights Reserved.                         
 -----------------------------------------------------------------------------
 Module     : Database Base Adapter
 File       : DBAdapter.php
 Author     : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description:
******************************************************************************/
class DBAdapter
{
	/**
	 * Data source conenction
	 *
	 * @var DataSource
	 */
	var $_connection;

	function DBAdapter( &$db )
	{
		if ( !is_object( $db ) ) Diagnostics::error( "dataDource is not object" );
		$this->_connection = &$db;
	}
	function getConnection()
	{
		return( $this->_connection );
	}
	
	/** execute command

	  @return array or null if error
	 */
	function execute( $command )
	{
		print $command->getText() . "\n";
	}
	function getLastError()
	{
		return( $_connection->lastError() );
	}
}

?>