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



/** class for makeing relations. dot not use It directly
 */
abstract class DBRelationAdapter
{
	/**
	 * @var IDataSource
	 */
	var $connection;

	function __construct(IDataSource $connection=NULL)
	{
		$this->connection = $connection;
	}

	/**
	 * @param \IDataSource $connection
	 * @return DBRelationAdapter
	 */
	public function setConnection($connection)
	{
		$this->connection = $connection;
		return $this;
	}


	/**
	 * @return SimpleStatementRunner
	 */
	public function database()
	{
		if ( !$this->connection) {
			throw new DatabaseException("Connection for RelationAdapter does not defined");
		}
		return new SimpleStatementRunner($this->connection);
	}



	private function _getValuesArray(&$obj)
	{
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
	function add( $objectID, $memberID)
	{
		$obj = $this->getObject( $objectID, $memberID );

		/* validate what entry exists */
		$stm = new SQLStatementSelect($obj);
		$stm->resetColumns();
		StmHelper::stmAddCount($stm);
		$stm->setExpression( new ExprAND( $this->_getValuesArray($obj)) );

		$obj = $this->database()->executeSelectOne($stm);
		$count = $obj->count;

		if ( $count == 0 ){
			$obj = $this->getObject( $objectID, $memberID );
			$this->database()->execute(new SQLStatementInsert($obj));
		}
	}

	/**
	 * Remove  link to member object
	 *
	 * @param integer $objectID  owner object primary key ID
	 * @param integer $memberID  member object primary ID
	 * @return array|\DBObject|int
	 * @internal param \DBDataSource $ds Data source
	 */
	function remove($objectID, $memberID)
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

		return $this->database()->execute($stm);
	}

	/**
	 * Add and remove links
	 *
	 * @param $objectId array|integer owner object ID
	 * @param $addedIds array|integer added child IDs
	 * @param $removedIds array|integer removed child IDs
	 */
	function addremove( $objectId, $addedIds, $removedIds)
	{
		foreach( $removedIds as $id ) {
			$this->remove($objectId, $id);
		}
		foreach( $addedIds as $id ) {
			$this->add($objectId, $id);
		}
	}

	/** select members by ID/IDs
	 * @param ds        - datasource
	 * @param string $order - sort order
	 * @throws DatabaseException
	 * @return array|\DBObject|int
	 */
	function select($objectID, $order = "")
	{
		$values = array();
		if ( is_object( $objectID)) {
			throw new DatabaseException("Expected number but object gived as objectID. Got: ".get_class( $objectID));
		}
		if ( count( $objectID ) == 0 ) {
			return $values;
		}

		$stm = $this->stmSelectChilds( $objectID, $order );
		return $this->database()->execute($stm);
	}

	/**
	 * GGets Statement for selecting child bojects.
	 *
	 * @param array|integer $parentIds  parent object IDs.
	 * @param string $order
	 * @return SQLStatementSelect
	 */
	function stmSelectChilds( $parentIds, $order=null )
	{
		if ( !is_array( $parentIds ) ) $parentIds = array( $parentIds );

		$link   = $this->getObject( $parentIds[0],0 );
		$data   = $this->getDataObject( $parentIds[0] );
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
				 new ExprIn( $keyDefs[0],$parentIds )
				 ) 
			);

		if ( $order ){
			$stm->addOrder( $order );
		}
		return( $stm );
	}

	/**
	 * Get member by ID.
	 *
	 * @param $memberID  integer  member PK id.
	 * @internal param \IDataSource $ds data source.
	 * @return DBOBject
	 */
	function getMemberById($memberID)
	{
		$stm = StmHelper::stmSelectByPrimaryKey($this->getMemberObject(0), $memberID);
		return( $this->database()->executeSelectOne($stm) ) ;
	}

	
	/** should be defined in inherits
	 */
	/**
	 * Method must returns instance (prototype) of link object assigned foreign keys
	 * @param integer $objectID  primary key ID of owner object
	 * @param integer $memberID  primary key ID of member object
	 * @return DBObject  created link object prototype
	 */
	protected abstract function getObject( $objectID, $memberID );

	/**
	 * Method must returns instance (prototype) of member object with assigned primary key ID
	 * @param integer $objectID  primary key ID
	 * @return DBObject  created object prtotype
	 */
	protected abstract  function getMemberObject( $memberID );

	/**
	 * Method must returns instance (prototype) of Owner object with assigned primary key ID
	 * @param integer $objectID  primary key ID
	 * @return DBObject  created object prtotype
	 */
	protected abstract function getDataObject( $objectID );

	/**
	 *  Gets names of foreign keys.
	 *
	 * @return array(string)  
	 */
	protected abstract function getForeignKeys();

}

