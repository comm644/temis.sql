<?php
require_once( dirname(__FILE__ ) . "/tracer.php" );
require_once( dirname(__FILE__ ) . "/objects/schema.php" );
require_once( DIR_MODULES . "/ADO/ADO.php");

class C1
{
	var $field;

	function method()
	{
		return "text";
	}
}

class testDBObject extends PhpTest_TestSuite
{
	function test1()
	{
		$obj = new C1;
		
		$rc= array_key_exists( "field", $obj );
		TS_ASSERT_EQUALS( true, $rc );
		
		$rc= array_key_exists( "unexists", $obj );
		TS_ASSERT_EQUALS( false, $rc );
	}
	function test2()
	{
		$obj = "str";

		TS_ASSERT_EQUALS( FALSE, is_object( $obj) &&  get_class( $obj ) );
	}

}
?>
