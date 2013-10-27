<?php

/**
 * SQL:  'ORDER BY'  construction
 *
 */
class SQLOrder
{
	var $column;
	var $ascending = null;

	/**
	 * Construct SQL ORDER operator.
	 *
	 * SQL order render code by next rules:
	 *  - if $column is string - value used as column name for default table.
	 *    And renders next code:  'ORDER BY defaultTable.column'
	 *  - if $column is SQLName  - value used as fully qualified SQL Name.
	 *  - if $column is DBColumnDefinition  - value used as fully qualified column name
	 *    and will be rendered as :  ORDER BY tableName.columnName
	 *
	 *
	 * @param string|SQLName|DBColumnDefinition  $column   column name
	 * @param bool  $ascending   ascending flag. Order ascending if true. Otherwise order sets as descending.
	 */
	function SQLOrder( $column, $ascending=null)
	{
		$this->set( $column, $ascending );
	}

	function set( $column, $ascending =null )
	{
		$this->column = $column;
		$this->ascending = $ascending;
	}

	function generate( $generator, $defaultTable=null )
	{
		$sql = new SQLDic();
		
		if ( is_null( $this->column )) return "";

		$column = $this->column; //shorten val
		
		$parts = array();
		if ( !is_object( $column) )  {
			$parts[] = SQLName::getNameFull( $defaultTable, $column, $generator );
		}
		else switch( get_class( $column ) ) {
		case FALSE:
			
			break;
		case CLASS_SQLName: 
			$parts[] = $column->generate($generator);
			break;
		case CLASS_DBColumnDefinition:
			$parts[] = SQLName::getNameFull( $column->getTableAlias(), $column->name, $generator );
		}
		
		if ( $this->ascending === true ) {
			$parts[] = $sql->sqlAscending;
		}
		else if ( $this->ascending === false ) {
			$parts[] = $sql->sqlDescending; 
		}
		
		return( implode( " ", $parts ) );
	}
}

?>