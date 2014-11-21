	<?php

require_once( dirname(__FILE__ ) . "/tracer.php" );
require_once( dirname(__FILE__ ) . "/objects/schema.php" );
require_once( dirname( __FILE__ ) . "/MockDataSource.php" );
require_once( dirname( __FILE__ ) . "/../ADO.php");
require_once dirname(__FILE__) . '/mocks.php';

class testDBRelationAdapter extends PhpTest_TestSuite
{
	function ws($str )
	{
		return str_replace("\n", "", $str );
	}
	function getSampleResult()
	{
		return array(
			array( 1, "2000-01-01 00:00", 5, "string", "text", 'red', 10),
			array( 2, "2000-01-01 00:00", 5, "string", "text", 'red', 10)
			);
	}

	function testSelect()
	{

		$ds = new MockDataSource();
		$ds->answer[] = $this->getSampleResult();

		
		$ra = new DataDictionaryRelation();

		$query = $ra->stmSelectChilds( 5 );
		$expected = 
			"SELECT `t_dictionary`.`dictionary_id` AS `dictionary_id`, `t_dictionary`.`text` AS `text` "
			."FROM `t_dictionary`,`t_link` "
			."WHERE ((`t_link`.`dictionary_id` = `t_dictionary`.`dictionary_id`) "
			."AND `t_link`.`data_id` IN (5))";

		$gen = new SQLGenerator();
		
		TS_ASSERT_EQUALS( $expected, $this->ws($gen->generate( $query )) );
	}

	function testSelect_with_complex_types()
	{

		$ds = new MockDataSource();
		$ds->answer[] = $this->getSampleResult();

		
		$ra = new AnotherDataDictionaryRelation($ds);

		$query = $ra->stmSelectChilds( 5 );
		$expected = 
			"SELECT `t_dictionary`.`dictionary_id` AS `dictionary_id`, `t_dictionary`.`text` AS `text` "
			."FROM `t_dictionary`,`t_another_link` "
			."WHERE ((`t_another_link`.`child_id` = `t_dictionary`.`dictionary_id`) "
			."AND `t_another_link`.`owner_id` IN (5))";
		
		$gen = new SQLGenerator();
		TS_ASSERT_EQUALS( $expected, $this->wS($gen->generate( $query )) );
	}
	function testAdd()
	{
		$ds = new MockDataSource();
		$ds->answer[] = array( array( 'count' => 0 ) ); //count
		$ds->answer[] = new MockQueryAnswer( 0, 1 );

		
		$ra = new DataDictionaryRelation($ds);

		$query = $ra->add(5, 1 );
		
		TS_ASSERT_CONTAINS($this->ws($ds->query[0]), "count( `t_link`.`link_id` )" );
		TS_ASSERT_CONTAINS($this->ws($ds->query[0]), "WHERE ((`t_link`.`data_id` = 5) AND (`t_link`.`dictionary_id` = 1))");
		
		TS_ASSERT_EQUALS( "INSERT INTO `t_link` ( `data_id`,`dictionary_id` ) VALUES ( 5,1 )", $this->ws($ds->query[1]) );
	}
}
?>