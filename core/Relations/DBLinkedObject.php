<?php
require_once( dirname( __FILE__ ) . "/DBHistory.php" );

/**
 * @property array|DBHistory _history
 */
abstract class DBLinkedObject extends DBObject
{
	function loadMembers( $ds, $name )
	{
		$ra = $this->getRelationAdapter( $name );
		$ra->setConnection($ds);
		if ( !$ra ) return;
		$this->{$name} = $ra->select($this->primary_key_value());
	}


	/** add member object
	 */
	function addMember( $name, &$obj )
	{
		if ( !array_key_exists( $name, $this  ) ) $this->$name = array();

		$index = uuid();
		if ( !array_key_exists( $name, $this ) ) {
			$this->$name = array();
		}

		$this->{$name}[ $index ] = $obj;

		$this->_history[] = new DBHistory( DBH_ADD, $name, $index );

		return( $index );
	}

	/** update member object
	 */
	function updateMember( $name, $index )
	{
		$signAlreadyUpdated = false;
		//forget about already updated  item
		if ( array_key_exists( "_history", $this ) ) {
			foreach( array_keys( $this->_history ) as $pos ) {
				if ( $this->_history[ $pos ]->index != $index ) continue;
				if ( $this->_history[ $pos ]->op == DBH_UPDATE ) {
					$signAlreadyUpdated = true;
				}
			}
		}
		if ( !$signAlreadyUpdated ) {
			$this->_history[] = new DBHistory( DBH_UPDATE, $name, $index );
		}

		return( $index );
	}

	function deleteMember( $name, $index )
	{
		$signNewlyAdded = false;
		//forget about newly created item
		if ( array_key_exists( "_history", $this ) ) {
			foreach( array_keys( $this->_history ) as $pos ) {
				if ( $this->_history[ $pos ]->index != $index ) continue;
				if ( $this->_history[ $pos ]->op == DBH_REMOVE ) continue;
				unset( $this->_history[ $pos ] );
				$signNewlyAdded = true;
			}
		}
		if ( !$signNewlyAdded ) {
			$this->_history[] = new DBHistory( DBH_REMOVE, $name, $index, $this->{$name}[ $index ] );
		}
		unset( $this->{$name}[ $index ] );
	}

	/** should returns relations adapter for $name
	 * @return DBRelationAdapter
	 */
	function getRelationAdapter( $name )
	{
		return( null );
	}

	function executeHistory( IDataSource &$ds )
	{
		$pk = $this->primary_key_value();
		
		//if object supports history
		if ( !array_key_exists( "_history", $this ) ) return;
		
		foreach( $this->_history as $info ) {
			$name  = $info->container;
			$index =  $info->index;
			
			switch( $info->op ) {
			case DBH_ADD:
				/** @var $obj DBLinkedObject */
				$obj = &$this->{$name}[ $index ];

				$ds->queryStatement(new SQLStatementInsert($obj) );

				//add relations
				$childID = $ds->lastID();
				$obj->{$obj->primary_key()} = $childID;

				/** @var $ra DBRelationAdapter */
				$ra = $this->getRelationAdapter( $name );
				$ra->setConnection($ds);
				$ra->add( $pk, $childID );

				//recursive execute history
				$obj->executeHistory( $ds );
				
				break;

			case DBH_UPDATE:
				$obj = &$this->{$name}[ $index ];

				if ( $obj->isChanged()) {
					$ds->queryStatement(new SQLStatementUpdate($obj));
				}

				//recursive execute history
				$obj->executeHistory( $ds );
				
				break;
			case DBH_REMOVE:
				//remove relations
				$ra = $this->getRelationAdapter( $name );
				$ra->setConnection($this);
				$ra->remove($pk, $index);

				//remove object
				$obj = &$info->deletedObject;
				if ( !$obj ) break;

				//recursive execute history before object
				$obj->executeHistory( $ds );

				$stm = new SQLStatementDelete($obj);
				$stm->setExpression($obj->get_condition());
				$ds->queryStatement($stm);

				unset( $info->deletedObject );
				break;
			case DBH_ADDLINK:
				//add relations
				$childID = $index;
				$ra = $this->getRelationAdapter( $name );
				$ra->setConnection($ds);
				$ra->add( $pk, $childID );
				break;

			case DBH_REMOVELINK:
				//remove relations
				$childID = $index;
				$ra = $this->getRelationAdapter( $name );
				$ra->setConnection($ds);
				$ra->remove($pk, $childID);
				break;
			}
		}
	}
		
}
?>