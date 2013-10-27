<?php
require_once( dirname(__FILE__ ) . "/tracer.php" );
require_once( dirname(__FILE__ ) . "/objects/schema.php" );
require_once( dirname(__FILE__ ) . "/../ADO.php");

class testSqlSelect extends PhpTest_TestSuite
{

	function testSelect_Object()
	{
		$data = new Data();
		$data->setTableAlias( "d" );
		
		$stm = new SQLStatementSelect( $data );

		$generator = new MysqlGenerator();
		$query = $stm->generate($generator );
		
		$expected = "SELECT"
		   ." `d`.`data_id` AS `data_id`,"
			." `d`.`date` AS `date`,"
			." `d`.`value` AS `value`,"
			." `d`.`string` AS `string`,"
			." `d`.`text` AS `text`,"
			." `d`.`enum` AS `enum`,"
			." `d`.`blob` AS `blob`,"
			." `d`.`real` AS `real`,"
			." `d`.`dictionary_id` AS `dictionary_id`"
			." FROM `t_data` AS `d`";
		
		TS_ASSERT_EQUALS( $expected, $query );
	}
	function testExpessions()
	{
		$data = new Data();
		$data->setTableAlias( "d" );
		
		$expr = new ExprAND(
			new ExprEQ( $data->tag_value(), 1),
			 new ExprEQ( $data->tag_date(), 1),
			 new ExprEQ( $data->tag_string(), ""),
			 new ExprEQ( $data->tag_text(), ""),
			 new ExprEQ( $data->tag_text(), null)
			);
		$expected = ""
			."((`d`.`value` = 1)"
			." AND (`d`.`date` = '1970-01-01 03:00:01')"
			." AND (`d`.`string` = \"\")"
			." AND (`d`.`text` = \"\")"
			." AND (`d`.`text` IS NULL))"
			;
		
		$query = SQL::compileExpr( $expr, new MysqlGenerator() );
			
		TS_ASSERT_EQUALS( $expected, $query );
	}

	/**
	 validate:

	 column alias
	 table alias
	 join
	 columns
	 order
	 */
	function testSelect()
	{
		$data = new Data();
		$data->setTableAlias( "d" );

		$dic = new Dictionary();
		$dic->setTableAlias( "dic" );
		
		$stm = new SQLStatementSelect( $data );
		$stm->setExpression(
			new ExprAND(
				new ExprEQ( $data->tag_value(), 1),
				 new ExprEQ( $data->tag_date(), 1),
				 new ExprEQ( $data->tag_string(), ""),
				 new ExprEQ( $data->tag_text(), ""),
				 new ExprEQ( $data->tag_text(), null)
				) );
		
		$stm->addOrder( $data->tag_date() );
		$stm->addGroup( $data->tag_date() );
		$stm->addColumn( $dic->tag_text() );
		$stm->addColumn( $dic->tag_text("dic_text") ); //same and alias
		$stm->addJoin( $data->key_dictionary_id($dic) );

		$query = $stm->generate(new MysqlGenerator());
		
		$expected = "SELECT"
		   ." `d`.`data_id` AS `data_id`,"
			." `d`.`date` AS `date`,"
			." `d`.`value` AS `value`,"
			." `d`.`string` AS `string`,"
			." `d`.`text` AS `text`,"
			." `d`.`enum` AS `enum`,"
			." `d`.`blob` AS `blob`,"
			." `d`.`real` AS `real`,"
			." `d`.`dictionary_id` AS `dictionary_id`,"
			." `dic`.`text` AS `text`,"
			." `dic`.`text` AS `dic_text`"
			." FROM `t_data` AS `d` "
			."LEFT JOIN `t_dictionary` AS `dic` ON (`dic`.`dictionary_id` = `d`.`dictionary_id`) "
			."WHERE ((`d`.`value` = 1)"
			." AND (`d`.`date` = '1970-01-01 03:00:01')"
			." AND (`d`.`string` = \"\")"
			." AND (`d`.`text` = \"\")"
			." AND (`d`.`text` IS NULL)) "
			."GROUP BY `d`.`date` "
			."ORDER BY `d`.`date` ASC"
			;
		
		TS_ASSERT_EQUALS( $expected, $query );
	}
	
	function testSelect_Condition1()
	{
		$data = new Data();
		$data->setTableAlias( "d" );

		$dic = new Dictionary();
		$dic->setTableAlias( "dic" );

		
		$stm = new SQLStatementSelect( $data );
		$stm->setLimit( 10 );
		$stm->setOffset( 10 );
		

		$query = $stm->generate(new MysqlGenerator());
		
		$expected = "SELECT"
		   ." `d`.`data_id` AS `data_id`,"
			." `d`.`date` AS `date`,"
			." `d`.`value` AS `value`,"
			." `d`.`string` AS `string`,"
			." `d`.`text` AS `text`,"
			." `d`.`enum` AS `enum`,"
			." `d`.`blob` AS `blob`,"
			." `d`.`real` AS `real`,"
			." `d`.`dictionary_id` AS `dictionary_id`"
			." FROM `t_data` AS `d` "
			."LIMIT 10 "
			."OFFSET 10"
			;
		
		TS_ASSERT_EQUALS( $expected, $query );
		
	}
}

?>