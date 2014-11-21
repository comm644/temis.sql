<?php
/******************************************************************************
 Copyright (c) 2005 by Alexei V. Vasilyev.  All Rights Reserved.                         
 -----------------------------------------------------------------------------
 Module     : The object provides information about relations.
 File       : DBRelationInfo.php
 Author     : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description:

   1. the container have primary key
   2. the member have foreign key refrences to  container

   
 
******************************************************************************/

class DBRelationInfo
{
	var $object_class = "";
	var $object_key = "";
	var $member_class = "";
	var $member_key = "";

	function DBRelationInfo( $oclass="", $okey="", $mclass="", $mkey="" )
	{
		$this->object_class = $oclass; // object (Container) class name
		$this->object_key   = $okey;   // primary key of Object (Container)
		$this->member_class = $mclass; // name of member class 
		$this->member_key   = $mkey;   // foreign key from member class
	}
	function getSelectCondition( $objectID )
	{
		return DBQueryHelper::conditionEqual( $this->member_key, $objectID );
	}
}

?>