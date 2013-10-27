<?php
/******************************************************************************
 Copyright (c) 2007 by Alexei V. Vasilyev.  All Rights Reserved.                         
 -----------------------------------------------------------------------------
 Module     : keyword OFFSET
 File       : SQLOffset.php
 Author     : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description:
******************************************************************************/
require_once( dirname( __FILE__ ) . "/SQLParam.php" );

/**
 * SQL:  'OFFSET'  construction
 *
 */
class SQLOffset extends SQLParam
{
	function generate()
	{
		if ( !$this->value ) return "";
		
		$dic = new SQLDic;
		return $this->_generate( $dic->sqlOffset );
	}
}

?>