<?php
require_once( dirname(__FILE__ ) . "/tracer.php" );
require_once( dirname(__FILE__ ) . "/objects/schema.php" );
require_once( dirname( __FILE__ ) . "/MockDataSource.php" );
require_once( DIR_MODULES . "/ADO/ADO.php");

class testDBObjectAdapter extends PhpTest_TestSuite
{
	function getSampleResult()
	{
		return array(
			array( 1, "2000-01-01 00:00", 5, "string", "text", 'red', 'some content', 10),
			array( 2, "2000-01-01 00:00", 5, "string", "text", 'red', 'some content', 10)
			);
	}
	
	function tesT_select_default()
	{
	
		$ds = new MockDataSource();
		$ds->answer[] = $this->getSampleResult();
		
		$dbo = new DBObjectAdapter( $ds, new Data );

		$rc = $dbo->select( $objects );
		TS_ASSERT_EQUALS( 0, $rc );
		TS_ASSERT_EQUALS( 2, count( $objects ) );

		$expected = "SELECT "
			."`t_data`.`data_id`,"
			."`t_data`.`date`,"
			."`t_data`.`value`,"
			."`t_data`.`string`,"
			."`t_data`.`text`,"
			."`t_data`.`enum`,"
			."`t_data`.`dictionary_id` "
			."FROM `t_data`";

		TS_ASSERT_EQUALS( $expected, $ds->query[0] );
	}

	function test_select_expression()
	{
	
		$ds = new MockDataSource();
		$ds->answer[] = $this->getSampleResult();

		$dbo = new DBObjectAdapter( $ds, new Data );

		$expr = new ExprEQ( $dbo->def->tag_data_id(), 5 );
		$rc = $dbo->select( $objects,  $expr );
		TS_ASSERT_EQUALS( 0, intval($rc) );
		TS_ASSERT_EQUALS( 2, count( $objects ) );

		$expected = "SELECT "
			 ."`t_data`.`data_id`,"
			 ."`t_data`.`date`,"
			 ."`t_data`.`value`,"
			 ."`t_data`.`string`,"
			 ."`t_data`.`text`,"
			."`t_data`.`enum`,"
			."`t_data`.`blob`,"
			 ."`t_data`.`dictionary_id` "
			."FROM `t_data` WHERE (`t_data`.`data_id` = 5)";

		TS_ASSERT_EQUALS( $expected, $ds->query[0] );
	}

	function test_select_condition_combined_not_recoment_for_using()
	{
		$ds = new MockDataSource();
		$ds->answer[] = $this->getSampleResult();

		$dbo = new DBObjectAdapter( $ds, new Data );

		$rc = $dbo->select( $objects,  null, "data_id, date, text DESC" );
		TS_ASSERT_EQUALS( 0, $rc );
		TS_ASSERT_EQUALS( 2, count( $objects ) );

		$expected = "SELECT "
			."`t_data`.`data_id`,"
			."`t_data`.`date`,"
			."`t_data`.`value`,"
			."`t_data`.`string`,"
			."`t_data`.`text`,"
			."`t_data`.`enum`,"
			."`t_data`.`blob`,"
			."`t_data`.`dictionary_id` "
			."FROM `t_data` "
			."ORDER BY `t_data`.`data_id` ASC,"
			."`t_data`.`date` ASC,"
			."`t_data`.`text` DESC";

		TS_ASSERT_EQUALS( $expected, $ds->query[0] );
	}
	
	function test_select_condition_old_style_unrecomended()
	{
		$ds = new MockDataSource();
		$ds->answer[] = $this->getSampleResult();

		$dbo = new DBObjectAdapter( $ds, new Data );

		$expr = "data_id = 5";

		ob_start();
		$rc = $dbo->select( $objects,  $expr );
		$log = ob_get_clean();
		
		TS_ASSERT_EQUALS( 0, $rc );
		TS_ASSERT_EQUALS( 2, count( $objects ) );

		$expected = "SELECT "
			."`t_data`.`data_id`,"
			."`t_data`.`date`,"
			."`t_data`.`value`,"
			."`t_data`.`string`,"
			."`t_data`.`text`,"
			."`t_data`.`enum`,"
			."`t_data`.`blob`,"
			."`t_data`.`dictionary_id` "
			."FROM `t_data`  WHERE data_id = 5  ";

		TS_ASSERT_EQUALS( $expected, $ds->query[0] );
		TS_ASSERT_DIFFERS( "", $log );
		
	}

	function testInsert()
	{
		$ds = new MockDataSource();
		$ds->answer[] = new MockQueryAnswer(0, 10 );

		$dbo = new DBObjectAdapter( $ds, new Data );

		$obj = new Data;
		$obj->set_text( "some text" );

		$dbo->insert( $obj, true );

		$expected = "INSERT INTO `t_data` SET `text` = 'some text'";
		
		TS_ASSERT_EQUALS( $expected, $ds->query[0] );
	}
	function testInsert_all_fields()
	{
		$ds = new MockDataSource();
		$ds->answer[] = new MockQueryAnswer(0, 10 );

		$dbo = new DBObjectAdapter( $ds, new Data );

		$obj = new Data;
		$obj->set_text( "some text" );
		$obj->set_date( mktime(1, 13, 0, 1, 4, 2007) );
		$obj->set_string( "some string" );
		$obj->set_value( 5 );
		$obj->set_dictionary_id( null );
		

		$dbo->insert( $obj, true );

		$expected = "INSERT INTO `t_data` SET "
			."`date` = '2007-01-04 01:13:00'"
			.",`value` = 5"
			.",`string` = 'some string'"
			.",`text` = 'some text'";

		TS_ASSERT_EQUALS( $expected, $ds->query[0] );
	}
	function testInsert_old_style_not_recomended()
	{
		$ds = new MockDataSource();
		$ds->answer[] = new MockQueryAnswer(0, 10 );

		$dbo = new DBObjectAdapter( $ds, new Data );

		$obj = new Data;
		$obj->set_text( "some text" );

		$dbo->insert( $obj );

		//only data fields without PK
		$expected = "INSERT INTO `t_data` SET "
			."`date` = NULL,"
			."`value` = NULL,"
			."`string` = NULL,"
			."`text` = 'some text',"
			."`enum` = 'red',"
			."`blob` = NULL,"
			."`dictionary_id` = NULL";
		
		TS_ASSERT_EQUALS( $expected, $ds->query[0] );
	}
	
}

?>