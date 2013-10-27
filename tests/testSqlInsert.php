<?php
require_once( dirname(__FILE__ ) . "/tracer.php" );
require_once( dirname(__FILE__ ) . "/objects/schema.php" );
require_once( dirname(__FILE__ ) . "/../ADO.php");
require_once( dirname(__FILE__ ) . "/../Mysql/MysqlDataSource.php");


/**
 * Description of testSqlInsert
 *
 * @author comm
 */
class testSqlInsert extends PhpTest_TestSuite
{
	function setUp()
	{
	 mysql_connect(null, "root", "root");
	}

    //put your code here
    function test1()
    {
        $obj = new Data();
        $obj->set_string('string\\path');
        $obj->set_value(1);
        $obj->set_date(mktime(12, 30, 45, 4, 10, 2010));

        $stm = new SQLStatementInsert($obj);

		$generator = new MysqlGenerator();
		$query = $stm->generate($generator );

        $this->assertEquals('INSERT INTO `t_data` ( `date`,`value`,`string`,`text`,`enum`,`blob`,`real`,`dictionary_id` ) '
                .'VALUES ( \'2010-04-10 12:30:45\',1,"string\\\\path",NULL,"red",NULL,NULL,NULL )', $query);
    }
	function xtestInsertSet()
	{
		$data = new Data();
		$data->set_data_id( 100 );
		$data->set_value( 'value' );

		$stm = new SQLStatementInsert( $data );

		$query = $stm->generate(new MysqlGenerator());

		$expected = "INSERT INTO `t_data` SET "
			."`date`=NULL,"
			."`value`=0,"
			."`string`=NULL,"
			."`text`=NULL,"
			."`enum`='red',"
			."`blob`=NULL,"
			."`real`=NULL,"
			."`dictionary_id`=NULL";

		TS_ASSERT_EQUALS( $expected, $query );
	}
	function testInsertValues()
	{
		$data = new Data();
		$data->set_data_id( 100 );
		$data->set_value( 'value' );

		$stm = new SQLStatementInsert( $data );

		$query = $stm->generate(new MysqlGenerator());

		$expected = "INSERT INTO `t_data` "
            ."( `date`,`value`,`string`,`text`,`enum`,`blob`,`real`,`dictionary_id` )"
            ." VALUES"
            ." ( NULL,0,NULL,NULL,\"red\",NULL,NULL,NULL )";

		TS_ASSERT_EQUALS( $expected, $query );
	}
	function testInsertValuesForArray()
	{
		$data = new Data();
		$data->set_data_id( 100 );
		$data->set_value( 1001 );
		

		$stm = new SQLStatementInsert( array( $data, $data ));

		$query = $stm->generate(new MysqlGenerator());

		$expected = "INSERT INTO `t_data` "
            ."( `date`,`value`,`string`,`text`,`enum`,`blob`,`real`,`dictionary_id` )"
            ." VALUES"
            ." ( NULL,1001,NULL,NULL,\"red\",NULL,NULL,NULL )"
		    .",( NULL,1001,NULL,NULL,\"red\",NULL,NULL,NULL )"
		;

		TS_ASSERT_EQUALS( $expected, $query );
	}


	function xtestInsertSet_withPK()
	{
		$data = new Data();
		$data->set_data_id( 100 );
		$data->set_value( 'value' );

		$stm = new SQLStatementInsert( $data, true );

		$query = $stm->generate(new MysqlGenerator());

		$expected = "INSERT INTO `t_data` SET "
			."`data_id`=100,"
			."`date`=NULL,"
			."`value`=0,"
			."`string`=NULL,"
			."`text`=NULL,"
			."`enum`='red',"
			."`blob`=NULL,"
			."`real`=NULL,"
			."`dictionary_id`=NULL";

		TS_ASSERT_EQUALS( $expected, $query );
	}
	function testInsertValues_withPK()
	{
		$data = new Data();
		$data->set_data_id( 100 );
		$data->set_value( 'value' );

		$stm = new SQLStatementInsert( $data, true );

		$query = $stm->generate(new MysqlGenerator());

		$expected = "INSERT INTO `t_data` "
			."( `data_id`,`date`,`value`,`string`,`text`,`enum`,`blob`,`real`,`dictionary_id` )"
			." VALUES ( 100,NULL,0,NULL,NULL,\"red\",NULL,NULL,NULL )";

		TS_ASSERT_EQUALS( $expected, $query );
	}
}
?>
