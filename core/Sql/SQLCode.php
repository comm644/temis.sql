<?php

class SQLCode
{
	var $code;

	function SQLCode( $code )
	{
		$this->code = $code;
	}
	function generate()
	{
		return ($this->code);
	}
}

define( "CLASS_SQLCode", get_class( new SQLCode(null ) ) );
?>