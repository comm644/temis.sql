<?php
/******************************************************************************
 Copyright (c) 2005 by Alexei V. Vasilyev.  All Rights Reserved.                         
 -----------------------------------------------------------------------------
 Module     : Abstract adapter for suppoerting relations for good formed databases
 File       : RelationAdapter.php
 Author     : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description:

   TODO:  make template using for 'SelectMembers' query
******************************************************************************/
require_once( dirname( __FILE__ ) . "/SQLValue.php" );


/** class for makeing relations. dot not use It directly
 */
class DBRelationAdapter
{
	function _getValuesArray(&$obj)
	{
		$stm  = new SQLStatement( $obj );

		$names = $this->getForeignKeys();
		$values = array();
		foreach( $names as $name ) {
			$tagname = 'tag_' . $name;
			$def = $obj->$tagname();
			$values[] = new ExprEQ( $def, $obj->{$def->name});
		}
		return( $values );
	}
	

	/**
	 * Add link to member object
	 *
	 * @param DBDataSource $ds   Data source
	 * @param integer $objectID  owner object primary key ID
	 * @param integer $memberID  member object primary ID
	 */
	function add( $ds, $objectID, $memberID)
	{
		$obj = $this->getObject( $objectID, $memberID );

		/* validate what entry exists */
		$cond = new ExprAND( $this->_getValuesArray($obj));

		$dba = new DBObjectAdapter( $ds, $obj );
		$count = $dba->getCount( $cond );
		if ( $count == 0 ){
			$dba->insert( $obj );
		}
	}

	/**
	 * Remove  link to member object
	 *
	 * @param DBDataSource $ds   Data source
	 * @param integer $objectID  owner object primary key ID
	 * @param integer $memberID  member object primary ID
	 */
	function remove( $ds, $objectID, $memberID )
	{
		$obj = $this->getObject( $objectID, $memberID );

		$pairs = array();
		foreach( $this->getForeignKeys() as $keyname ) {
			$keytag = 'key_' . $keyname;
			$key = $obj->$keytag();
			$pairs[] = new ExprEQ( $key->ownerTag, $obj->{$key->ownerTag->name} );
		}
		$expr = new ExprAND( $pairs );
		
		$link   = $this->getObject( 0,0 );
		$stm  = new SQLStatementDelete( $link );
		$stm->setExpression( $expr );

		$res = $stm->createResultContainer();
		$ds->queryStatement( $stm, $res );
	}

	/** select members by ID/IDs

	@param objectID  - objet ID  or array of object ID
	@param ds        - datasource
	@param order     - sort order
	 */
	function select( $ds, $objectID, $order="" )
	{
		$values = array();
		if ( is_object( $objectID)) {
			Diagnostics::error("Expected number but object gived as objectID. Got: ".get_clasS( $objectID));
		}
		if ( count( $objectID ) == 0 ) return $values;
		

		$stm = $this->getSelectQuery( $objectID, $order );
		$query = $stm->generate($ds->getGenerator());

		$res = $stm->createResultContainer();
		$ds->queryStatement( $stm, $res );
		return $res->getResult();
	}

	/**
	 * GGets Statement for selecting child bojects.
	 *
	 * @param array|integer $objectIDS
	 * @param string $order
	 * @return SQLStatementSelect
	 */
	function getSelectQuery( $objectIDS, $order=null )
	{
		if ( !is_array( $objectIDS ) ) $objectIDS = array( $objectIDS );

		$link   = $this->getObject( $objectIDS[0],0 );
		$data   = $this->getDataObject( $objectIDS[0] );
		$member = $this->getMemberObject( 0 );

		$defs = $link->getColumnDefinition();
		$keyDefs = array();
		$keys = $this->getForeignKeys();
		foreach( $keys as $key ) {
			$keyDefs[] = $defs[ $key ];
		}

		
		$linkTable   = $link->table_name();
		$memberTable = $member->table_name();
		
		$dataKey     = $data->getPrimaryKeyTag();
		$memberKey   = $member->getPrimaryKeyTag();
		
		$stm = new SQLStatementSelect( $member );
		$stm->addTables( $linkTable );
		$stm->setExpression(
			new ExprAND(
				new EXprEQ( $keyDefs[1], $memberKey ),
				 new ExprIn( $keyDefs[0],$objectIDS ) 
				 ) 
			);

		if ( $order ){
			$stm->addOrder( $order );
		}
		return( $stm );
	}

	function getMemberById( &$ds, $memberID )
	{
		$dba = new DBObjectAdapter( $ds, $this->getMemberObject( 0 ) );
		return( $dba->getByPrimaryKey( $memberID ) );
	}

	
	/** should be defined in inherits
	 */
	/**
	 * Method must returns instance (prototype) of link object assigned foreign keys
	 * @param integer $objectID  primary key ID of owner object
	 * @param integer $memberID  primary key ID of member object
	 * @return DBObject  created link object prototype
	 */
	protected function getObject( $objectID, $memberID )
	{
		return( die( __CLASS__ . "::" . __METHOD__ . " id not defined" ) );
	}

	/**
	 * Method must returns instance (prototype) of member object with assigned primary key ID
	 * @param integer $objectID  primary key ID
	 * @return DBObject  created object prtotype
	 */
	protected function getMemberObject( $memberID )
	{
		return( die( __CLASS__ . "::" . __METHOD__ . " id not defined" ) );
	}

	/**
	 * Method must returns instance (prototype) of Owner object with assigned primary key ID
	 * @param integer $objectID  primary key ID
	 * @return DBObject  created object prtotype
	 */
	protected function getDataObject( $objectID )
	{
		return( die( __CLASS__ . "::" . __METHOD__ . " id not defined" ) );
	}

	/**
	 *  Gets names of foreign keys.
	 *
	 * @return array(string)  
	 */
	protected function getForeignKeys()
	{
		return( die( __CLASS__ . "::" . __METHOD__ . " id not defined" ) );
	}

}

?>