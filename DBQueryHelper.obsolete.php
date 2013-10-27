<?php
/******************************************************************************
 Copyright (c) 2005 by Alexei V. Vasilyev.  All Rights Reserved.                         
 -----------------------------------------------------------------------------
 Module     : Object Database model
 File       : CObjectDB.php
 Author     : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description:

   Query Helper providers construction SQL queries using metadata from DBObject

   if object does not consists meta-information then helper have using reflection for
   getting information about data
    
******************************************************************************/
require_once( dirname( __FILE__ ) . "/SQLValue.php" );


// Quote variable to make safe
function quote_smart($value)
{
   // Stripslashes
   if ( get_magic_quotes_gpc() ) {
	   if ( is_array($value) || is_object( $value )) {
		   Diagnostics::error( '$value should be simpletype , but got ' . gettype($value) );
		   return $value;
	   }
	   $value = stripslashes($value);
   }
   // Quote if not integer
   if (!is_numeric($value)) {
//       $value = "'" . mysql_real_escape_string($value) . "'";
   }
   return $value;
}


class DBQueryHelper1
{

	/** get 'member=value' pair (single use)
	 */
	function getMemberPair( $obj, $key )
	{
		$value = $obj->$key;

		$str = "";
		if ( !isset( $value ) ) {
			$str .= "$key=null";
		}
		else if( is_null( $value ) ){
			$str .= "$key=null";
		}
		else {
			$str .= sprintf( "%s=%s", $key, quote_smart( $value ) );
		}
		return( $str );
	}

	
	/** convert Object to 'member=value' pair list
	 */
	function getMemberPairs( $obj, $excludeList=NULL, $signUseClassVars=false )
	{
		$pairs = DBQueryHelper::getArrayOfMemberPairs( $obj, $excludeList, $signUseClassVars );

		$str = implode( ", ", $pairs );
		return( $str );
	}

	/** convert Object to 'member=value' pair list
	 */
	function getArrayOfMemberPairs( $obj, $excludeList=NULL, $signUseClassVars=false )
	{
		if ( !isset( $excludeList ) ) $excludeList = array();
		
		$pairs = array();

		if ( $signUseClassVars ) {
			$names = $obj->getColumnDefinition();
			if ( !$names ) {
				$names = array_keys( get_class_vars( get_class( $obj ) ) );
			}
		}
		else {
			$names = array_keys( get_object_vars( $obj ) );
		}
		
		foreach( $names as $key ) {
			$name = is_object( $key )  ? $key->name : $key;
			$value = $obj->$name;
			$type = is_object( $key )  ? $key->type : gettype( $value );

			if ( in_array( $name, $excludeList ) ) continue;
			$str = SQL::setValue( $name, $value, $type );
			$pairs[] = $str;
		}
		return( $pairs );
	}

	/** convert Object to 'member' list
	 */
	function getMembers( $obj, $excludeList=NULL, $prefix=""  )
	{
		if ( !isset( $excludeList ) ) $excludeList = array();
		
		$nvalue =0;
		$str = "";
		$keys = array();

		$members = get_class_vars( get_class( $obj ) );
		foreach( $members as $key => $initval ) {
			if ( in_array( $key, $excludeList ) ) continue;
			$keys[] = $prefix . "`" . $key ."`";
		}
		$str = implode( ", ", $keys );
		return( $str );
	}

	function getCondition( $cond, $prefix = "WHERE" )
	{
		if( isset( $cond ) && $cond != "" ) return( " {$prefix} {$cond}" );
		return( "" );
	}
	function where( $cond )
	{
		if( isset( $cond ) && $cond != "" ) return( " WHERE {$cond}" );
		return( "" );
	}
	
	function getOrder( $cond )
	{
		if( isset( $cond ) && $cond != "" ) return( " ORDER BY $cond" );
		return( "" );
	}

	/** return query for Select from ObjectDB
	 */
	function getSelectQuery( $table, $obj, $cond = NULL, $order = NULL )
	{
		$qWhere = "";
		$qOrder = "";
		
		if( isset( $cond ) && $cond != "" ) $qWhere  = " WHERE $cond";
		if( isset( $order ) && $order != "" ) $qOrder = " ORDER BY $order";
		
		$query = "SELECT "
			. DBQueryHelper::getMembers( $obj )
			. " FROM $table"
			. $qWhere
			. $qOrder
			;
		
		return( $query );
	}

	/** copy all members
	 */
	function copyMembers( &$dest, $src)
	{
		foreach( $src as $key => $value ) {
			//if ( !isset( $dest->$key ) ) continue;
			$dest->$key = $value;
		}
		return( $dest );
	}
	function concatConditions( $conditions, $concatby = "AND" )
	{
		$query = "";
		if ( count( $conditions ) != 0 ) {
			$i= 0;
			foreach( $conditions as $cond ) {
				if ( $i >0 ) $query .= " " . $concatby ." ";
				$query .= "(". $cond .")";
				
				$i++;
			}
		}
		return( $query );
	}


	function conditionIn( $column, $ids_array )
	{
		$text = "";
		$pos = 0;
		foreach( $ids_array as $id ) {
			if ( $pos >0 ) $text .= ",";
			$text .= SQLValue::getValue( $id );
			$pos ++;
		}
		return( "`$column` IN ( $text )" );
	}
	function conditionEqual( $column, $value )
	{
		$text = SQLValue::getValue( $value );
		return( "`$column` = $text" );
	}

	function getUpdateExpression( $table, $condition, $values_pairs )
	{
		$values = implode( ",", $values_pairs );
		$query = "UPDATE {$table} SET {$values} ";
		if ( $condition != "" ) $query .= " WHERE {$condition} ";
		return( $query );
	}
	function getUpdateQueryFor( $obj )
	{
		if ( !$obj->isChanged() ) return ( FALSE );
		$table  = $obj->table_name();
		$values = DBQueryHelper::getValuesSet( $obj );
		$condition = DBQueryHelper::conditionPK( $obj );
		
		$query = "UPDATE {$table} SET {$values} WHERE {$condition} ";
		return( $query );
	}
	function getValuesSet( &$obj )
	{
		$setPairs =array();
		foreach( $obj->getUpdatedFields() as $name => $value ) {
			$setPairs[] = DBQueryHelper::getSetExpression( $name, $value );
		}
		$values = implode( ",", $setPairs );
		return( $values );
	}

	function conditionPK( &$obj )
	{
		$pk  = $this->primary_key();
		if ( !is_array( $pk ) ) $pk = array( $pk );

		$cond = array();
		foreach( $pk as $key ) {
			$cond[] = DBQueryHelper::conditionEqual( $key, $obj->$key );
		}
		$cond = DBQueryHelper::concatConditions( $cond, "AND" );
		
		return( $cond );
	}
	
	function getSetExpression( $column, $value )
	{
		$text = SQLValue::getValue( $value );
		return( "`$column` = $text" );
	}

	function setValue( $column, $value, $type = null )
	{
		$text = SQLValue::getValue( $value, $type );
		return( "`$column` = $text" );
	}

	function getInsertExpr( $table, $valuesArray )
	{
		$pairs = SQL::array2pairs( $valuesArray );
		return( "INSERT INTO `$table` SET $pairs" );
	}

	function array2pairs( $valuesArray )
	{
		$pairs = array();
		foreach( $valuesArray as $name => $value ) {
			$pairs[] = SQL::setValue( $name, $value );
		}

		$str = implode( ",", $pairs );
		return( $str );
	}
	
};

?>