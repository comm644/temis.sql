<?php
require_once( dirname(__FILE__ ) . "/tracer.php" );
require_once( dirname(__FILE__ ) . "/objects/schema.php" );
require_once( DIR_MODULES . "/ADO/ADO.php");
require_once( dirname(__FILE__ ) . "/../Sqlite/SqliteDictionary.php" );
require_once( dirname(__FILE__ ) . "/../Sqlite/PdoDataSource.php" );


class testSqlite extends PhpTest_TestSuite
{

	function executeStatement( $db, $sql )
	{
		$query = $db->query( $sql .";", PDO::FETCH_CLASS, "stdclass" );

	//	print_r( $query);
		
	//	print("\nerror:");
		//print_r( $db->errorCode() );
//		print_r( $db->errorInfo());
		return $query;
	}

	function testDirectPDO()
	{
		
		$file = dirname( __FILE__ ) . "/database.db";
		$mode = 0666;
		$error_message = null;

		if ( file_exists( $file ) ) unlink( $file );

		$db = new PDO( "sqlite:$file");


		$sql= file_get_contents( dirname( __FILE__ ) ."/objects/schema.sqlite" );
		

		$db->exec($sql.";");
//		$this->executeStatement($db, $batch);
		
/*		if ( $rc === fALSE ) {
			echo "db error: " . $msg;
		}
*/
		$gen = new SqliteGenerator();

		
		$data = new Data();
		$data->setTableAlias( "d" );
		$data->set_string( "value" );
		$data->set_date( "2009-07-07 23:50:20.10" );
		$data->set_enum('red');
		$data->set_text('some text');
		$data->set_value(10);
		$data->set_real(123.50);
		$data->set_blob("bytes\001\002\003");
		
		
		$stm = new SQLStatementInsert( $data );
		$sql= $gen->generate( $stm );
	//		print $sql."\n";

//		print "\ninsert: $sql";
		$db->exec($sql.";");
		$db->exec($sql.";");
		//		$this->executeStatement($db, $sql);
//		print "\n";
		
		
		$stm = new SQLStatementSelect( $data );

		$sql = $gen->generate( $stm );
		

		//print $sql ."\n";
		
		$expected = "SELECT"
		    ." d.data_id,"
			." d.date,"
			." d.value,"
			." d.string,"
			." d.text,"
			." d.enum,"
			." d.blob,"
			." d.real,"
			." d.dictionary_id"
			." FROM t_data AS d";
		
		TS_ASSERT_EQUALS( $expected, $sql );
		

		$rc = $this->executeStatement($db, $sql);
		
		$array =$rc->fetchAll();

//		print_r( $array);
		TS_ASSERT_EQUALS(1, intval($array[0]->data_id));
		TS_ASSERT_EQUALS($data->enum, $array[0]->enum);
		TS_ASSERT_EQUALS($data->string, $array[0]->string);
		TS_ASSERT_EQUALS($data->value, intval($array[0]->value));
		TS_ASSERT_EQUALS($data->date, $array[0]->date);
		TS_ASSERT_EQUALS(strval($data->real), $array[0]->real);
		
/*		
		if ( $result === fALSE ) {
			echo "db error: " . $msg;
		}*/
	}
	
	function testWithDataSource()
	{
		$file = dirname( __FILE__ ) . "/database.db";
		if ( file_exists( $file ) ) unlink( $file );
		
				
		$ds = new PdoDataSource();
		$ds->connect("sqlite://localhost/?database=$file");
//		$ds->signShowQueries = true;
		
		$sql= file_get_contents( dirname( __FILE__ ) ."/objects/schema.sqlite" );
		$container = new DBResultContainer();
		
		$ds->queryCommand($sql, $container);
		
		$data = new Data();
		$data->setTableAlias( "d" );
		$data->set_string( "value" );
		$data->set_date( mktime(13, 27, 20, 9, 6, 2009));
		$data->set_enum('red');
		$data->set_text('some text');
		$data->set_value(10);
		$data->set_real(123.62);
		$data->set_blob("bytes\001\002\003");
		
		
		$stm = new SQLStatementInsert( $data );
		$container = $stm->createResultContainer();
		$ds->queryStatement($stm, $container);
		TS_ASSERT_EQUALS(1, $container->data[0], "affected rows");

		$ds->queryStatement($stm, $container);
		TS_ASSERT_EQUALS(1, $container->data[0], "affected rows");
		
		
		
		$stm = new SQLStatementSelect( $data );
		$container = $stm->createResultContainer();
		$ds->queryStatement($stm, $container);
		
		$array = $container->getResult();
		
		TS_ASSERT_EQUALS( 2, count( $array ) );
		
		TS_ASSERT_EQUALS(1, intval($array[1]->data_id));
		TS_ASSERT_EQUALS($data->enum, $array[1]->enum);
		TS_ASSERT_EQUALS($data->string, $array[1]->string);
		TS_ASSERT_EQUALS($data->value, intval($array[1]->value));
		TS_ASSERT_EQUALS($data->date, $array[1]->date);
		TS_ASSERT_EQUALS($data->blob, $array[1]->blob);
		TS_ASSERT_EQUALS($data->real, $array[1]->real);
		
		TS_ASSERT_EQUALS(strftime( "%c", $data->date), strftime( "%c", $array[1]->date));

		$proto = new Data() ;
		
		$stm = new SQLStatementDelete( $proto  );
		$stm->setExpression( new ExprEQ( $proto->getPrimaryKeyTag(), 1));
		$container = $stm->createResultContainer();
		$ds->queryStatement($stm, $container);
		
		TS_ASSERT_EQUALS(1, $container->data[0], "affected rows");
		
		$stm = new SQLStatementSelect( $data );
		$container = $stm->createResultContainer();
		$ds->queryStatement($stm, $container);
		$array = $container->getResult();
		
	}
	
	function printResult( $pdoStm )
	{
		if ( !$pdoStm ) {
			print( "{ null }");
			return;	
		}
		foreach ($pdoStm as $row) {
			print_r($row);
		}		
	}
}

