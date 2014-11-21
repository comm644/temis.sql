<?php
require_once( dirname( __FILE__ ). '/SqliteDictionary.php' );



class SqliteGenerator extends SQLGenerator 
{
	/**
	 * dictionary
	 *
	 * @var SqliteDictionary
	 */
	private $_dictionary;
	
	function SqliteGenerator()
	{
		$this->_dictionary = new SqliteDictionary();
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
	
	function generateInsert($stm)
	{
		$tname = new SQLName($stm->table, null);
		
		$sql = $this->getDictionary();
		
		$parts = array();
		$parts[] = $stm->sqlStatement;
		$parts[] = $tname->generate( $this );
		
		$parts[] = $sql->sqlOpenFuncParams;
		$parts[] = $stm->_getColumns($this);
		$parts[] = $sql->sqlCloseFuncParams;
		
		$parts[] = $sql->sqlValues;
		
		$parts[] = $sql->sqlOpenFuncParams;
		$parts[] = $stm->_getValues($this);
		$parts[] = $sql->sqlCloseFuncParams;
		
		return( implode( " ", $parts ) );
	}
	
	function generate( $stm )
	{
		switch( get_class( $stm ))
		{
			case CLASS_SQLStatementInsert:
				return $this->generateInsert($stm);
			default:
				return parent::generate( $stm);
		}
	}
	function generateValueasBLOB( &$value )
	{
		//mysql
		return( "X'" . bin2hex( $value ) ."'");
	}
	
	/** convert unit-time to ISO8601 format. "yyyy-MM-ddTHH:mm:ss.SSS"
	 */
	function generateDateTime( $value )
	{
		$str = strftime( '%Y-%m-%dT%H:%M:%S', $value );
		return $str;
	}

	/**
	 * for PDO returns same string. because PDO can escape by the way.
	 * @param string $value
	 */
	function escapeString( $value )
	{
		return $value;
	}

	function updateLimitEnabled()
	{
		return false;
	}
}