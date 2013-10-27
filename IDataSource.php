<?php
require_once( dirname( __FILE__ ). "/Clonable.php" );

  /**

  \brief object oriented data source access interface

  This inteface profviced object oriented access to datasources and
  performs executing SQLStatements only without direct queries
  
   */
class IDataSource extends Clonable
{

	/** method performs connecting to data base

	@param $dsn \b string describes Data Source Name  as next string:
	     engine_name://username:password@server[:port]/database

	@return error code as \b enum specified for concrete data source	 
	 */
	function connect($dsn)
	{
		Diagnostics::error( "method was not redefined" );
	}

	/** execute SQLstatement

	@param $statement SQLStatement object
	@return success code 
	 */
	function queryStatement( $statement, &$resultContainer )
	{
		Diagnostics::error( "method was not redefined" );
	}
}


define( "CLASS_IDataSource", get_clasS( new IDataSource() ) );
?>