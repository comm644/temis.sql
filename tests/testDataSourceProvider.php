<?php
require_once( dirname( __FILE__ ). "/../IDataSource.php" );
require_once( dirname( __FILE__ ). "/../DataSourceFactory.php" );


class MyDataSource extends IDataSource
{
	var $_dsn;
	
	function connect($dsn)
	{
			$this->_dsn = $dsn;
	}

	function getDSN()
	{
		return( $this->_dsn );
	}
}

class MyClonable extends Clonable
{
}

class testDataSourceFactory extends PhpTest_TestSuite
{

	/** this test case show how use Data Source Provider in your programm.

	this class provides \b Factory  for creating DataSource object.
	and you can create object instance in \b uiPage constructor
	bacause object does not have internal state.
	 */
	function testUsage()
	{
		$proto = new MyDataSource();
		
		$dsn = "mydsn://localhost/mydatabase";
		
		$dsf = new DataSourceFactory( $dsn, $proto);

		$ds = $dsf->getConnection();

		TS_ASSERT_EQUALS( get_class( $proto ), get_class( $ds ) );
		TS_ASSERT_EQUALS( $dsn, $ds->getDSN() );
	}

	function xtestError_clonable()
	{
		$dsn = "mydsn://localhost/mydatabase";
		
		$dsf = new DataSourceFactory( $dsn, new MyClonable());
		
		
	}
}
?>