<?php
/******************************************************************************
 Copyright (c) 2008 by Alexei V. Vasilyev.  All Rights Reserved.                         
 -----------------------------------------------------------------------------
 Module     : Data Source Factory
 File       : DataSourceFactory.php
 Author     : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description:
******************************************************************************/
require_once( dirname( __FILE__ ) ."/Clonable.php" );


/**

\brief Data source factory, controls your data sources

This class provides \b Factory  for creating DataSource object.
and you can have creation object instance in \b uiPage constructor
bacause object does not have big internal state linked with database.

Factory provides common service for seting current used database engine.
via "one point change" approach.

\relates DataSource

*/
class DataSourceFactory
{
	/**  contains DSN to current used database
	 */
	var $_dsn="";

	/**
	 * constains  prototype for creating \b DS objects
	 * @var IDataSource|null
	 */
	var $_proto=null;

	
	/** construct factory

	@param string $dsn \b string describes Data Source Name  as next string:
	     engine_name://username:password@server[:port]/database

	@param mixed  $proto \b class instance which have support next interfaces:
	 \li Clonable
	 \li DataSource

	 */
	function DataSourceFactory( $dsn, IDataSource $proto )
	{
		if ( !is_subclass_of( $proto, CLASS_Clonable ) ) {
			throw new DatabaseException( "object " . get_class( $proto ) . " is not subclass of ". CLASS_Clonable );
		}

		$this->_dsn = $dsn;
		$this->_proto = $proto;
	}
	
	/** get connection for current used DSN and Data source object

	@return new created \b object wich was created from recevied in constructor prototype
	 */
	function getConnection()
	{
		
		$ds = $this->_proto->cloneObject();
		$ds->connect( $this->_dsn );
		
		return(  $ds );
	}
}
