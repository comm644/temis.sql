<?php
require_once( DIR_MODULES . "/ADO/ADO.php");
require_once( __DIR__ .'/objects/schema.php');
require_once( __DIR__ . '/../Sqlite/PdoDataSource.php');


/**
 *  Base1 -> Base2 -> Details
 *
 *  active record ??
 */
class BaseClass  extends table_t_base
{
}
class DetailsClass extends table_t_details
{
}
class SubDetails extends table_t_subdetails
{

}

class testExtendsObjectStorage extends PhpTest_TestSuite
	implements IStatementRunner
{
	/**
	 * @var PdoDataSource
	 */
	public $ds;

	function setUp()
	{
		$file = dirname( __FILE__ ) . "/database.db";
		if ( file_exists( $file ) ) unlink( $file );

		$ds = new PdoDataSource();
		$ds->connect("sqlite://localhost/?database=$file");

		$ds->beginTransaction();

		$sql= file_get_contents( dirname( __FILE__ ) ."/objects/schema.sqlite" );
		$container = new DBResultContainer();

		$ds->queryCommand($sql, $container);

		$this->ds = $ds;
	}
	function tearDown()
	{
		unset( $this->ds );
	}

	function testInsert()
	{
		$stor = new ExtendedObjectsStorage($this, new DetailsClass());

		//construct object
		$obj = new DetailsClass();

		$obj->set_detailsData( 10 );
		$obj->set_baseData( 20 );


		$stor->insert($obj);

		$this->assertEquals(1, $obj->get_base_id());
		$this->assertEquals(1, $obj->get_details_id());

		$proto = new DetailsClass();

		$objs = $this->execute($stor->stmSelect() );
		$this->assertEquals(1, count( $objs));

		/** @var $obj DetailsClass */
		$obj = reset($objs);
		$this->assertEquals(1, $obj->get_base_id());
		$this->assertEquals(1, $obj->get_details_id());
		$this->assertEquals(20, $obj->get_baseData());
		$this->assertEquals(10, $obj->get_detailsData());


		$obj->set_baseData(30);
		$obj->set_detailsData(40);
		$stor->update($obj);

		$objs = $this->execute($stor->stmSelect() );
		$obj = reset($objs);
		$this->assertEquals(30, $obj->get_baseData());
		$this->assertEquals(40, $obj->get_detailsData());
	}

	function testCreateSubDetails()
	{
		$stor = new ExtendedObjectsStorage($this, new SubDetails());

		//construct object
		$obj = new SubDetails ();

		$obj->set_baseData( 10 );
		$obj->set_detailsData( 20 );
		$obj->set_subDetailsData(30);

		$stor->insert($obj);

		$objs = $this->execute($stor->stmSelect() );
		$this->assertEquals(1, count( $objs));
		/** @var $obj SubDetails */
		$obj = reset($objs);

		$this->assertEquals(10, $obj->get_baseData());
		$this->assertEquals(20, $obj->get_detailsData());
		$this->assertEquals(30, $obj->get_subDetailsData());
	}
	function testListSubdetails()
	{
		$this->testCreateSubDetails();

		$stor = new ExtendedObjectsStorage($this, new SubDetails());
		$objs = $this->execute($stor->stmSelect() );
		$this->assertEquals(1, count( $objs));
		/** @var $obj SubDetails */
		$obj = reset($objs);

		$this->assertEquals(10, $obj->get_baseData());
		$this->assertEquals(20, $obj->get_detailsData());
		$this->assertEquals(30, $obj->get_subDetailsData());
	}

	function testUpdateSubDetails()
	{
		$this->testCreateSubDetails();

		$stor = new ExtendedObjectsStorage($this, new SubDetails());

		$objs = $this->execute($stor->stmSelect() );
		/** @var $obj SubDetails */
		$obj = reset($objs);

		$obj->set_baseData( 110 );
		$obj->set_detailsData( 120 );
		$obj->set_subDetailsData(130);

		$stor->update($obj);

		$objs = $this->execute($stor->stmSelect() );
		/** @var $obj SubDetails */
		$obj = reset($objs);

		$this->assertEquals(110, $obj->get_baseData());
		$this->assertEquals(120, $obj->get_detailsData());
		$this->assertEquals(130, $obj->get_subDetailsData());
	}

	function execute(SQLStatement $stm)
	{
		$res = $stm->createResultContainer(true);
		$this->ds->queryStatement($stm, $res);

		$lastID = -1;
		if ( get_class( $stm) == CLASS_SQLStatementInsert) {
			$lastID = $this->ds->lastID();
		}
		if ( $lastID != -1 ) {
			return $lastID;
		}
		return $res->getResult();
	}
}