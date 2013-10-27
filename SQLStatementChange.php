<?php
require_once( dirname( __FILE__ ) . "/DBQuery.php" );
require_once( dirname( __FILE__ ). "/DBDefaultResultContainer.php" );

/**
 * Base class for all chaching operations. Do not use directly
 *
 */
class SQLStatementChange extends SQLStatement
{
	/**  enable using user defined Primary key in insert/update operations
	 @var bool
	 */
	var $signEnablePK;
	
	function SQLStatementChange( $obj )
	{
		parent::SQLStatement( $obj );
	}

	/** private. return SQL values array  for changing
	 */
	function _getValues($generator)
	{
		return( implode( ",", $this->_getValuesArray($generator) ) );
	}
	/** private. return SQL names for changing
	 */
	function _getColumns($generator)
	{
		return( implode( ",", $this->_getColumnsArray($generator) ) );
	}
	
 	/** private. return SQL value pairs for changing
	 */
	function _getValuePairs($generator)
	{
		return( implode( ",", $this->_getValuePairsArray($generator) ) );
	}
	
	/** private. return values array for changing
	 */
	function _getValuesArray($generator)
	{
		$pk   = $this->primaryKeys();
		$defs = $this->columnDefs;
		$values = array();
		foreach( $defs as $def ) {
			if ( in_array( $def->name, $pk ) and !$this->signEnablePK) continue;
			if ( !$this->_isMemberChanged( $def->name ) ) continue;
			
			$values[] = SQLValue::getValue( $this->object->{$def->name}, $def->type, $generator);
		}
		return( $values );
	}

	/** private. return values array for changing
	 */
	function _getValuePairsArray($generator)
	{
		$sql = $generator->getDictionary();
		
		$values = $this->_getValuesArray($generator);
		$names = $this->_getColumnsArray($generator);
		
		$count = count( $values );
		
		$pairs = array();
		for( $i=0; $i < $count; ++$i )
		{
			$pairs[] = $names[$i] . $sql->sqlAssignValue . $values[$i];
		}
		return( $pairs );
	}
	
	/** private. return columns array for changing
	 */
	function _getColumnsArray($generator)
	{
		$pk   = $this->primaryKeys();
		$defs = $this->columnDefs;
		$items = array();
		foreach( $defs as $def ) {
			if ( in_array( $def->name, $pk ) &&  !$this->signEnablePK) continue;
			if ( !$this->_isMemberChanged( $def->name ) ) continue;
			
			$sqlName = new SQLName(null, $def->name);
			
			$items[] = $sqlName->generate( $generator);
		}
		return( $items);
	}
	
	/** public. genrarate SQL query
	 @param SQLGenerator $generator
	 */
	function generate($generator)
	{
		$sql = $generator->getDictionary();
		$tname = new SQLName($this->table, null);
		
		$parts = array();
		$parts[] = $this->sqlStatement;
		$parts[] = $tname->generate( $generator );
		$parts[] = $sql->sqlSet;
		$parts[] = $this->_getValuePairs($generator);
		
		return( implode( " ", $parts ) );
	}

	/**
	 * Generate parametrized query.
	 *
	 * @param SQLGenerator $generator
	 * @return DBQuery
	 */
	function generateQuery($generator)
	{
		if ( !$generator ) {
			Diagnostics::error("Invalid argument. 'generator' is null'" );
		}
		$params = array();
		$names  = array();
		$pholders = array();
		
		$pk   = $this->primaryKeys();
		$defs = $this->columnDefs;
		
		
		foreach( $defs as $def ) {
			if ( in_array( $def->name, $pk ) && $this->signEnablePK == false) continue;
			if ( !$this->_isMemberChanged( $def->name ) ) continue;
			
			$pholderName = $generator->generatePlaceHolderName($def->name);
			$value = $this->object->{$def->name};
			
			$sqlName = new SQLName(null, $def->name);
			$names[] = $sqlName->generate( $generator);
			
			$pholders[] = $pholderName ;
			
			$param = new DBParam(
			 	$pholderName, 
				SQLValue::getDbParamType($value, $def->type), 
				SQLValue::getDbParamValue($value, $def->type, $generator )
				);
			
			$params[] = $param;
		}
		$sql = $this->_generateParametrizedQuery($names, $pholders, $generator);
		
		return new DBQuery( $sql, $params);
	}

	/**
	 * Generate parametrizedquery for specifies names and placeholders.
	 * this mehod can be overriden in inherit class for customize query generation.
	 *
	 * @param array $names  sql names string array.
	 * @param array $pholders sql placeholders string array
	 * @param SQLGenerator $generator  SQL generator.
	 * @return string
	 * @access protected
	 */
	function _generateParametrizedQuery ($names, $pholders, $generator)
	{
		$sql = $generator->getDictionary();
		$tname = new SQLName($this->table, null);
		
		$parts = array();
		$parts[] = $this->sqlStatement;
		$parts[] = $tname->generate($generator);
		$parts[] = $sql->sqlSet;
		
		$count = count($names);
		$pairs = array();
		for ($pos = 0; $pos < $count; ++ $pos) {
			$pairs[] = $names[$pos] . $sql->sqlAssignValue . $pholders[$pos];
		}
		$parts[] = implode(',', $pairs);
		
		return (implode(' ', $parts));
	}
	
	
	/** protected. can be overried if need disable checking for "changed"
	 */
	function _isMemberChanged( $name )
	{
		return $this->object->isMemberChanged($name);
	}
	
	/**
	 * create default result container
	 *
	 * @param bool $signUseID  set true if need use primary keys as array indexes
	 * @return SQLStatementSelectResult  
	 */
	function createResultContainer()
	{
		return new DBDefaultResultContainer( new stdclass, false);
	}
	
	/**
	 *  Enable to change primary key.
	 * @param bool $sign  true is PK changing enabled.
	 */
	function enableChangePK($sign = true )
	{
		$this->signEnablePK = $sign;
	}
	
}
define( "CLASS_SQLStatementChange", get_class( new SQLStatementChange(new DBObjectMock())));

?>