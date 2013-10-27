<?php

/**
 * This class provides column as result of calculatons.
 * You can use Expr* object as argument.
 */
class SQLColumnExpr 
{
	/**
	 *
	 * @var Expression
	 */
	var $expr;
	var $alias;
	
	function __construct($expr, $alias="") {
		$this->expr = $expr;
		$this->alias = $alias;
	}
	function getAlias()
	{
		return( $this->alias );
	}
	
	function getAliasOrName()
	{
		return $this->getAlias();
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
		
		$parts[] = ECompilerSQL::s_compile($this->expr, $generator);
		
		if ( $this->alias ) {
			$parts[] = $sql->sqlAs;
			$parts[] = $generator->generateName( $this->alias );
		}
		return( implode(" ", $parts ));
	}	
}
