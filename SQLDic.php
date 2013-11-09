<?php
/******************************************************************************
 Copyright (c) 2007 by Alexei V. Vasilyev.  All Rights Reserved.                         
 -----------------------------------------------------------------------------
 Module     : SQL language dictionary
 File       : SQLDic.php
 Author     : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description:

  descibe SQL language keywords.
  this object should be provided by DataSource
  
******************************************************************************/

class SQLDic	
{
	var $sqlSelect="SELECT";
	var $sqlFrom="FROM";
	var $sqlWhere="WHERE";
	var $sqlLimit="LIMIT";
	var $sqlOffset="OFFSET";
	
	var $sqlOrder="ORDER BY";
	var $sqlGroup="GROUP BY";
	var $sqlAscending = "ASC";
	var $sqlDescending = "DESC";

	var $sqlAnd = "AND";
	var $sqlLike = "LIKE";
	var $sqlOr   = "OR";
	var $sqlIn   = "IN";
	var $sqlAs   = "AS";

	var $sqlLeftJoin="\nLEFT JOIN";
	var $sqlOn="ON";

	var $sqlOpenName="`";
	var $sqlCloseName="`";
	var $sqlTableColumnSeparator = '.';

	var $sqlStringOpen = '"';
	var $sqlStringClose = '"';

	var $sqlInsert ="INSERT INTO";
	var $sqlValues = "VALUES";
	
	var $sqlSet="SET";

	var $sqlLikeMaskAny = "%";
	var $sqlIsNull = "IS NULL";
	
	var $sqlOpenFuncParams = "(";
	var $sqlCloseFuncParams = ")";
	
	var $sqlAssignValue = '=';
}


?>