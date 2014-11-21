<?php
require_once( DIR_MODULES . "/ADO/ADO.php");
require_once( __DIR__ .'/objects/schema.php');
require_once( __DIR__ . '/../Sqlite/PdoDataSource.php');

class CompositeDetails1 extends table_t_propertiesOne
{
}
class CompositeDetails2 extends table_t_propertiesTwo
{

}

class ConcreteComposite extends table_t_details
	implements IDBCompositeObject
{
	var $details1;
	var $details2;

	function __construct()
	{
		$this->details1 = new CompositeDetails1;
		$this->details2 = new CompositeDetails2;
	}

	function members()
	{
		return array(
			$this->details1,
			$this->details2);
	}
}

class SimpleConcreteComposite extends table_t_base
	implements IDBCompositeObject
{
	var $details1;
	var $details2;

	function __construct()
	{
		$this->details1 = new CompositeDetails1;
		$this->details2 = new CompositeDetails2;
	}

	function members()
	{
		return array(
			$this->details1,
			$this->details2);
	}
}

class testCompositeStorage extends PhpTest_TestSuite
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

	function testCreate()
	{
		$storage = new CompositeStorage( $this, new ConcreteComposite() );

		$object = new ConcreteComposite();
		$object->details1->set_propertiesOneData(20 );
		$object->details2->set_propertiesTwoData( 30 );
		$object->set_baseData( 10 );
		$object->set_detailsData( 40 );

		$storage->insert( $object);

		$this->assertEquals(1, $object->get_base_id());
		$this->assertEquals(1, $object->get_details_id());
		$this->assertEquals(1, $object->details1->get_propertiesOne_id());
		$this->assertEquals(1, $object->details2->get_propertiesTwo_id());
	}

	function testCreateSimple()
	{
		$storage = new CompositeStorage( $this, new SimpleConcreteComposite() );

		$object = new ConcreteComposite();
		$object->details1->set_propertiesOneData(20 );
		$object->details2->set_propertiesTwoData( 30 );
		$object->set_baseData( 10 );

		$storage->insert( $object);

		$this->assertEquals(1, $object->get_base_id());
		$this->assertEquals(1, $object->details1->get_propertiesOne_id());
		$this->assertEquals(1, $object->details2->get_propertiesTwo_id());
	}

	function testSelect()
	{
		$storage = new CompositeStorage( $this, new ConcreteComposite() );

		$this->testCreate();

		$stm = $storage->stmSelect();
		$stm->addExpression(new ExprEQ( $storage->proto()->getPrimaryKeyTag(), 1 ));

		$retrieved = $this->execute($stm);
		$this->assertEquals(1, count($retrieved));

		/** @var $retrievedObject ConcreteComposite */
		$retrievedObject = reset($retrieved);
		$this->assertEquals(1, $retrievedObject->get_base_id());
		$this->assertEquals(1, $retrievedObject->get_details_id());

		//auto fields
		$this->assertEquals(1, $retrievedObject->propertiesOne_id );
		$this->assertEquals(1, $retrievedObject->propertiesTwo_id );
		$this->assertEquals(1,  $retrievedObject->get_base_id() );
		$this->assertEquals(10, $retrievedObject->get_baseData() );
		$this->assertEquals(1,  $retrievedObject->get_details_id() );
		$this->assertEquals(40, $retrievedObject->get_detailsData() );
	}

	function testSelectSimple()
	{
		$storage = new CompositeStorage( $this, new SimpleConcreteComposite() );

		$this->testCreateSimple();

		$stm = $storage->stmSelect();
		$stm->addExpression(new ExprEQ( $storage->proto()->getPrimaryKeyTag(), 1 ));

		$retrieved = $this->execute($stm);
		$this->assertEquals(1, count($retrieved));

		/** @var $retrievedObject SimpleConcreteComposite */
		$retrievedObject = reset($retrieved);
		$this->assertEquals(1, $retrievedObject->get_base_id());

		//auto fields
		$this->assertEquals(1, $retrievedObject->propertiesOne_id );
		$this->assertEquals(1, $retrievedObject->propertiesTwo_id );
		$this->assertEquals(20, $retrievedObject->propertiesOneData );
		$this->assertEquals(30, $retrievedObject->propertiesTwoData );
		$this->assertEquals(1,  $retrievedObject->get_base_id() );
		$this->assertEquals(10, $retrievedObject->get_baseData() );
	}

	function testRead()
	{
		$storage = new CompositeStorage( $this, new ConcreteComposite() );

		$this->testCreate();

		$stm = $storage->stmSelect();
		$stm->addExpression(new ExprEQ( $storage->proto()->getPrimaryKeyTag(), 1 ));

		$retrieved = $this->execute($stm);
		/** @var $retrievedObject ConcreteComposite */
		$retrievedObject = reset($retrieved);

		$storage->assignMemberFields( $retrievedObject );

		//auto fields
		$this->assertEquals(1, $retrievedObject->details1->get_propertiesOne_id() );
		$this->assertEquals(1, $retrievedObject->details2->get_propertiesTwo_id() );

		$this->assertEquals(1,  $retrievedObject->get_base_id() );
		$this->assertEquals(10, $retrievedObject->get_baseData() );
		$this->assertEquals(1,  $retrievedObject->get_details_id() );
		$this->assertEquals(40, $retrievedObject->get_detailsData() );
		$this->assertEquals(20, $retrievedObject->details1->get_propertiesOneData() );
		$this->assertEquals(30, $retrievedObject->details2->get_propertiesTwoData() );
	}

	function testReadSimple()
	{
		$storage = new CompositeStorage( $this, new ConcreteComposite() );

		$this->testCreateSimple();

		$stm = $storage->stmSelect();
		$stm->addExpression(new ExprEQ( $storage->proto()->getPrimaryKeyTag(), 1 ));

		$retrieved = $this->execute($stm);
		/** @var $retrievedObject ConcreteComposite */
		$retrievedObject = reset($retrieved);

		$storage->assignMemberFields( $retrievedObject );

		//auto fields
		$this->assertEquals(1, $retrievedObject->details1->get_propertiesOne_id() );
		$this->assertEquals(1, $retrievedObject->details2->get_propertiesTwo_id() );

		$this->assertEquals(1,  $retrievedObject->get_base_id() );
		$this->assertEquals(10, $retrievedObject->get_baseData() );
		$this->assertEquals(20, $retrievedObject->details1->get_propertiesOneData() );
		$this->assertEquals(30, $retrievedObject->details2->get_propertiesTwoData() );
	}

	function testUpdate()
	{
		$storage = new CompositeStorage( $this, new ConcreteComposite() );
		$this->testCreate();

		$stm = $storage->stmSelect();
		$stm->addExpression(new ExprEQ( $storage->proto()->getPrimaryKeyTag(), 1 ));

		$retrieved = $this->execute($stm);
		$retrievedObject = reset($retrieved);
		$storage->assignMemberFields( $retrievedObject );


		/** @var $retrievedObject ConcreteComposite */
		$retrievedObject->set_detailsData(100);
		$retrievedObject->set_baseData(110);
		$retrievedObject->details1->set_propertiesOneData(120);
		$retrievedObject->details2->set_propertiesTwoData(130);

		$storage->update($retrievedObject);

		$retrieved = $this->execute($stm);
		$retrievedObject = reset($retrieved);
		$storage->assignMemberFields( $retrievedObject );

		$this->assertEquals(110, $retrievedObject->get_baseData() );
		$this->assertEquals(100, $retrievedObject->get_detailsData() );
		$this->assertEquals(120, $retrievedObject->details1->get_propertiesOneData() );
		$this->assertEquals(130, $retrievedObject->details2->get_propertiesTwoData() );
	}

	function testUpdateSimle()
	{
		$storage = new CompositeStorage( $this, new ConcreteComposite() );
		$this->testCreateSimple();

		$stm = $storage->stmSelect();
		$stm->addExpression(new ExprEQ( $storage->proto()->getPrimaryKeyTag(), 1 ));

		$retrieved = $this->execute($stm);
		$retrievedObject = reset($retrieved);
		$storage->assignMemberFields( $retrievedObject );


		/** @var $retrievedObject ConcreteComposite */
		$retrievedObject->set_baseData(110);
		$retrievedObject->details1->set_propertiesOneData(120);
		$retrievedObject->details2->set_propertiesTwoData(130);

		$storage->update($retrievedObject);

		$retrieved = $this->execute($stm);
		$retrievedObject = reset($retrieved);
		$storage->assignMemberFields( $retrievedObject );

		$this->assertEquals(110, $retrievedObject->get_baseData() );
		$this->assertEquals(120, $retrievedObject->details1->get_propertiesOneData() );
		$this->assertEquals(130, $retrievedObject->details2->get_propertiesTwoData() );
	}
}