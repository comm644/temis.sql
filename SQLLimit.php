<?php
/******************************************************************************
 Copyright (c) 2007 by Alexei V. Vasilyev.  All Rights Reserved.                         
 -----------------------------------------------------------------------------
 Module     : keyword LIMIT
 File       : SQLLimit.php
 Author     : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description:
******************************************************************************/

require_once( dirname( __FILE__ ) . "/SQLParam.php" );

/**
 * SQL: LIMIT keyword
 *
 */
class SQLLimit extends SQLParam
{
	function generate()
	{
		if ( !$this->value ) return "";
		$dic = new SQLDic;
		return $this->_generate( $dic->sqlLimit );
	}
}

?>