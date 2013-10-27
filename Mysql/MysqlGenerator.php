<?php

class MysqlGenerator extends SQLGenerator
{
	/**
	 * dictionary
	 *
	 * @var SqliteDictionary
	 */
	private $_dictionary;
	
	function MysqlGenerator()
	{
		$this->_dictionary = new MysqlDictionary();
	}
	
	/**
	 * returns dictionary
	 *
	 * @return SqliteDictionary
	 */
	function getDictionary()
	{
		return $this->_dictionary;
	}

	/**
	 * Escape string for using in non-compileable SQL requestes.
	 *
	 * @param string $value
	 */
	function escapeString( $value )
	{
		return MysqlDataSource::escapeString( $value );
	}
}