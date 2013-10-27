<?php

/** multiargument expression
 */
class Expression
{
	var $mode = ""; //join mode
	var $args;      //array of arguments
	var $type;      //type of values (need for type conversion)
	
	function Expression( $mode, $args )
	{
		$this->mode = $mode;
		$this->args = $args;
	}
}
define( "CLASS_Expression", get_class( new Expression("=", array()))) ;

/** bool expression 
 */
class ExprBool extends Expression
{
	var $name ="";
	
	function ExprBool( $mode, $name, $values )
	{
		parent::Expression( $mode, $values );
		
		$this->name = $name;

		if ( is_object ($name) && get_class( $name ) == CLASS_DBColumnDefinition ) {
			$this->type = $name->type;
		}
	}
}
define( "CLASS_ExprBool", get_class( new ExprBool(null, null, null ) ) );

class ExprNOT extends Expression
{
	function ExprNOT( $expr )
	{
		$this->name =" NOT ";
		parent::Expression( "", $expr );
	}
}
define("CLASS_ExprNOT", get_class(new ExprNOT(null)));

/** set expression
 */
class ExprSet extends Expression
{
}
define( "CLASS_ExprSet", get_class( new ExprSet(null, null) ) );

class ExprDummy extends Expression
{
}
define( "CLASS_ExprDummy", get_class( new ExprDummy(null, null) ) );

class ExprLogic extends Expression
{
}

// logic 
class ExprOR extends ExprLogic
{
	function ExprOR( $args )
	{
		if ( !is_array( $args )  ) $args = func_get_args();
		parent::Expression( "OR", $args );
	}
}
define( "CLASS_ExprOR", get_class( new ExprOR(null, null) ) );


class ExprAND extends ExprLogic
{
	function ExprAND( $args )
	{
		if ( !is_array( $args )  ) $args = func_get_args();
		$resampled = array();
		foreach( $args as $arg ) {
			if ( !$arg ) {
				continue;
			}
			$resampled[] = $arg;
		}
		parent::Expression( "AND", $resampled );
	}
}
define( "CLASS_ExprAND", get_class( new ExprAND(null, null) ) );

// set
class ExprIN extends ExprSet
{
	function ExprIn( $name, $values )
	{
		parent::Expression( "IN", $values );
		$this->name = $name;
	}
}
define( "CLASS_ExprIN", get_class( new ExprIN(null, null) ) );

// bool
class ExprEQ extends ExprBool
{
	function ExprEQ( $name, $values )
	{
		if ( is_array( $values ) ) {
			if ( count( $values ) == 1 && is_null( reset( $values ) ) ) {
				parent::ExprBool( "IS", $name, reset( $values ) );
			}
			else {
				parent::ExprBool( "=", $name, $values );
			}
		}
		else if ( is_null( $values ) ) {
			parent::ExprBool( "IS", $name, $values );
		}
		else {
			parent::ExprBool( "=", $name, $values );
		}
	}
}
define( "CLASS_ExprEQ", get_class( new ExprEQ(null, null) ) );

class ExprNEQ extends ExprBool
{
	function ExprNEQ( $name, $values )
	{
		if ( is_array( $values ) ) {
			if ( count( $values ) == 1 && is_null( reset( $values ) ) ) {
				parent::ExprBool( "IS NOT", $name, reset( $values ) );
			}
			else {
				parent::ExprBool( "!=", $name, $values );
			}
		}
		else if ( is_null( $values ) ) {
			parent::ExprBool( "IS NOT", $name, $values );
		}
		else {
			parent::ExprBool( "!=", $name, $values );
		}
	}
}

class ExprLike extends ExprBool
{
	function ExprLike( $name, $values )
	{
		parent::ExprBool( "LIKE", $name, $values );
	}
}
define( "CLASS_ExprLike", get_class( new ExprLike(null, null) ) );

class ExprLikeNoMask extends ExprLike
{
	function ExprLikeNoMask( $name, $values )
	{
		parent::ExprLike( $name, $values );
	}
}
define( "CLASS_ExprLikeNoMask", get_class( new ExprLikeNoMask(null, null) ) );

class ExprLT extends ExprBool
{
	function ExprLT( $name, $values )
	{
		parent::ExprBool( "<", $name, $values );
	}
}
class ExprLTE extends ExprBool
{
	function ExprLTE( $name, $values )
	{
		parent::ExprBool( "<=", $name, $values );
	}
}
class ExprGT extends ExprBool
{
	function ExprGT( $name, $values )
	{
		parent::ExprBool( ">", $name, $values );
	}
}
class ExprGTE extends ExprBool
{
	function ExprGTE( $name, $values )
	{
		parent::ExprBool( ">=", $name, $values );
	}
}


/** advanced expression
 */
class ExprRange extends ExprDummy
{
	function ExprRange( $name, $min, $max )
	{
		$this->name = $name;
		$this->args = array( 
			new ExprAND( array( new ExprGTE( $name, $min ), new ExprLTE( $name, $max ) ) ) );
	}
}


/**
 * Raw condition . only for support old style queries in DBObjectAdapter
 *
 */
class ExprRaw extends Expression
{
	/**
	 * raw condition
	 *
	 * @var string
	 */
	var $raw;
	
	/**
	 * cosntruct expresson
	 *
	 * @param string $rawCondition
	 * @return ExprRaw
	 */
	function ExprRaw( $rawCondition)
	{
		$this->raw = $rawCondition;
	}
}
define( "CLASS_ExprRaw", get_class( new ExprRaw(null) ) );


/**
 * This class provides mathematics for expressions.
 */
class ExprMath extends Expression
{
	function __construct($opcode, $args, $args) 
    {
	    $args = func_get_args();
		array_shift($args);
		parent::Expression($opcode, $args);
	}
}

define( "CLASS_ExprMath", get_class( new ExprMath(null, null, null) ) );
