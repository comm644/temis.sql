<?php
require_once( dirname( __FILE__ ) . "/../ADO.php" );
require_once( dirname( __FILE__ ) . "/objects/schema.php" );
require_once dirname(__FILE__) . '/mocks.php';
require_once __ADO_PHP_DIR__ . 'Mysql/MysqlDataSource.php';

class testExpressions extends PhpTest_TestSuite
{
	public function suiteStart()
	{
		 mysql_connect(null, "root", "root");
	}

		function getCompiler()
	{
        $ds = new MysqlDataSource();
		return new ECompilerSQL( $ds->getGenerator() );
	}
	
	function testAND_OR()
	{
		$expr = new ExprAND(
			array( 1,
				new ExprOR( array( 2, 3 ) ),
				"string",
				4
				)
			);
			
		$compiler = $this-> getCompiler();
		$query = $compiler->compile( $expr );
		TS_ASSERT_EQUALS( "(1 AND (2 OR 3) AND 'string' AND 4)", $query );
	}
	function testAND_OR_2()
	{
		$expr = new ExprAND(
			1, new ExprOR( array( 2, 3 ) ), "string", 4);

		$compiler = $this-> getCompiler();
		$query = $compiler->compile( $expr );
		TS_ASSERT_EQUALS( "(1 AND (2 OR 3) AND 'string' AND 4)", $query );
	}
	function testEmpty()
	{
		$expr = new ExprAND( array() );

		$compiler = $this-> getCompiler();
		$query = $compiler->compile( $expr );
		TS_ASSERT_EQUALS( "", $query );
	}
	function testNull()
	{
		$expr = new ExprAND( null );

		$compiler = $this-> getCompiler();
		$query = $compiler->compile( $expr );
		TS_ASSERT_EQUALS( "", $query );
	}
	function testIN()
	{
		$expr = new ExprIN( 'table.column', array( 1, 'a', NULL ) );
		
		$compiler = $this-> getCompiler();
		$query = $compiler->compile( $expr );
		TS_ASSERT_EQUALS( "`table`.`column` IN (1,'a') AND `table`.`column` IS NULL", $query );
	}
	function testIN_tag()
	{
		$proto = new Data;
		
		$expr = new ExprIN( $proto->tag_data_id(), array( 1, 'a', NULL ) );

		$compiler = $this-> getCompiler();
		$query = $compiler->compile( $expr );
		TS_ASSERT_EQUALS( "`t_data`.`data_id` IN (1,'a') AND `t_data`.`data_id` IS NULL", $query );
	}
	function testIN_empty_set()
	{
		$expr = new ExprIN( 'table.column', array() );

		$compiler = $this-> getCompiler();
		$query = $compiler->compile( $expr );
		TS_ASSERT_EQUALS( "", $query );
	}
	function testIN_only_null()
	{
		$expr = new ExprIN( 'table.column', array(NULL) );

		$compiler = $this-> getCompiler();
		$query = $compiler->compile( $expr );
		TS_ASSERT_EQUALS( "`table`.`column` IS NULL", $query );
	}
	function testIN_only_one_value()
	{
		$expr = new ExprIN( 'table.column', array( 1 ) );

		$compiler = $this-> getCompiler();
		$query = $compiler->compile( $expr );
		TS_ASSERT_EQUALS( "`table`.`column` IN (1)", $query );
	}
	
	function testEQUAL()
	{
		$expr = new ExprEQ( 'column', array( 1, 'a', NULL ) );

		$compiler = $this-> getCompiler();
		$query = $compiler->compile( $expr );
		TS_ASSERT_EQUALS( "(`column` = 1 AND `column` = 'a' AND `column` = NULL)", $query );
	}

	function testEQUAL_2()
	{
		$expr = new ExprEQ( 'column',1 );

		$compiler = $this-> getCompiler();
		$query = $compiler->compile( $expr );
		TS_ASSERT_EQUALS( "(`column` = 1)", $query );
	}
	function testEQUAL_0()
	{
		$expr = new ExprEQ( 'column', 0);

		$compiler = $this-> getCompiler();
		$query = $compiler->compile( $expr );
		TS_ASSERT_EQUALS( "(`column` = 0)", $query );
	}

	
	function testEQ_type_datetime()
	{
		$data = new Data();
		$expr = new ExprEQ($data->tag_date(), 1 );
		
		$compiler = $this-> getCompiler();
		$query = $compiler->compile( $expr );
		TS_ASSERT_EQUALS( "(`t_data`.`date` = '1970-01-01 03:00:01')", $query );
	}

	function testLike()
	{
		$expr = new ExprLike( 'column', 'text' );

		$compiler = $this-> getCompiler();
		$query = $compiler->compile( $expr );
		TS_ASSERT_EQUALS( "(`column` LIKE '%text%')", $query );
	}
	function testName()
	{
		$compiler = $this-> getCompiler();
		$name = $compiler->getName( "table.column" );

		TS_ASSERT_EQUALS( "`table`.`column`", $name );
	}

	function testRange()
	{
		$expr = new ExprRange( 'column', 2, 3 );

		$compiler = $this-> getCompiler();
		$query = $compiler->compile( $expr );
		TS_ASSERT_EQUALS( "((`column` >= 2) AND (`column` <= 3))", $query );
	}

}
?>