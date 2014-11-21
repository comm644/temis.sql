<?php
require_once( DIR_MODULES . "/funcset/copy_members.php" );
require_once( DIR_MODULES . "/debug/debug.php" );


class MockQueryAnswer
{
	var $rc;
	var $lastInsertID;
	function MockQueryAnswer( $rc, $lastInsertID = 1)
	{
		$this->rc = $rc;
		$this->lastInsertID = $lastInsertID;
	}
}


class MockDataSource extends DBDataSource
{
	var $signShow;
	
	
	
	
	function MockDataSource()
	{
		$this->answer = array();
		$this->query = array();
		$this->signShow = false;
		$this->lastInsertID = 1;
		$this->nRequest = 0;
		$this->request = array();
	}
	function getGenerator()
	{
		return new SQLGenerator( new SQLDic());
	}
	function getLogger()
	{
		return DataSourceLogger::getInstance();
	}
	
	function isLinkAvailable()
	{
		return true;
	}
	
	function register()
	{
		$this->nRequest++;
		$trace = debug_backtrace();
		$info = $trace[1];
		if ( $this->signShow ) {
			$msg = sprintf( "call: %s::%s \nargs: %s",
				$info["class"], $info["function"], dumpvar($info["args"]));
			printd( $msg );
		}
		array_push( $this->request, $trace[1] );
	}
	function querySelect( $query, &$dataset, $class=null, $signUseID = true )
	{
		if ( $this->signShow ) print_r( $query );
		$this->query[] = $query;
		
		$answer = array_shift( $this->answer );
		$dataset =array();
		TS_ASSERT( is_array( $answer ), "Answer item should be array in [{$this->nRequest}] for $query" );
		$row=0;
		foreach( $answer as  $id => $item ) {
			if ( !is_object( $class )) {
				Diagnostics::error( "invalid argument 'class'");
			}
			$obj = clone $class;
			if ( strtolower( get_class( $obj ) ) != "stdclass" ) {
				TS_ASSERT( is_array( $item ), "Answer item should be array in [{$this->nRequest}:$row] for $query" );
				if ( count( get_object_vars( $obj ) ) != count( $item ) ) {
					TS_ASSERT_EQUALS( count( get_object_vars( $obj ) ), count( $item ), "The answer does not have members for object fill in [query=#{$this->nRequest} row=#$row query='$query'], count of members:"  );
					Diagnostics::printd("expected object:");
					Diagnostics::printd( $obj );
					Diagnostics::printd( "programmed answer:" );
					Diagnostics::printd( $item );
				}
				copy_from_array( $obj, $item );
			}
			else {
				$obj = array_to_object( $item );
			}
			$dataset[$id] = $obj;
			$row++;
		}
		$this->register();
		return( 0 );
	}
	/**
	 * query select 
	 *
	 * @param unknown_type $stm
	 * @param DBResultContainer  $result
	 * @param unknown_type $class
	 * @param unknown_type $signUseID
	 * @return unknown
	 */
	function querySelectEx( $query, &$result )
	{
		if ( $this->signShow ) print_r( $query );
		$this->query[] = $query;
		
		$answer = array_shift( $this->answer );
		TS_ASSERT( is_array( $answer ), "Answer item should be array in [{$this->nRequest}] for $query" );
		$row=0;
		
		foreach( $answer as $row ) {
		
			$obj = $result->fromSQL($row);
			$result->add($obj);
			$row ++;
		}
		return 0;
	}
	
	
	
	function queryCommand( $query, &$resultContainer )
	{
		$rc = true;
		if ( $this->signShow ) printd( $query );
		$this->query[] = $query;

		
		if ( !is_array( $this->answer ) ) {
			TS_ASSERT( false, " in [{$this->nRequest}: ]" );
			TS_TRACE( "Answers for MockDataSource is not defined. should be Array" );
		}
		
		$answer = array_shift( $this->answer );
		if ( !is_object( $answer ) ) {
			TS_ASSERT( false, "Answers for MockDataSource is not defined." );
			TS_TRACE( "Answers for MockDataSource is not defined." );
		}
		else {
			$this->lastInsertID = $answer->lastInsertID;
			$rc = $answer->rc;
		}
		$this->register();
		return( $rc );
	}
	function lastID()
	{
		return( $this->lastInsertID );
	}
	function escapeString( $str )
	{
		return $str;
	}
	function templateQuery( $filename, $vars, &$result, $signUseID=false, $class=null )
	{
		$rc = 0;
		$this->register();
		if ( !is_array( $this->answer ) || count( $this->answer ) == 0) {
			TS_ASSERT( false, " in [{$this->nRequest}] for $filename" );
			printd( $vars );
			TS_TRACE( "Answers for MockDataSource is not defined. should be Array" );
		}

		$result = array_shift( $this->answer );
		return 0;
	}
	function getDateTime( $val )
	{
		return( strftime( "%Y-%m-%d %H:%M:%S", $val ) );
	}


	function addTemplateValues( $values, $rc=0 )
	{
		$this->answer[] = array( array( "values"=>$values, "rc"=>$rc ) );
	}

	/**
	 * Retrieve eroror message or code from DB engine (mysql/...)
	 * @return string error message
	 * @access protected
	 */
	function  getEngineError()
	{
		// TODO: Implement getEngineError() method.
	}

	/**
	 * Gets DB engine name.
	 * @return string engine name (mysql, or sqlite..)
	 * @access protected
	 */
	function getEngineName()
	{
		// TODO: Implement getEngineName() method.
	}

	/** method performs connecting to data base
	 * @param $dsn \b string describes Data Source Name  as next string:
	engine_name://username:password@server[:port]/database
	 * @return error code as \b enum specified for concrete data source
	 */
	function connect($dsn)
	{
		// TODO: Implement connect() method.
	}


}

?>