<?php
/******************************************************************************
 Copyright (c) 2005 by Alexei V. Vasilyev.  All Rights Reserved.                         
 -----------------------------------------------------------------------------
 Module     : RElation Adapter whithout link for *-1 relations
 File       : SelfRelationAdapter.php
 Author     : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description:

 sample:
 
 class ViewGroupRelation extends SelfRelationAdapter
 {
 	function getRelationInfo()
	{
		$obj = new DBRelationInfo( "ViewGroup", "view_group_id", "Source", "view_group_id");
		return( $obj );
	}
 }


******************************************************************************/

require_once( DIR_MAPPERS . "/RelationAdapter.php" );
require_once( DIR_MAPPERS . "/DBRelationInfo.php" );

class SelfRelationAdapter 
{
	function SelfRelationAdapter()
	{
		$this->relationInfo = $this->getRelationInfo();
	}
	function select( $ds, $objectID, $order="" )
	{
		$info = &$this->relationInfo;
		$values = array();

		$dba = $info->getMemberAdapter( $ds );
		$rc  = $dba->select( $values, $info->getSelectCondition( $objectID ) );
		return( $values );
	}

	function add( $ds, $pk, $childID )
	{
		$info = &$this->relationInfo;
		$memberObject = new $info->member_class;
		$query = DBQueryHelper::getUpdateExpression(
			$memberObject->table_name(),
			 DBQueryHelper::conditionEqual( $memberObject->primary_key(), $childID ),
			 array( DBQueryHelper::getSetExpression( $info->member_key, $pk ) )
			);
		
		$ds->queryCommand( $query );
	}

	function remove( $ds, $objectID, $memberID )
	{
		$info = &$this->relationInfo;

		$memberObject = new $info->member_class;
		$query = DBQueryHelper::getUpdateExpression(
			$memberObject->table_name(),
			 DBQueryHelper::conditionEqual( $memberObject->primary_key(), $memberID ),
			 array( DBQueryHelper::getSetExpression( $info->member_key, NULL ) )
			);
		
		$ds->queryCommand( $query );
	}

	function getMemberById( &$ds, $memberID )
	{
		$info = &$this->relationInfo;

		$dba = new DBObjectAdapter( $ds, new $info->member_class );
		return( $dba->getByPrimaryKey( $memberID ) );
	}
	function getRelationInfo( $objectID )
	{
		return( die( __CLASS__ . "::" . __METHOD__ . " id not defined" ) );
	}
}
?>