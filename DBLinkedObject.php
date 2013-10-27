<?php
require_once( dirname( __FILE__ ) . "/DBObject.php" );
require_once( dirname( __FILE__ ) . "/DBHistory.php" );

class DBLinkedObject extends DBObject
{

	function addLink( &$ds, $name, $childID )
	{
		//load object
		$ra = $this->getRelationAdapter( $name );
		$this->{$name}[ $childID ] = $ra->getMemberById( $ds, $childID );

		//add info
		$this->_history[] = new DBHistory( DBH_ADDLINK, $name, $childID );
	}
	function deleteLink( $name, $childID )
	{
		$signNewlyAdded = false;
		
		//remove object
		unset( $this->{$name}[ $childID ] );

		if ( array_key_exists( "_history", $this ) ) {
			//forget about newly created item
			foreach( array_keys( $this->_history ) as $pos ) {
				if ( $this->_history[ $pos ]->index != $childID ) continue;
				if ( $this->_history[ $pos ]->op == DBH_REMOVELINK ) continue;
				unset( $this->_history[ $pos ] );
				$signNewlyAdded = true;
			}
		}
		if ( !$signNewlyAdded ) {
			$this->_history[] = new DBHistory( DBH_REMOVELINK, $name, $childID );
		}
	}

	function loadMembers( $ds, $name )
	{
		$ra = $this->getRelationAdapter( $name );
		if ( !$ra ) return;
		$this->{$name} = $ra->select( $ds, $this->primary_key_value() );
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
	 */
	function getRelationAdapter( $name )
	{
		return( null );
	}

	function executeHistory( &$ds )
	{
		$pk = $this->primary_key_value();
		
		//if object supports history
		if ( !array_key_exists( "_history", $this ) ) return;
		
		foreach( $this->_history as $info ) {
			$name  = $info->container;
			$index =  $info->index;
			
			switch( $info->op ) {
			case DBH_ADD:
				$obj = &$this->{$name}[ $index ];
				$dba = new DBObjectAdapter( $ds, $obj );
				$dba->insert( $obj );

				//add relations
				$childID = $ds->lastID();
				$obj->{$obj->primary_key()} = $childID;

				/** @var $ra DBRelationAdapter */
				$ra = $this->getRelationAdapter( $name );
				$ra->add( $ds, $pk, $childID );

				//recursive execute history
				$obj->executeHistory( $ds );
				
				break;

			case DBH_UPDATE:
				$obj = &$this->{$name}[ $index ];
				$dba = new DBObjectAdapter( $ds, $obj );
				$dba->update( $obj );

				//recursive execute history
				$obj->executeHistory( $ds );
				
				break;
			case DBH_REMOVE:
				//remove relations
				$ra = $this->getRelationAdapter( $name );
				$ra->remove( $ds, $pk, $index );

				//remove object
				$obj = &$info->deletedObject;
				if ( !$obj ) break;

				//recursive execute history before object
				$obj->executeHistory( $ds );
				
				$dba = new DBObjectAdapter( $ds, $obj );
				$dba->delete( $obj->get_condition() );
				unset( $info->deletedObject );
				break;
			case DBH_ADDLINK:
				//add relations
				$childID = $index;
				$ra = $this->getRelationAdapter( $name );
				$ra->add( $ds, $pk, $childID );
				break;

			case DBH_REMOVELINK:
				//remove relations
				$childID = $index;
				$ra = $this->getRelationAdapter( $name );
				$ra->remove( $ds, $pk, $childID );
				break;
			}
		}
	}
		
}
?>