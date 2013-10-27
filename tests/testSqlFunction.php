<?php
require_once( DIR_MODULES . "/ADO/ADO.php");
require_once( DIR_MODULES . "/ADO/SQLFunction.php");
require_once( dirname(__FILE__ ) . "/objects/schema.php" );


class testSqlFunction extends PhpTest_TestSuite
{

	function testCount_with_alias()
	{
		$data = new Data();
		
		$stm = SQLFunction::count( $data->tag_text(), 'count' );

		$query = $stm->generate(new SQLGenerator());
		TS_ASSERT_EQUALS( "count( `t_data`.`text` ) AS `count`", $query );
	}

	function testCount_with_alias2_not_recomended()
	{
		$name = 'column';
		$stm = SQLFunction::count( $name );

		$query = $stm->generate(new SQLGenerator());
		TS_ASSERT_EQUALS( "count( `column` ) AS `column`", $query );
	}
	
	function testCount_with_wildcard()
	{
		$name = '*';
		$stm = SQLFunction::count( $name, 'count' );

		$query = $stm->generate(new SQLGenerator());
		TS_ASSERT_EQUALS( "count( `*` ) AS `count`", $query );
	}
	
	function testCount_noalias()
	{
		$data = new Data();
		
		$stm = SQLFunction::count( $data->tag_text() );

		$query = $stm->generate(new SQLGenerator());
		TS_ASSERT_EQUALS( "count( `t_data`.`text` )", $query );
	}
	function testCount_Aliased()
	{
		$data = new Data();
		
		$stm = SQLFunction::count( $data->tag_text(), "count" );

		$query = $stm->generate(new SQLGenerator());
		TS_ASSERT_EQUALS( "count( `t_data`.`text` ) AS `count`", $query );
	}
}
?>