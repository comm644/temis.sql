<?php

/**
 * SQL:  AS  keyword.
 *
 *  example:   table.column AS alias
 */
class SQLAlias
{
	var $table;
	var $column;
	var $alias;
	
	function SQLAlias( $table, $column, $alias )
	{
		$this->table = $table;
		$this->column = $column;
		$this->alias = $alias;
	}

	function generate($generator)
	{
		$parts = array();
		$sql = $generator->getDictionary();

		if ( $this->table ) {
			$parts[] = SQLName::getName( $this->table, $generator);
		}
		if ( $this->column != null ) {
			$parts[] = $sql->sqlTablecolumnSeparator;
			$parts[] = SQLName::getName( $this->column, $generator);
		}
		if ( $this->alias ) {
			$parts[] = $sql->sqlAs;
			$parts[] = SQLName::getName( $this->alias, $generator );
		}
		return( implode( " ", $parts ) );
	}
	function generateAlias($generator)
	{
		if ( $this->alias ) $name = $this->alias;
		else if ( $this->column ) $name =  $this->column;
		else $name = $this->table;
		return( SQLName::getName( $name, $generator ) );
		
		
	}
}
