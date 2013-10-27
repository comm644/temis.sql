<?php
require_once( dirname( __FILE__ ). "/DBResultContainer.php" );


/**result container for working with SQLStatementSelect
 */ 
class SQLStatementSelectResult extends DBResultContainer
{
	/**
	 * Owner statement
	 *
	 * @var SQLStatementSelect
	 */
	var $stm;
	
	
	function SQLStatementSelectResult($stm, $signUseID)
	{
		$this->stm = $stm;
		$this->signUseID = $signUseID;
	}
	function fromSQL( $row )
	{
		if ( is_array($row)) {
			return $this->stm->readSqlArray( $row );
		}
		else {
			return $this->stm->readSqlObject( $row );
		}
	}
	
	/**
	 * Add row as object to result.
	 *
	 * @param object $obj
	 */
	function add( $obj )
	{
		if ( $this->signUseID == true) {
			$pos = $obj->primary_key_value();
			$this->data[ $pos ] = $obj;
		}
		else {
			$this->data[] = $obj;
		}
	}	
	
}


?>