<?php
/******************************************************************************
 Copyright (c) 2005 by Alexei V. Vasilyev.  All Rights Reserved.                         
 -----------------------------------------------------------------------------
 Module     : Database Objects adapter
 File       : DBObjectAdapter.php
 Author     : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description: see ADO.php
******************************************************************************/
require_once( dirname( __FILE__ ) . "/DBCommand.php" );
//require_once( dirname( __FILE__ ) . "/DBQueryHelper.php" );
require_once( dirname( __FILE__ ) . "/SQLStatement.php" );
require_once( dirname( __FILE__ ) . "/DBObjectCache.php" );

/**
 * dummy class for retrieving 'count' 
 *
 */
class DBObjectAdapter_count extends DBObject
{
	var $_table;
	var $count;
	
	function DBObjectAdapter_count($table)
	{
		$this->_table = $table;
	}
	function table_name()
	{
		return $this->_table;
	}
	function getColumnDefinition()
	{
		return array();
	}
}


/**
 * this class supports easy quick access to database.
 * Class upports  quick start for retrieving ans storing objects to database.
 * But if you whant use extended SQL quireies then use SQLStatement class hierarcy directly.
 * 
 * \para
 * Also adapter supports caching feature. for single retrieving and saving objects
 *
 */
class DBObjectAdapter extends DBAdapter
{
	/**
	 * Object prototype
	 *
	 * @var DBObject  (interface for DB object)
	 */
	var $def;
	var $table;

	var $cmdSelect;
	var $cmdQuery;
	var $signDummy = false;

	/**
	 * Construct DB Object adapter.  
	 * 	 
	 * @param unknown_type $db
	 * @param unknown_type $def
	 * @return DBObjectAdapter
	 */
	function DBObjectAdapter( &$db,$def )
	{
		if ( !is_object($def) || is_null( $def )) {
			Diagnostics::error( 'argument exception: object $def is not defined ot non object' );
		}
		if ( is_null( $db ) ) {
			Diagnostics::error( 'argument exception: data source is not defined' );
		}
		
		
		parent::DBAdapter( $db);
		$this->def   = $def;
		$this->table = $def->table_name();
	}


	/** select objects from data source
	 
	 @param array $values  empty dataset (array) fo filling
	 @param string|Expr $cond    expression of condition
	 @param string  $order   order tag (ascending)
	 @param bool $signUseID  sign what need place object into array by their IDs (ID=>object)
	 */
	function select( &$values, $cond=null, $order=null, $signUseID=TRUE )
	{
		$stm = new SQLStatementSelect ( $this->def );
		
		if ( is_object( $cond ) ) {
			$stm->setExpression( $cond );
		}
		else if ( is_string($cond )){
			Diagnostics::warning( "Do not use obsolete conditions" );
			$stm->setExpression(new ExprRaw($cond));
		}
		
		$order = explode(",",$order);
		foreach( $order as $crit )  {
			$pair = explode( " ", trim($crit) );
			$signDesc = ( count( $pair ) > 1 && strcmp( $pair[1], "DESC" )==0 );
			$stm->addOrder( $pair[0], !$signDesc );
		}
		$query = $stm->generate();
		$result = $stm->createResultContainer($signUseID);

		$rc = $this->_connection->querySelectEx( $query, $result);

		$values = $result->getResult();
		return $rc;
	}
	
	function insert( &$obj, $signUseChangedMembers=false )
	{
		DBObjectCache::reset($this->def);
						
		$stm = new SQLStatementInsert( $obj );
		$stm->enableUseChangedMembers( $signUseChangedMembers );

		$result = $stm->createResultContainer() ;
 		$rc = $this->_connection->queryStatement( $stm, $result );
		return( $rc );
	}

	function update( &$obj, $signSmart=false )
	{
		DBObjectCache::reset($this->def);
				
		$stm = new SQLStatementUpdate( $obj );
		$stm->signForceUpdate = !$signSmart;
		
		$rc = $this->_connection->queryStatement( $stm, $stm->createResultContainer() );
		return $rc;
	}

	function delete( $cond )
	{
			Diagnostics::warning( "Do not use obsolete conditions" );
		DBObjectCache::reset($this->def);
				
		return( $this->cmdDelete->execute( $cond) );
	}
	function getLastInsertID()
	{
		$conn = $this->getConnection();
		return( $conn->lastID() );
	}

	/**
	 * Insert Or Update object in database
	 *
	 * @param DBObject $obj
	 * @param bool $smartUpdate  sign that need activate smart update featuture or execute only update
	 * @return bool true on success
	 */
	function smart_update( &$obj, $smartUpdate=false )
	{
		if ( $obj->isNew() ) {
			return( $this->insert( $obj ) );
		}
		else {
			return( $this->update( $obj, $smartUpdate ) );
		}
	}
	
	/**
	 * Store object in database (insert or update)
	 *
	 * @param DBObject $obj
	 * @return bool true on success
	 */
	function updateObject( &$obj )
	{
		DBObjectCache::reset($this->def);
		
		return $this->smart_update( $obj, true );
	}

	function get( $cond, $order="" )
	{
		$values = array();
		$this->select( $values, $cond, $order, false );
		return array_shift($values);
	}

	/**
	 * Execute SQLStatmentSelect
	 *
	 * @param array $values result dataset
	 * @param SQLStatementSelect $stm  select statement 
	 * @param bool $signUseID   true if nned assign keys as primary key
	 * @return bool true on success
	 */
	function executeStatementSelect( &$values, $stm, $signUseID ) 
	{
		$result = $stm->createResultContainer ( $signUseID );
		$rc = $this->_connection->queryStatement( $stm, $result);
		
		$values = $result->getResult();
		return $rc;
	}
	
	function getByPrimaryKey( $value )
	{
		$obj = DBObjectCache::get( $value,$this->proto() );
		if ( $obj ) return( $obj );

		$stm = new SQLStatementSelect($this->def);
		$stm->setExpression ( new ExprEQ ( $this->def->getPrimaryKeyTag (), $value ) );
		
		$values = array();
		$this->executeStatementSelect( $values, $stm, false );

		foreach( $values as $obj ) {
			DBObjectCache::store( $value, $obj );
		}
		$obj = array_shift( $values );
			
		return( $obj );
	}


	/**
	 * delete object
	 *
	 * @param DBOBject $obj
	 */
	function deleteObject( $obj )
	{
		$stm = new SQLStatementDelete($obj);
		$stm->setExpression($obj->get_condition());
		
		$result = new DBResultContainer();
		$this->_connection->executeStatement( $stm , $result);
	}

	function getCount( $cond =null )
	{
		$proto = new DBObjectAdapter_count($this->def->table_name());
		$stm = new SQLStatementSelect($proto );
		$stm->addColumn( SQLFunction::count($this->def->getPrimaryKeyTag(), "count"));
		if ( is_string( $stm) ) {
			Diagnostics::warning( "Do not use obsolete conditions" );
			$stm->setExpression(new ExprRaw($cond));
		}
		else if ( is_object($cond) || !is_null($cond)) {
			$stm->setExpression($cond);
		}

		$generator = $this->_connection->getGenerator();
		
		$result = $stm->createResultContainer ( false );
		$this->_connection->queryStatement( $stm, $result );
		$values = $result->getResult();
		$row = array_shift( $values);
		return( $row->count );
	}

	function proto()
	{
		return( $this->def );
	}
};

