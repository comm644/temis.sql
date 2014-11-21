<?php
require_once( dirname( __FILE__ ) . "/../funcset/DSN.class.php" );

if ( 0 ) {

/**
 * Parse DSN (Data source name) and provice methods for reading received DSN
 *
 *  DSN object provides universal way to describe connect string for connecting to data source.
 * 
 * for client-server DBMS DSN uses as :  
 *   dbmsengine://server/database                     - connect to 'database' w/o username and password
 *   dbmsengine://username:password@server/database       - connect to 'database' on server with specified usernmae and password
 *   dbmsengine://server/database/table               - connect to 'database' and select some table (URI to concretee table)
 * 
 * for non-server uses as :
 *   dbmsengine://localhost/?database=local/path/to/database.db
 *      - open 'database.db' located in 'local/path/to/'
 * 
 *   dbmsengine://localhost/?database=local/path/to/database.db&table=t_table   
 *      - open database.db, 
 *      - located in local/path/to/
 *      - select concrete table 't_table'
 *      
 * 
 * The main idea in presentation DSN as URI , 
 * you can use URI notation for locating any database object in your system, 
 * with using additional arguments for DBMS you can extends syntax.
 *
 *  dbms://server/database/table/primary_key/pkvalue&connectparam=value
 *  
 * Notice: server query argument names:  'database', 'table'
 *  
 * Examples:
 * 
 * Task: URI to signle object by primary key (in table 't_table' select object with id=1):
 * 
 * mysql://localhost/database/t_table?select='id=1'
 *  - URL via query
 * 
 * mysql://localhost/database/t_table/id/1
 *  - URI as hierachy
 *  
 * sqlite://localhost/?database=../local/database.db&table=t_table&&select='id=1'
 * - DSN for non-server DBMS can't accept only URI beacause need describe path to DB file on filesystem.
 * 
 * sqlite://localhost//t_table/id/1&database=../local/database.db
 * - combined way: URI as hierarhy and database file name in query
 * 
 */
class DSN
{
	var $params = array();
	var $dsn = null;
	var $isValid = false;
	
	/**
	 * construct object
	 *
	 * @param string  $dsn  methods://user:password@server:port/database/table[query]
	 * @return DSN
	 */
	function DSN( $dsn)
	{
		$this->dsn = $dsn;
		$this->isValid = $this->_parse( $dsn );
	}
	
	/**
	 * returns methos or null
	 *
	 * @return string
	 */
	function getMethod()
	{
		if ( !array_key_exists( "scheme", $this->params ) ) return null;
		return $this->params[ "scheme"];
	}
	
	/**
	 * returns username or null
	 *
	 * @return string
	 */
	function getUsername()
	{
		if ( !array_key_exists( "user", $this->params ) ) return null;
		return $this->params[ "user"];
	}
	/**
	 * returns password or null
	 *
	 * @return string
	 */
	function getPassword()
	{
		if ( !array_key_exists( "pass", $this->params ) ) return null;
		if ( !$this->params['pass'] ) return null;
		return $this->params[ "pass"];
	}
	
	/**
	 * returns database name or null 
	 *
	 * @return string
	 */
	function getDatabase()
	{
		if ( !array_key_exists( "database", $this->params ) ) return null;
		return $this->params[ "database"];
	}
	
	/**
	 * returns table name or null
	 *
	 * @return unknown
	 */
	function getTable()
	{
		if ( !array_key_exists( "table", $this->params ) ) return null;
		return $this->params[ "table"];
	}
	
	/**
	 * returns host or null
	 *
	 * @return string
	 */
	function getHost()
	{
		if ( !array_key_exists( "host", $this->params ) ) return null;
		return $this->params[ "host"];
	}
	
	/**
	 * returns port number or null
	 *
	 * @return integer
	 */
	function getPort()
	{
		if ( !array_key_exists( "port", $this->params ) ) return null;
		if ( is_null( $this->params[ "port"])) return null;
		return intval($this->params[ "port"]);
	}
	
	/**
	 * Return TRUE if 'port' was specified in DSN
	 *
	 * @return bool
	 */
	function isPortSpecified()
	{
		return $this->getPort() != null;
	}

	/**
	 * Gets specified params.
	 *
	 * @return array  array of params specivied via query part of URI
	 */
	function getParams()
	{
		if (!array_key_exists('params', $this->params)) return array();
		return $this->params;
	}

	/**
	 * Retrun 'true' if item selector specified.
	 *
	 * @return bool
	 */
	function isItemSelectorSpecified()
	{
		return array_key_exists('column_name', $this->params);
	}
	
	/**
	 * Gets item selctor if specified.
	 *
	 * returns array with keys for creating select query:
	 * 
	 * \li table   - table name where need select item
	 * \li column  - primary key column name
	 * \li value   - primary key column value
	 * 
	 * @return array accosiative array
	 */
	function getItemSelector()
	{
		if ( !$this->isItemSelectorSpecified()) return array();
		
		return array(
			'table' => $this->getTable(),
			'column'  => $this->params['column_name'],
			'value'  => $this->params['column_value']
		);
	}
	
	/**
	 * true if received DSN is valid
	 *
	 * @return bool
	 */
	function isValid()
	{
		return $this->isValid;
	}

	function _convertURI()
	{
		return true;
	}
	/**
	 @return bool sign of valid url
	 */
	function _parse( $url )
	{
		$parts = explode( "//", $url );
		if ( count( $parts ) == 1 ) return false;
		if ( $parts[1] == "" ) return false;
		
		$this->params = parse_url( $url );
		
		
		if ( !array_key_exists( "path", $this->params ) ) return false;

		$parts = explode( "/", $this->params["path"] );

		if ( !array_key_exists( 1, $parts ) ) return false;

		if ( !array_key_exists( "pass",$this->params ) ) {
			$this->params["pass"] = "";
		}
		
		$this->params["database"] = $parts[1];
		if ( count( $parts ) > 2 ) {
			$this->params["table"]    = $parts[2];
			
			if (count( $parts ) == 5  ) {
				//primary key and value
				$this->params["column_name"] = $parts[3];
				$this->params["column_value"] = $parts[4];
			}
		}
		else {
			$this->params["table"]    = "";
		}
		
		if ( !array_key_exists( "port", $this->params ) ) {
			$this->params["port"]=null;
		}

		
		//for non server databases
		if (array_key_exists('query', $this->params)) {
			$args = array();
			parse_str($this->params['query'], $args);
			$this->params['params'] = $args;
			
			if ( array_key_exists('database', $args)) {
				$this->params['database'] = $args['database'];
			}
			
			if ( array_key_exists('table', $args)) {
				$this->params['table'] = $args['table'];
			}
		}
		
		return true;
	}
}

}