<?php


class SQLGenerator
{
	/**
	 * SQL dictionary
	 *
	 * @var SQLDic
	 */
	private $_dictionary;
	
	/** construct generator
	 @param SQLDictionary $dictionary
	 */
	function SQLGenerator( $dictionary = null)
	{
		if ( $dictionary == null ) $dictionary = new SQLDic();
		
		$this->_dictionary = $dictionary;
	}
	
	/**
	 * Get SQL dictionary.
	 *
	 * @return SQLDic
	 */
	function getDictionary()
	{
		return $this->_dictionary;
	}

	/** generate sql column defintion from mixed variable
	 * 
	 * @param string|SQLName|DBColumnDefinition|SQLFunction  $column
	 * @param string  $defaultTable  default name.
	 */
	function generateColumn( $column, $defaultTable=null )
	{
		if (!is_object($column)) {
			return SQLName::getNameFull( $defaultTable, $column, $this );
		}
		else switch( get_class( $column ) ) {
		case FALSE:
			return SQLName::getNameFull( $defaultTable, $column, $this );

		case CLASS_SQLName: 
			return $column->generate();

		case CLASS_DBColumnDefinition:
			return SQLName::getNameFull( $column->getTableAlias(), $column->getName(), $this );

		case CLASS_SQLFunction:
			return $column->generate($this);
			
		default:
			return $column->generate($this);
		}
		return( "" );
	}

	function generateName( $column )
	{
		if ( !is_object( $column ) ) {
			return SQLName::getName( $column, $this );
		}
		switch( get_class( $column ) ) {
		case FALSE:
			return SQLName::getName( $column, $this );

		case CLASS_SQLName: 
			return $column->generate($this);

		case CLASS_DBColumnDefinition:
			$obj = new SQLName(null,$column->name  );
			
			return SQLName::getName( $column->name );

		case CLASS_SQLFunction:
			return $column->generate($this);

		}
		return( "" );
	}
	
	function generateValueasBLOB( &$value )
	{
		//mysql
		return( "0x" . bin2hex( $value ) );
	}

	/**
	 * Generate place holder name.
	 *
	 * @param string $name  base name
	 * @return string  real place holder name
	 */
	function generatePlaceHolderName($name)
	{
		return ':'.$name;
	}


	/**
	 * Generate statement SQL query 
	 *
	 */
	function generate( $stm )
	{
		return $stm->generate( $this);
	}
	
	/**
	 * Generate parametrized query. 
	 * Use this method for insert and update opration bacause BLOBs via parameter have better transfer speed.
	 *
	 * @param SQLStatementChange $stm
	 * @return DBQuery  rendreed query object
	 */
	function generateParametrizedQuery( $stm)
	{
		return $stm->generateQuery($this);
	}

    /**
     * Generate SQL DateTime value
     * @param integer $value  unix time
     * @return string  SQL92 Date time
     */
	function generateDateTime( $value )
	{
		return( strftime( "%Y-%m-%d %H:%M:%S", $value ) );
	}

    /**
     * Generate SQL Date value
     * @param integer $value  unix time
     * @return string  SQL92 Date time
     */
	function generateDate( $value )
	{
		return( strftime( "%Y-%m-%d", $value ) );
	}

	/**
	 * Escape string for using in non-compileable SQL requestes.
	 * for PDO implement as dummy method.
	 *
	 * @param string $value
	 * @return string   escaped string.
	 */
	function escapeString( $value )
	{
        die("method " . __METHOD__ . " is not implemented" . Diagnostics::trace());
	}

	/**
	 * Generarte LIKE condition
	 *
	 * @param sttring $name  column name
	 * @param string $value  value string without 'any string' instructions
	 * @return string constructed expression
	 */
	function generateLikeCondition( $name, $value )
	{
		$value = $this->escapeString( $value );
		return( "{$name} LIKE '%{$value}%'" );
	}

	/**
	 * Generate search string. escape string if need and wrap to '%' or
	 * another according to database
	 * 
	 * @param string $value
	 * @return string
	 */
	function generateSearchString( $value )
	{
		$value = $this->escapeString( $value );
		return( "%".$value."%" );
	}

	/**
	 * static method. Generate statement. 
	 *
	 */
	function s_generate( $stm )
	{
		$generator = new SQLGenerator();
		return $generator->generateStatement($stm);
	}

	/** some databases does not have support LIMIT for UPDATE Statement.
	 */
	function updateLimitEnabled()
	{
		return true;
	}
}
?>