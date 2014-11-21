<?php

define( 'DBParamType_integer', 'integer');
define( 'DBParamType_string', 'string');
define( 'DBParamType_lob', 'blob');
define( 'DBParamType_real', 'real');
define( 'DBParamType_bool', 'bool');
define( 'DBParamType_null', 'NULL');

/**
 * This class describes parameter for parametrized query
 *
 */
class DBParam
{
	/**
	 * Common value data type. Can be 'string', 'integer', 'blob'
	 *
	 * @var string
	 * @see DBParamType_integer
	 * @see DBParamType_string
	 * @see DBParamType_lob
	 */
	var $type;
	
	/**
	 * Paremeter name. Shoul be same as in parametrized Query.
	 *
	 * @var string
	 */
	var $name;
	
	/**
	 * Value which will be transmitted to query.
	 *
	 * @var mixed
	 */
	var $value;
	
	
	/**
	 * Initializes new instance of DBParam
	 *
	 * @param string $name  parameter name, must be same as in placeholder.
	 * @param string $type  common database type for parameter, can be 'string', 'integer', 'lob'
	 * @param mixed $value
	 * @return DBParam
	 */
	function DBParam( $name, $type, $value )
	{
		$this->name = $name;
		$this->value = $value;
		$this->type = $type;	
	}
}
