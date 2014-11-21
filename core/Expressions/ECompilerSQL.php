<?php

class ECompilerSQL
{
	/**
	 * SQL dictionary
	 *
	 * @var SQLDic
	 */
	var $sqlDic = null;

	
	/**
	 * SQL Geenrator instance
	 *
	 * @var SQLGenerator
	 */
	var $generator = null;
	
	
	/**
	 * Indicates wheter method compile() returns parametrized query.
	 * 
	 * @var bool
	 */
	var $isParametrized = false;

	/**
	 * Param index for constructing name
	 * 
	 * @var integer
	 */
	var $paramIndex = 0;

	/**
	 *  Array of contructed params
	 * @var arrat(DBParam)
	 */
	var $params = array();
	
	/**
	 * Initialize new copy of ECompilerSQL
	 *
	 * @param SQLGenerator $generator
	 * @return ECompilerSQL
	 */
	function ECompilerSQL($generator, $isParametrized=false)
	{
		$this->sqlDic = $generator->getDictionary();
		$this->generator = $generator;
		$this->isParametrized = $isParametrized;
	}

	/**
	 * Gets compiled parameters
	 * @return array(DBParam)
	 */
	public function getParameters() {
		return $this->params;
	}


	
	
	/**
	 * group expressions
	 *
	 * @param string $query
	 * @return string
	 */
	function sqlGroup( $query )
	{
		return "({$query})";
	}


	function createParamName()
	{
		$name = $this->generator->generatePlaceHolderName( 'p' . $this->paramIndex );
		$this->paramIndex++;
		return $name;
	}
	/**
	 * Generate SQL Value or placeholder
	 * @param SQLValue $expr
	 * @return string
	 */
	function compileValue( $expr )
	{
		if ( !$this->isParametrized ) {
			return( $expr->generate($this->generator) );
		}
		$name = $this->createParamName();

		$this->params[] = new DBParam($name,
			$expr->getDbParamType($expr->value, $expr->type),
			$expr->getDbParamValue($expr->value, $expr->type, $this->generator));
		return $name;
	}

	function compileLike( $name, $value )
	{
		if ( !$this->isParametrized ) {
			return $this->generator->generateLikeCondition( $name, $value );
		}
		$pholder= $this->createParamName();
		
		$this->params[] = new DBParam($pholder,
			SQLValue::getDbParamType($value ),
			$this->generator->generateSearchString($value));
		
		return "$name LIKE $pholder";
	}

	function compile( $expr, $type=null )
	{
		if ( !is_object( $expr ) ) {
			$expr = new SQLValue( $expr, $type );
		}
			

		switch( get_class( $expr ) ) {

		case CLASS_ExprLike:
			$pos = 0;
			$query = "";
			if ( !is_array( $expr->args ) ){
				$query .= $this->compileLike( $this->getName( $expr->name ), $expr->args );
			}
			else foreach( $expr->args as $arg ) {
				if ( $pos > 0 ) $query .= " {$this->sqlDic->sqlAnd} ";
				$query .= $this->compileLike( $this->getName( $expr->name ), $arg );
				$pos ++;
			}
			return( $this->sqlGroup($query ) );
				
		case CLASS_ExprLikeNoMask:
			if ( count ( $expr->args ) == 0 ) return( "" );
			$query = "";
			$pos = 0;
			if ( !is_array( $expr->args ) ){
				$query .= $this->compile( $expr->name, $expr->type );
				$query .= " {$expr->mode} ";
				$query .= $this->compile( $expr->args, $expr->type );
			}
			else foreach( $expr->args as $arg ) {
				if ( $pos > 0 ) $query .= " {$this->sqlDic->sqlAnd} ";
				$query .= $this->compile( $expr->name, $expr->type );
				$query .= " {$expr->mode} ";
				$query .= $this->compile( $arg, $expr->type );
				$pos ++;
			}
			return( $this->sqlGroup($query ) );
		
		case CLASS_ExprRaw:
			return $expr->raw;		
			
		case CLASS_SQLName:
			return( $expr->generate($this->generator) );

		case CLASS_SQLValue:
			return $this->compileValue($expr );
				
		case CLASS_DBColumnDefinition:
			$sqlName = new SQLName( $expr->getTableAlias(), $expr->getName() );
			return( $sqlName->generate($this->generator) );
			
		case CLASS_ExprNOT:
			$query = "";
			$query .= " {$expr->name} ";
			$query .= $this->sqlGroup( $this->compile( $expr->args, $expr->type ) );
			return $query;
			
		default:
			switch( get_parent_class( $expr ) ) {
			case CLASS_ExprSet:
				if ( count( $expr->args ) > 0 ) {
					$signTestNull = false;
					$parts = array();
					foreach( $expr->args as $arg ) {
						$text = $this->compile( $arg, $expr->type );
						if ( $text == "NULL" ) {
							$signTestNull = true;
							continue;
						}
						$parts[] = $text;
					}

					if ( count( $parts ) == 0 ) {
						if ( !$signTestNull ) return( "" ); //empty set

						//null only
						$query = $this->getName( $expr->name ) . " {$this->sqlDic->sqlIsNull}";
						return( $query );
					}
					//other values
						
					$query = "{$this->getName( $expr->name )} {$expr->mode} ";
					$query .= $this->sqlGroup(implode( ",", $parts ));
					
					if ( $signTestNull ) {
						$query .= " {$this->sqlDic->sqlAnd} ". $this->getName( $expr->name ) . " {$this->sqlDic->sqlIsNull}";
					}
					return( $query );
					break;
				}
				//empty set
				return( "" );

			case CLASS_ExprBool:
				$pos = 0;
				$query = "";

				if ( $expr->mode == "IS"){
					$query .= $this->getName( $expr->name )  . ' '. $expr->mode . ' NULL';
				}
				else if ( !is_array( $expr->args ) ){
					$query .= $this->getName( $expr->name )  . ' '. $expr->mode . ' ' . $this->compile( $expr->args, $expr->type );
				}
				else {
					$parts = array();

					if ( count( $expr->args ) > 1 ) {
						foreach( $expr->args as $arg ) {
							if ( $pos > 0 ) $parts[] = $this->sqlDic->sqlAnd;
							$parts[] =  $this->getName( $expr->name );
							$parts[] = $expr->mode;
							$parts[] = $this->compile( $arg, $expr->type );
							$pos++;
						}
					}
					else {
						$parts[] = array_shift( $expr->args );
					}
					$query .= implode( ' ', $parts );
				}
				return( $this->sqlGroup( $query ) );
				break;
				
			case CLASS_ExprDummy:
				if ( count ( $expr->args ) == 0 ) return( "" );
				$query = "";
				foreach( $expr->args as $arg ) {
					$query .= $this->compile( $arg );
				}
				return( $query );
				
				
			default:
				if ( !is_subclasS_of( $expr, CLASS_Expression) ) {
					Diagnostics::error("Is not expression subclass given. Got: " . get_class( $expr ));
				}
				if ( count ( $expr->args ) == 0 ) return( "" );
				$query = "";
				$pos = 0;
				foreach( $expr->args as $arg ) {
					if ( $pos > 0 ) $query .= " {$expr->mode} ";
					$query .= $this->compile( $arg, $expr->type );
					$pos ++;
				}
				return( $this->sqlGroup($query ) );
			}//end switch( parent class)
		}//end switch( class )
	}
	function getName( $name )
	{
		if ( is_object( $name ) ) {
			switch( get_class( $name ) ) {
			case CLASS_SQLFunction: return( $name->generate($this->generator) );
			case CLASS_SQLName: return( $name->generate($this->generator) );
			case CLASS_DBColumnDefinition:
				$name = new SQLName( $name->getTableAlias(), $name->getName() );
				return ( $name->generate($this->generator) );
			}
		}
		return( SQLName::getName( $name, $this->generator ) );
	}


	/**
	 * compile Expression
	 *
	 * @param Expr $expr  expression
	 * @param SQLGenerator $generator  SQL generator.
	 * @return string  compiled SQL query
	 */
	static function s_compile( &$expr, $generator )
	{
		$compiler = new ECompilerSQL($generator);
		return( $compiler->compile( $expr ) );
	}
}
