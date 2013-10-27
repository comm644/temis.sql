<?php

class SQLName
{
	var $table;
	var $column;
	
	function SQLName( $table, $column )
	{
		$this->table = $table;
		$this->column = $column;
	}

	/** returns normalized escaped NAME for SQL

	sample:
	in:  table.name
	out: `table`.`name`
	 * @param SQLGenerator $generator
	 */
	static function getName( $name, $generator )
	{
		$sql = $generator->getDictionary();
		
		$pos = strpos($name,  $sql->sqlTableColumnSeparator);
		if ( $pos !== FALSE ) {
			$table  = substr( $name, 0, $pos );
			$column = substr( $name, $pos+1 );
			
			$obj = new SQLName($table, $column);
		}
		else {
			$obj = new SQLName(null, $name);
		
		}
		return $obj->generate($generator);
	}

	static function getNameFull( $table, $column, $generator )
	{

		if ( !is_object($generator) ) {
			Diagnostics::error( "Invalid argument: 'generator'. Value must not be null");
		}
		
		$sql = $generator->getDictionary();
		
		if ( is_null( $table ) ) return SQLName::wrap( $column, $sql );
		
		return( SQLName::wrap( $table, $sql ) . $sql->sqlTableColumnSeparator . SQLName::wrap( $column, $sql ) );
	}
	
	static function wrap( $name, $sql )
	{
		$parts = array();
		
		//filter wrong names.
		if ( $sql->sqlOpenName  ) {
			if ( strpos( $name, $sql->sqlOpenName ) !== FALSE ) {
				Diagnostics::error('Invalid argument: $name' . "\nSQL Name cannot be given with quotes, because ADO database independed.");
			}
		}
		
		if ( $sql->sqlOpenName  ) {
			if ( strpos( $name, $sql->sqlOpenName ) !== FALSE ) {
				return( $name );
			}
			
			$parts[] = $sql->sqlOpenName ;
		}
		$parts[] = $name ;
		
		if ( $sql->sqlCloseName  ) {
			$parts[] = $sql->sqlCloseName ;
		}
		
		
		if ( $sql->sqlOpenName != '' ) {
			if ( strpos( $name, $sql->sqlOpenName ) !== FALSE ) return( $name );
			$name = $sql->sqlOpenName . $name . $sql->sqlCloseName;
		}
		
		return( $name );
	}

	/**
	 * Generate SQL query.
	 *
	 * @param SQLGenerator $generator
	 * @param string $defaultTable
	 * @return string  SQL query 
	 */
	function generate($generator)
	{
		if ( !$generator ) {
			Diagnostics::error("Invalid argument 'generator' value must not be null.");
		}
		$sql = $generator->getDictionary();
		
		$parts = array();
		
		if ( $this->table != null) {
			$parts[] = $this->wrap( $this->table, $sql );

			if ( $this->column != null ) {
				$parts[] = $sql->sqlTableColumnSeparator;
			}
		}
		if ( $this->column != null ) {
			$parts[] = SQLName::wrap( $this->column, $sql );
		}
		return( implode('', $parts) );
	}
}


define( "CLASS_SQLName", get_class( new SQLName(null,null ) ) );
?>