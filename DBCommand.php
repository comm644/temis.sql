<?php
/******************************************************************************
 Copyright (c) 2005 by Alexei V. Vasilyev.  All Rights Reserved.                         
 -----------------------------------------------------------------------------
 Module     : Database command executor
 File       : DBCommand.php
 Author     : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description:
******************************************************************************/

class DBCommand
{
	var $db;

	function DBCommand( &$db )
	{
		$this->setConnection( $db );
	}
	
	function setConnection( &$db )
	{
		$this->db = &$db;
	}
	function getconnection()
	{
		return( $this->db );
	}
};


?>