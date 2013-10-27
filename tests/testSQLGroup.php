<?php
require_once( DIR_MODULES . "/ADO/ADO.php");
require_once( DIR_MODULES . "/ADO/SQLGroup.php");
require_once( dirname(__FILE__ ) . "/objects/schema.php" );


class testSQLGroup extends PhpTest_TestSuite
{

	function testString()
	{
		$expr = new SQLGroup( 'column' );
		$query = $expr->generate(new SQLGenerator());
		
		TS_ASSERT_EQUALS( "`column`", $query );
	}

	function testString_Table()
	{
		$expr = new SQLGroup( 'column' );
		$query = $expr->generate(new SQLGenerator(),'table');
		
		TS_ASSERT_EQUALS( "`table`.`column`", $query );
	}

	
	function testAscending()
	{
		$expr = new SQLGroup( 'column', true );
		$query = $expr->generate(new SQLGenerator());
		
		TS_ASSERT_EQUALS( "`column` ASC", $query );
	}
	function testDescending()
	{
		$expr = new SQLGroup( 'column', false );
		$query = $expr->generate(new SQLGenerator());
		
		TS_ASSERT_EQUALS( "`column` DESC", $query );
	}

	function testName()
	{
		$expr = new SQLGroup( new SQLName( 'table', 'column') );
		$query = $expr->generate(new SQLGenerator());

		TS_ASSERT_EQUALS( "`table`.`column`", $query );
	}

	function testDefinition()
	{
		$proto = new Data;
		
		$expr = new SQLGroup( $proto->tag_data_id() );
		$query = $expr->generate(new SQLGenerator());
		TS_ASSERT_EQUALS( "`t_data`.`data_id`", $query );
	}
}
?>