<?php
require_once( DIR_MODULES . "/ADO/ADO.php");


class testOffset extends PhpTest_TestSuite
{

	function testBasic()
	{
		$stm = new SQLOffset( 10 );

		$query = $stm->generate();
		TS_ASSERT_EQUALS( "OFFSET 10", $query );
	}

	function test_zero_offset_should_be_ignored()
	{
		$stm = new SQLOffset( 0 );

		$query = $stm->generate();
		TS_ASSERT_EQUALS( "", $query );
	}
}

?>