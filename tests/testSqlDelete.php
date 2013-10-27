<?php
require_once( dirname(__FILE__ ) . "/tracer.php" );
require_once( dirname(__FILE__ ) . "/objects/schema.php" );
require_once( dirname(__FILE__ ) . "/../ADO.php");
require_once( dirname(__FILE__ ) . "/../Mysql/MysqlDataSource.php");

class testSqlDelete extends PhpTest_TestSuite
{
	function setUp()
	{
	 mysql_connect(null, "root", "root");
	}
	
	function testCanDeleteByLikeNoMask()
	{
		$proto = new Data();
		
		$stm =new SQLStatementDelete( $proto );
		$stm->addExpression( new ExprLikeNoMask($proto->tag_string(), "begin%"));
		
		$generator = new MysqlGenerator();
		$sql = $stm->generate($generator);
		
		$this->assertEquals("DELETE FROM `t_data` WHERE (`t_data`.`string` LIKE \"begin%\")", $sql);
	}
	function testCanDeleteByEquals()
	{
		$proto = new Data();
		
		$stm =new SQLStatementDelete( $proto );
		$stm->addExpression( new ExprEQ($proto->tag_string(), "begin%"));
		
		$generator = new MysqlGenerator();
		$sql = $stm->generate($generator);
		
		$this->assertEquals("DELETE FROM `t_data` WHERE (`t_data`.`string` = \"begin%\")", $sql);
	}
	function testCanDeleteBySetExpression()
	{
		$proto = new Data();
		
		$stm =new SQLStatementDelete( $proto );
		$stm->setExpression( new ExprEQ($proto->tag_string(), "begin%"));
		
		$generator = new MysqlGenerator();
		$sql = $stm->generate($generator);
		
		$this->assertEquals("DELETE FROM `t_data` WHERE (`t_data`.`string` = \"begin%\")", $sql);
	}
}