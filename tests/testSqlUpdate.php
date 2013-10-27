<?php
require_once( dirname(__FILE__ ) . "/tracer.php" );
require_once( dirname(__FILE__ ) . "/objects/schema.php" );
require_once( dirname(__FILE__ ) . "/../ADO.php");
require_once( dirname(__FILE__ ) . "/../Mysql/MysqlDataSource.php");

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of testSqlUpdate
 *
 * @author comm
 */
class testSqlUpdate extends PhpTest_TestSuite
{
	function setUp()
	{
	 mysql_connect( null, "root", "root" );
	}
	
    
    function testUseCase()
    {
        $obj = new Data();
		$obj->set_data_id(10);
        $obj->set_value(1);

        $stm = new SQLStatementUpdate($obj);

		$generator = new MysqlGenerator();
		$query = $stm->generate($generator );

        $this->assertEquals('UPDATE `t_data` SET `value`=1 WHERE (`data_id` = 10) LIMIT 1', $query);
    }
	
	//WHEN primary key chnaged THEN previous key value must be used in stetement
    function testUpdatePrimaryKeyChange()
    {
        $obj = new Data();
		$obj->set_data_id(5);
		$obj->set_data_id(10);
        $obj->set_value(1);

        $stm = new SQLStatementUpdate($obj);
		$stm->enableChangePK();

		$generator = new MysqlGenerator();
		$query = $stm->generate($generator );

        $this->assertEquals('UPDATE `t_data` SET `data_id`=10,`value`=1 WHERE (`data_id` = 5) LIMIT 1', $query);
    }
	
}


