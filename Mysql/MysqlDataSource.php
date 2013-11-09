<?php
/******************************************************************************
 Module : database proxy
 File :   DataSource.php
 Author : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description:
******************************************************************************/
if( !defined( "__ADO_DATASOURCE_PHP_DIR__" ) ) define(  "__ADO_DATASOURCE_PHP_DIR__", dirname( __FILE__ ) . "/../" );

define(  "__ADO_MYSQL_DIR__", dirname( __FILE__ ) . "/" );

require_once( __ADO_DATASOURCE_PHP_DIR__ . "/DSN.class.php");

require_once( __ADO_DATASOURCE_PHP_DIR__ . "/DBDefaultResultContainer.php");
require_once( __ADO_DATASOURCE_PHP_DIR__ . "/DataSourceLogger.php");
require_once( __ADO_DATASOURCE_PHP_DIR__ . "/DBDataSource.php");

require_once __ADO_MYSQL_DIR__ . 'MysqlDictionary.php';
require_once __ADO_MYSQL_DIR__ . 'MysqlGenerator.php';


if (!defined( 'DS_MSG_NOLINK')) {
	define( "DS_MSG_NOLINK", "Link is not actived" );
}

define( "SUPPRESS_ERROR_NONE", 0 );
define( "SUPPRESS_ERROR_SINGLE", 1 );
define( "SUPPRESS_ERROR_PERSISTENT", 2 );

//define( "DIR_SQLCACHE", dirname( __FILE__ ) . "/../sql.cache/" );

class MysqlDataSource extends DBDataSource
{
	var $link = NULL;
	var $lastError = NULL;
	var $timefunc = "time";
	var $signSuppressError = false;
	var $signShowQueries = false;
	var $signDebugQueries = true;
	var $connectString = "";
	var $signUseCache = false;
	var $signUseNames = true;
	var $signDualAccess = false;  //sign about dual access to single server
	var $resultcontainer = null;


	/**
	 * connect to database
	 *
	 * @param string $cn DSN  such as: "mysql://user:password@host/database/table" 
	 * @return integer  DS_SUCCESS or error
	 */
	function connect( $connectString )
	{
		$this->connectString = $connectString;
		
		$dsn = new DSN( $connectString );
		if ( $dsn->getMethod() != "mysql" ) {
			return $this->errorMethodNotSupported($dsn->getMethod() );
		}
		
		// Connecting, selecting database
		$port = ( $dsn->getPort() != "" ) ? ":" . $dsn->getPort() : "";
		$this->link = @mysql_connect( $dsn->getHost() . $port, $dsn->getUsername(), $dsn->getPassword());
		if ( !$this->link ) {
			$this->registerError( "", "Can't connect to '{$dsn->getHost()}' as '{$dsn->getUsername()}'" . '(' .mysql_error() .')' );
			return( DS_CANT_CONNECT );
		}

		$this->registerHost( $dsn->getHost() .$dsn->getPort().$dsn->getDatabase(), $dsn->getTable());
		//echo 'Connected successfully';
		$this->database = $dsn->getDatabase();
		$rc = $this->selectDatabase();
		if ( $rc ) return( $rc );

		$rc = $this->setPacketSize();
		if ( $rc ) return( $rc );
		
		$this->setEncoding();
		DataSourceLogger::notice( "encoding:" . mysql_client_encoding( $this->link ) );
		return( DS_SUCCESS );
	}

	function selectDatabase()
	{
		$rc = @mysql_select_db( $this->database, $this->link );
		if ( !$rc ) {
			$this->registerError( "", "Can't Select database '{$this->database}': " .mysql_error()  );
			return( DS_CANT_SELECT_DB );
		}
		return( DS_SUCCESS );
	}

    /**
     * Set packet size for accepting big blobs
     * 
     * @return integer
     */
	function setPacketSize()
	{
		$rc = $this->queryCommand("SET max_allowed_packet=50000000");
		return DS_SUCCESS;
	}
	function setEncoding()
	{
		if ( !$this->signUseNames ) return;
        $saved = array( $this->signSuppressError, $this->signDebugQueries);
		$this->signSuppressError = true;
		$this->signDebugQueries = false;
	    $rc = $this->queryCommand( "SET NAMES 'utf8'" );

        list( $this->signSuppressError, $this->signDebugQueries) = $saved;
		if ( $rc ) {
			
			$this->signUseNames = false;
		}
	}

    /**
     * Returns last error.
     * 
     * @return string
     */
	function lastError( )
	{
		return( $this->lastError );
	}

	/**
	 * Disconnect from current datasource
	 *
	 * @return integer zero on success (always)
	 */
	function disconnect() {
		// Closing connection
		mysql_close($this->link);
		$this->link = null;
		return( 0 );
	}

	function suppressError( $sign=true )
	{
		$this->signSuppressError = $sign;
	}

	function resetError()
	{
		$this->lastError = null;
	}
		

	function registerError( $query, $appError = null )
	{
		if ( $appError ) $this->lastError = $appError;
		else $this->lastError = mysql_error($this->link);
		
		if ( $this->signDebugQueries ) {
			DataSourceLogger::warning( "MYSQL Error: {$this->lastError}" );
		}
		if ( $this->signSuppressError ) {
			if ($this->signSuppressError == SUPPRESS_ERROR_SINGLE || $this->signSuppressError === true ) {
				$this->signSuppressError = false;
			}
			return;
		}
		if ( !$appError ) {
			DataSourceLogger::warning( "in query: $query <br>\nMYSQL Error: $this->lastError" );
		}
	}
	
	/** query "SELECT"
	 @param $queryquery
	 @param $args array. return value
	 @param $classclass name for creating object
	 @returnFALSE if no errors
	*/
	function querySelect( $query, &$dataset, $class=null, $signUseID = true )
	{
		$result = new DBDefaultResultContainer($class, $signUseID);

		$rc = $this->querySelectEx( $query, $result );

		$dataset = $result->getResult();
		return $rc;
	}


	/** query "SELECT" to container
	 @param string $query     SQL query
	 @param DBResultcontainer|DBDefaultResultContainer|SQLStatementREsultContainer 
         $resultcontainer  contaner stategy 
	 @return integer zero on success
	 @see DBResultcontainer
	 @see DBDefaultResultContainer
	 */
	function querySelectEx( $query, &$resultcontainer )
	{
		$this->resetError();
		$resultcontainer->begin();
		
		if ( !$this->link ) { $this->registerError( "", DS_MSG_NOLINK ); return DS_ERROR; }
		
		if ( $this->signShowQueries ) print( $query . ";\n");
		if ( $this->signDebugQueries ) DataSourceLogger::debug( str_replace( array( "\n", "\r"), array( " ", " " ), $query ) . ";");
		if ( $this->signUseCache ) {
			$rc = $this->getFromCache( $query, $dataset );
			if ( !$rc ) return( 0 );
		}
		
		//set database
		$this->syncDSN();
		
		// Performing SQL query
		$result = mysql_query($query, $this->link);
		if ( $result == FALSE ) {
			$this->registerError( $query );
			return( 1 );
		}

		$nrow = 0;
		while ($line = mysql_fetch_object($result)) {
			$obj = $resultcontainer->fromSQL($line);
			$resultcontainer->add($obj);
			$nrow ++;
		}
		$resultcontainer->end();

		
		if ( $this->signDebugQueries ) DataSourceLogger::debug( "returned {$nrow} rows");

		if ( $this->signUseCache ) {
			$dataset = $resultcontainer->getResult();
			$this->putToCache( $query, $dataset );
		}
		
		// Free resultset
		mysql_free_result($result);
		return( 0 );
	}
	
	
	/**
	 * Execute SQL statement
	 *
	 * @param SQLStatementSelect|SQLStatementDelete|SQLStatementUpdate|SQLStatementInsert $stm
	   @param DBResultcontainer|DBDefaultResultContainer|SQLStatementResultContainer 
         $resultcontainer  contaner stategy 
	 * @return integer zero on success
	 * @see DBResultContainer
	 * @see SQLStatementSelectResult
	 * @see SQLStatementSelect::createResultContainer()
	 */
	function executeStatement( $stm, &$resultcontainer )
	{
		$class = get_class( $stm );
		switch( $class )
		{
			case CLASS_SQLStatementSelect:
				return $this->querySelectEx( $stm->generate(), $resultcontainer );
				
			case CLASS_SQLStatementDelete:
			case CLASS_SQLStatementUpdate:
			case CLASS_SQLStatementInsert:
				$rc= $this->queryCommand($stm->generate());
				$resultcontainer->begin();
				$resultcontainer->add(mysql_affected_rows());
				$resultcontainer->end();
				return $rc;
			default:
				Diagnostics::error("Don't know how to execute  $class");
		}
		return -1;
	}
	
	
	/** query "INSERT/UPDATE/DELETE"
	 @param $queryquery
	 @param $args array. return value
	 @param $classclass name for creating object
	 @return integer  0 on success or error code
	*/
	function queryCommand( $query, &$resultcontainer )
	{
		if ( !$this->link ) { $this->registerError( "", DS_MSG_NOLINK ); return DS_ERROR; }
		if ( $this->signShowQueries ) print( $query . ";\n");
		if ( $this->signDebugQueries ) DataSourceLogger::debug(str_replace( array( "\n", "\r"), array( " ", " " ), $query ) . ";");

		//set database, resource can be modified from other object
		$this->syncDSN();
		
		// Performing SQL query
		$result = mysql_query($query, $this->link);
		if ( $result === FALSE ) {
			$this->registerError( $query );
			return( true );
		}
		return( 0 );
	}


	/** execute simple SQL file : one line - one query (without splitted queries)

	for splitted queries use templateQuery()
	also this function no return result. used only for default fill databases in Unit tests
	*/
	function execFile( $filename )
	{
		if ( !$this->link ) { $this->registerError( "", DS_MSG_NOLINK ); return DS_ERROR; }
		
		$fd = fopen( $filename, "r" ) or die( "Can't open ". $filename );
		while( !feof( $fd ) ) {
			$line = fgets( $fd );
			if ( $line == "" ) continue;
			if ( strncasecmp( $line, "//", 2) == 0 ) continue;
			if ( strncasecmp( $line, "--", 2 ) == 0 ) continue;

			$query = trim( $line );
			if ( $query == "" ) continue;

			if ( strncasecmp( $query, "SELECT", 6 ) == 0 ) {
				$rc = $this->querySelect( $query, $values, null, FALSE );
			}
			else {
				$rc = $this->queryCommand( $query );
			}
			if ( $rc !== DS_SUCCESS ) {
				$this->lastQuery = $query;
				break;
			}
		}
		return $rc;
	}

	/** execute SQL file with apply template variables
	 */
	function templateQuery( $filename, $vars, &$result, $signUseID=false, $class=null )
	{
		if ( $this->signDebugQueries ) DataSourceLogger::debug( "Executing: $filename");
		if ( !$this->link ) { $this->registerError( "", DS_MSG_NOLINK ); return DS_ERROR; }
		$fd = fopen( $filename, "r" ) or die( "Can't open ". $filename );

		$query = "";
		$QueryNo = 0;
		$result= array();
		$signComment = 0;
		$rc = 0;
		while( !feof( $fd ) ) {
			$line = fgets( $fd );
			if ( $line == "" ) continue;
			if ( strncasecmp( $line, "//", 2) == 0 ) continue;
			if ( strncasecmp( $line, "--", 2 ) == 0 ) continue;

			if ( strstr( $line, "*/" ) != FALSE ) {
				$signComment = false;
				continue;
			}
			if ( $signComment ) continue;
			if ( strstr( $line, "/*" ) != FALSE ) {
				$signComment = true;
				continue;
			}

			$query .= " " . trim($line);
			if ( !strstr( $line, ";") ) continue;
			
			$query = trim( str_replace( array_keys( $vars ), array_values( $vars ), $query ) );
			unset( $values );

			if ( strncasecmp( $query, "SELECT", 6 ) == 0 ) {
				$rc = $this->querySelect( $query, $values, $class, $signUseID );
				array_push( $result, array( "query" => $query, "rc" => $rc, "values" => $values ) );
			}
			else {
				//insert. drop...
				$values = null;
				$rc = $this->queryCommand( $query );
				array_push( $result, array( "query" => $query, "rc" => $rc, "values" => $values ) );
			}
			if ( $rc ) break;
			$query = "";
		}
		fclose( $fd );

		return( $rc );
	}

	function lastID() {
		if ( !$this->link ) { $this->registerError( "", DS_MSG_NOLINK ); return -1; }
		return( mysql_insert_id( $this->link ) );
	}
	
	function getAffectedRows()
	{
		if ( !$this->link ) { $this->registerError( "", DS_MSG_NOLINK ); return -1; }
		return mysql_affected_rows($this->link);
	}

	function now()
	{
		$timefunc = $this->timefunc;
		return( DataSource::getDateTime( $timefunc() ) );
	}
	function getDateTime( $val )
	{
		return( strftime( "%Y-%m-%d %H:%M:%S", $val ) );
	}


	static function escapeString( $str )
	{
		return( mysql_real_escape_string( $str ) );
	}

	function getFromCache( $query, &$dataset )
	{
		$md5 = md5( $query );
		$filename = DIR_SQLCACHE. "/" . $md5;
		if ( file_exists( $filename ) ) {
			$dataset = unserialize( file_get_contents( $filename ) );
		}
		else {
			return( true );
		}
		debug_log( DLOG_USR, "retrieved from cache: " . $filename );
		return( false );
	}
	function putToCache( $query, &$dataset )
	{
		$md5 = md5( $query );
		$filename = DIR_SQLCACHE. "/" . $md5;
		file_put_contents( $filename, serialize( $dataset ) );
		debug_log( DLOG_USR, "saved to cache: " . $filename );
		return( false );
	}


	function registerHost($host, $tableName)
	{
		global $DataSourceHosts;
		if ( !isset( $DataSourceHosts ) ) {
			$DataSourceHosts = array();
		}

		if ( !array_key_exists( $host, $DataSourceHosts ) ){
			$obj = new stdclass;
			$obj->tableName = $tableName;
			$obj->dualAccess = false;

			$DataSourceHosts[ $host ] = $obj;
		}
		else if ( $DataSourceHosts[ $host ]->tableName != $tableName) {
			$DataSourceHosts[ $host ]->dualAccess = true;
		}
		$this->host = $host;
		$this->signDualAccess = true;
	}

	function syncDSN()
	{
		if ( $this->signDualAccess ) {
			$this->selectDatabase();
		}
	}
	
	function getErrorString( $rc )
	{
		switch( $rc )
		{
			case DS_SUCCESS: return "Success";
			case DS_CANT_CONNECT: return "Can't connect";
			case DS_METHOD_DOESNT_SUPPORTED: return "Method doesn't supported";
			case DS_CANT_SELECT_DB: return "Can't select database";
			default: return "Unknown code $rc";
		}
	}

 	/**
	 * Gets SQLGenerator according to implemented SQL engine.
	 * @return SQLGenerator
	 * @access protected
	 */
	function getGenerator()
	{
		return new MysqlGenerator();
	}

	/**
	 * Gets logger intsance
	 * @return DataSourceLogger
	 */
	function getLogger()
	{
		return  DataSourceLogger::getInstance();
	}
	function isLinkAvailable()
	{
		return $this->link != null;
	}
};

class Mysql51DataSource  extends MysqlDataSource
{
	function setEncoding()
	{
        if ( function_exists("mysql_set_charset") ) {
            $rc = mysql_set_charset ("utf8", $this->link );
            if ( $rc === false ) {
                $this->registerError('mysql_set_charset()' );
            }
        }
	else {
		parent::setEncoding();
	}
	}

    /**
     * Set packet size for accepting big blobs
     *
     * @return integer
     */
	function setPacketSize()
	{
		//$rc = $this->queryCommand("SET GLOBAL max_allowed_packet=50000000");
		return DS_SUCCESS;
	}
}

?>
