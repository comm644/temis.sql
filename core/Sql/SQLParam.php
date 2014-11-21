<?php
class SQLParam extends Clonable
{
	var $value;

	function SQLParam( $value )
	{
		$this->value = $value;
	}

	/** protected. generate quiery accoring to keyword and saved value
	 */
	function _generate( $statement )
	{
		$parts = array();
		$parts[] = $statement;
		$parts[] = $this->value;
		return( implode( " ", $parts ) );
	}
		
}


?>