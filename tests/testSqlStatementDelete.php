<?php
require_once( dirname(__FILE__ ) . "/tracer.php" );
require_once( dirname(__FILE__ ) . "/objects/schema.php" );
require_once( dirname(__FILE__ ) . "/../ADO.php");
require_once( dirname(__FILE__ ) . "/../Mysql/MysqlDataSource.php");


class testSqlStatementDelete extends PhpTest_TestSuite
{
	function ws($str )
	{
		return str_replace("\n", "", $str );
	}

	/**
	 * WHEN object contains valid PK then will be deleted only this object.
	 */
	function testObject()
	{
		$obj = new Data();
		$obj->set_data_id(10);

		$stm = new SQLStatementDelete( $obj );

		$generator = new MysqlGenerator();
		$query = $stm->generate($generator );

		$expected = "DELETE FROM `t_data` WHERE (`t_data`.`data_id` = 10)";

		TS_ASSERT_EQUALS( $expected, $this->ws($query) );
	}

	/**
	 * WHEN prototype given to statement THEN will be deleted all objects.
	 */
	function testDeleteAll()
	{
		$obj = new Data();

		$stm = new SQLStatementDelete( $obj );

		$generator = new MysqlGenerator();
		$query = $stm->generate($generator );

		$expected = "DELETE FROM `t_data`";

		TS_ASSERT_EQUALS( $expected, $this->ws($query) );
	}
}