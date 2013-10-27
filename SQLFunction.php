<?php
require_once( dirname( __FILE__ ) ."/SQLGenerator.php" );
require_once( dirname( __FILE__ ) ."/DBValueType.php" );

class SQLFunction
{
	var $name;
	var $args = array();
	var $alias=null;
	var $type = 'string';

	/**
	 * Create function definition
	 *
	 * @param string  $functionName
	 * @param DBColumnDefintion $column
	 * @param string $type  result type
	 * @param sting   $alias   column alias
	 * @return SQLFunction  created object
	 */
	static function _create1( $functionName, &$column, $type, $alias=null)
	{
		if ( is_null( $alias ) ){
			if ( is_string( $column ) ) $alias = $column;
			else $alias = null;
		}
		$obj = new SQLFunction;
		$obj->name = $functionName;
		$obj->args[] = $column;
		$obj->alias = $alias;

		return( $obj );
	}

	function getAlias()
	{
		if ( $this->alias == null ) return( $this->name );
		return( $this->alias );
	}
	
	function getAliasOrName()
	{
		return $this->getAlias();
	}
	
	/**
	 * create function 'count'
	 *
	 * @param DBColumnDefintion $column
	 * @param string $alias (null if not set)
	 * @return SQLFunction
	 */
	static function count( $column, $alias=null )
	{
		return( SQLFunction::_create1( "count", $column, DBValueType_integer, $alias ) );
	}
	static function max( $column, $alias=null )
	{
		return( SQLFunction::_create1( "max", $column, DBValueType_integer, $alias ) );
	}
	static function min( $column, $alias=null )
	{
		return( SQLFunction::_create1( "min", $column, DBValueType_integer, $alias ) );
	}
	static function sum( $column, $alias=null )
	{
		return( SQLFunction::_create1( "sum", $column, DBValueType_integer, $alias ) );
	}

	/**
	 * Generate SQL query.
	 *
	 * @param SQLGenerator $generator
	 * @param string $defaultTable
	 * @return string  SQL query 
	 */
	function generate( $generator, $defaultTable = null)
	{
		$sql = $generator->getDictionary();
		
		$parts = array();
		
		$parts[] = $this->name . $sql->sqlOpenFuncParams;
		foreach( $this->args as $arg ) {
			$parts[] = $generator->generateColumn( $arg, $defaultTable );
		}
		$parts[] = $sql->sqlCloseFuncParams;
		
		if ( $this->alias ) {
			$parts[] = $sql->sqlAs;
			$parts[] = $generator->generateName( $this->alias );
		}
		return( implode(" ", $parts ));
	}
}
define( "CLASS_SQLFunction", get_class( new SQLFunction ) );
?>