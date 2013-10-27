<?php

/* * ****************************************************************************
  Copyright (c) 2005 by Alexei V. Vasilyev.  All Rights Reserved.
  -----------------------------------------------------------------------------
  Module     : SQL Value converter
  File       : SQLValue.php
  Author     : Alexei V. Vasilyev
  -----------------------------------------------------------------------------
  Description:

  require: DataSource global defined class
 * **************************************************************************** */

class SQLValue
{

	/**
	 * value
	 *
	 * @var mixed
	 */
	var $value;

	/**
	 * internals SQL type for values
	 *
	 * @var string
	 */
	var $type;

	/**
	 * construct SQL value container
	 *
	 * @param mixed $value
	 * @return SQLValue
	 */
	function SQLValue($value, $type = null)
	{
		$this->value = $value;

		if (is_null($type))
			$type = gettype($value);
		$this->type = $type;
	}

	function generate($generator)
	{
		return $this->getValue($this->value, $this->type, $generator);
	}

	/**
	 *  Present value as string for using in SQL query.
	 *
	 * @param string $value
	 * @param SQLGenerator $generator
	 */
	static function getAsString(&$value, $generator)
	{
		return sprintf('"%s"', $generator->escapeString($value));
	}

	static function getAsBLOB(&$value, $generator)
	{
		return $generator->generateValueAsBLOB($value);
	}

	static function getAsInt(&$value)
	{
		//process foreign-keys
		if ($value === "")
			return( SQLValue::getAsNull($value) );
		if ($value === null)
			return( SQLValue::getAsNull($value) );
		return( sprintf("%d", $value) );
	}

	static function getAsDatetime(&$value, $generator)
	{
		if (is_string($value))
			return( sprintf("'%s'", $value) );
		return( sprintf("'%s'", $generator->generateDateTime($value)) );
	}

	static function getAsDate(&$value, $generator)
	{
		if (is_string($value))
			return( sprintf("'%s'", $value) );
		return( sprintf("'%s'", $generator->generateDate($value)) );
	}

	static function fromSqlDateTime($value)
	{
		if (!$value) {
			return null;
		}
		$parts = preg_split("/[ T\.\-:]/", $value);
		if (count($parts) < 6) {
			Diagnostics::warning("not expeced SQL datetime: $value");
		}
		list( $year, $month, $day, $hour, $min, $sec ) = $parts;
		$result = mktime(
			intval($hour), intval($min), intval($sec), intval($month), intval($day), intval($year));
		return $result;
	}

	static function fromSqlDate($value)
	{
		if (!$value) {
			return null;
		}
		$parts = preg_split("/[ T\.\-]/", $value);
		if (count($parts) < 3) {
			Diagnostics::warning("not expeced SQL date: $value");
		}
		list( $year, $month, $day) = $parts;
		$result = mktime(
			null, null, null, intval($month), intval($day), intval($year));
		return $result;
	}

	static function getAsNull(&$value)
	{
		return( "NULL" );
	}

	/** returns SQL value with conversion according to Type Definition
	 */
	static function getValue($value, $type, $generator)
	{
		if (is_null($value))
			return( "NULL" );
		if (is_null($type))
			$type = gettype($value);

		switch ($type) {
			default:
			case "enum":
			case "text":
			case "string":
				return( SQLValue::getAsString($value, $generator) );
				break;

			case "longblob":
			case "tinyblob":
			case "mediumblob":
			case "blob": return( SQLValue::getAsBLOB($value, $generator) );
				break;
			case "int":
			case "integer": return( SQLValue::getAsInt($value) );
				break;
			case "date": return( SQLValue::getAsDate($value, $generator) );
				break;
			case "datetime": return( SQLValue::getAsDatetime($value, $generator) );
				break;
		}
	}

	/** returns DBParamType  for specified  Type Definition
	 * 
	 * @param mixed $value  value for parameter, required for NULL detection.
	 * @param string $type   DbType  defined in DBObject/DBColumnDefinition
	 */
	static function getDbParamType($value, $type = null)
	{
		if (is_null($value))
			return( DBParamType_null );
		if (is_null($type))
			$type = gettype($value);

		switch ($type) {
			default:
			case "enum":
			case "date":
			case "datetime":
			case "string":
				return( DBParamType_string );

			case "bool":
			case "boolean":
				return DBParamType_bool;

			case "tinyint":
			case "smallint":
			case "biglint":
			case "mediumint":
			case "int":
			case "integer":
				return( DBParamType_integer );

			case "double":
			case "float":
				return DBParamType_real;


			case "longblob":
			case "tinyblob":
			case "mediumblob":
			case "blob":
				return( DBParamType_lob );
		}

		Diagnostics::error("Don't know how to convert SQL type '$type' to DBParamType");
	}

	/** get value according to type and database engine.
	 * @param SQLGenerator $generator
	 */
	static function getDbParamValue($value, $type, $generator)
	{
		if (is_null($value))
			return( "NULL" );
		if (is_null($type))
			$type = gettype($value);

		switch ($type) {
			case "datetime":
				if (is_string($value))
					return( $value );
				return( $generator->generateDateTime($value) );

			case "date":
				if (is_string($value))
					return( $value );
				return( $generator->generateDate($value) );

			default:
				return $value;
		}
	}

	static function importValue($value, $type)
	{
		switch ($type) {
			case "datetime":
				return( SQLValue::fromSqlDateTime($value) );

			case "date":
				return( SQLValue::fromSqlDate($value) );

			case "tinyint":
			case "smallint":
			case "bigint":
			case "mediumint":
			case "int":
			case "integer":
				return( intval($value) );
				break;

			case "double":
			case "float":
				return floatval($value);

			default:
				return $value;
		}
	}

}

define("CLASS_SQLValue", get_class(new SQLValue(null)));
