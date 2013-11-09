<?php
if( !defined( "__ADO_DATASOURCE_PHP_DIR__" ) ) define(  "__ADO_DATASOURCE_PHP_DIR__", dirname( __FILE__ ) . '/../' );

require_once( __ADO_DATASOURCE_PHP_DIR__ . "/DBDefaultResultContainer.php");
require_once( __ADO_DATASOURCE_PHP_DIR__ . "/DataSourceLogger.php");
require_once( __ADO_DATASOURCE_PHP_DIR__ . "/DBDataSource.php");
require_once( __ADO_DATASOURCE_PHP_DIR__ . "/Sqlite/SqliteDictionary.php");
require_once( __ADO_DATASOURCE_PHP_DIR__ . "/Sqlite/SqliteGenerator.php");
require_once( __ADO_DATASOURCE_PHP_DIR__ . "/DSN.class.php");

define( "SQLITE_LOWALPHA",  "абвгдеёжзийклмнорпстуфхцчшщъьыэюя");
define( "SQLITE_HIALPHA",   "АБВГДЕЁЖЗИЙКЛМНОРПСТУФХЦЧШЩЪЬЫЭЮЯ");

function sqlite_ru_upper($string)
{
	$string = strtr($string, SQLITE_LOWALPHA,SQLITE_HIALPHA);
	$rc = strtoupper($string );
	return $rc;
}


class PdoDataSource extends DBDataSource
{
/**
 * Link object (PDO)
 *
 * @var PDO
 */
	private $link;
	
	private $preparedStm;
	
	function __construct() {
		$this->preparedStm= array();
		
	}
	public function __destruct() {
		if ($this->link->inTransaction()) {
			$this->link->commit();
		}
		$this->link = null;
	}

		/**
	 * connect to database
	 *
	 * @param string $connectString DSN  such as: "sqlite://localhost//?database=path/to/database.db"
	 * @return integer  DS_SUCCESS or error
	 */
	function connect( $connectString )
	{
		$logger = $this->getLogger();
		$logger->debug("connecting to $connectString");
		$this->setConnectString( $connectString );

		$dsn = 	new DSN( $connectString );

		$method = $dsn->getMethod();
		if ( $method  != "sqlite" ) {
			return $this->errorMethodNotSupported($method );
		}

		$file= $dsn->getDatabase();

		// Connecting, selecting database
		try {
			$this->link = new PDO( "sqlite:$file", "");
//			$this->link = new PDO( "sqlite:$file", "", "", array(PDO::ATTR_PERSISTENT => true));
			$this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//$this->link->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE );
			//$this->link->setAttribute(PDO::ATTR_PERSISTENT , false);

		}
		catch( PDOException $exception  )
		{
			$this->registerError( "", "Can't connect to '{$file}':{$exception->getMessage()} " );
			return( DS_CANT_CONNECT );
		}

		$rc = $this->link->sqliteCreateFunction('ru_upper', 'sqlite_ru_upper');
		if ( $rc === FALSE ) {
			$logger->warning('Cany create collation for ru_RU');
		}
		return( DS_SUCCESS );
	}

	function getEngineError ()
	{
		$info = $this->link->errorInfo();
		if ( $info[0] == '00000') return 'Success.';
		return $info[2];
	}

	function getEngineName ()
	{

		return ("PDO(sqlite)");
	}
	function isLinkAvailable()
	{
		return $this->link != null;
	}

	/**
	 * returns SQL geenrator.
	 *
	 * @return SQLGenerator
	 */
	function getGenerator()
	{
		return new SqliteGenerator();
	}

	/**
	 * Gets logger intsance
	 * @return DataSourceLogger
	 */
	function getLogger()
	{
		return  DataSourceLogger::getInstance();
	}
	/** query "SELECT" to container
	 @param string $query     SQL query
	 @param DBResultcontainer |DBDefaultResultContainer|SQLStatementREsultContainer
	 $resultcontainer  contaner stategy
	 @return integer zero on success
	 @see DBResultcontainer
	 @see DBDefaultResultContainer
	 */
	function querySelect( $query, &$resultContainer )
	{
		$resultStm = $this->link->query( $query.";", PDO::FETCH_CLASS, "stdclass" );
		$this->_processSelectResult($resultStm, $resultContainer);
	}

	private function _processSelectResult($resultStm, &$resultContainer)
	{
		if ( !$resultStm) {
			Diagnostics::error("PDO query error: {$this->getEngineError()}\nQuery: $query ");
		}
		foreach( $resultStm->fetchAll(PDO::FETCH_NUM) as $row ) {
			$obj = $resultContainer->fromSQL( $row );
			$resultContainer->add( $obj );
		}
		return 0;
	}


	function querySelectEx( $query, &$resultContainer )
	{
		return $this->querySelect($query, $resultContainer);
	}

	/** query "INSERT/UPDATE/DELETE"
	 @param string $query SQL query
	 @param DBResultcontainer|DBDefaultResultContainer|SQLStatementREsultContainer
	 $resultcontainer   contaner stategy. method must returns count for affected rows as result set
	 @return integer  0 on success or error code
	 */
	function queryCommand( $query, &$resultContainer )
	{
		try  {
			$rowsAffected = $this->link->exec($query.";");
		}
		catch( PDOException $e ) {
			print( $query );
			throw $e;
		}

		$resultContainer->add( $rowsAffected );
		return 0;
	}



	/**
	 * Execute statement.
	 *
	 * This method cabe overriden in inherit class for special procesing statements.
	 *
	 * @access protected.
	 * @param SQLStatement|SQLStatementSelect|SQLStatementInsert|SQLStatementUpdate  $stm
	 @param DBResultcontainer|DBDefaultResultContainer|SQLStatementResultContainer
	 $resultcontainer  contaner stategy
	 */
	protected function _executeStatement($stm, &$resultcontainer)
	{
	//process query

		$generator = $this->getGenerator();

		$class = get_class( $stm );
		switch( $class )
		{
			case CLASS_SQLStatementSelect:
				$pdoStm = $this->_preparePdoStatement($stm);
				$pdoStm->execute();
				$rc = $this->_processSelectResult($pdoStm, $resultcontainer);
				break;
			case CLASS_SQLStatementDelete:

				$sql = $generator->generate($stm);
				$pdoStm = $this->link->prepare($sql.";");
				if (! $pdoStm) {
					Diagnostics::error("Can't prepare statement: $sql\nError: " . $this->getEngineError());
				}

				$rc = $pdoStm->execute();
				$rowsAffected = $pdoStm->rowCount();
				$resultcontainer->add( $rowsAffected );
				break;

			case CLASS_SQLStatementUpdate:
			case CLASS_SQLStatementInsert:

				$pdoStm = $this->_preparePdoStatement($stm);
				$rc = $pdoStm->execute();
				$rowsAffected = $pdoStm->rowCount();
				$pdoStm->closeCursor();

				$resultcontainer->add( $rowsAffected );
				break;
			default:
				Diagnostics::error("Don't know how to execute  $class");
		}
		return $rc;
	}

	function _preparePdoStatement($stm)
	{
		$generator = $this->getGenerator();
		$query = $generator->generateParametrizedQuery($stm);
		$sql   = $query->getQuery();

		if ( $this->signShowQueries ) {
			print_r( $query );
		}
		if (!array_key_exists($sql, $this->preparedStm)) {
			$pdoStm =  $this->link->prepare($sql .";");	
			$this->preparedStm[$sql] = $pdoStm;
		}
		else 
		{
			$pdoStm = $this->preparedStm[$sql];
		}
		
		if ( !$pdoStm ) {
			Diagnostics::error("Can't prepare statement: $sql\nError: "
				. $this->getEngineError()
				. 'STM: ' . Diagnostics::dumpVar($stm));
		}
		foreach( $query->getParameters() as $param ){

			$rc = $pdoStm->bindValue( $param->name, $param->value, $this->_getPdoType( $param->type ) );

			if ( $rc === FALSE ) {
				Diagnostics::error("Can't bind value to statement: $sql\nError: " . $this->getEngineError());
			}
		}
		return $pdoStm;
	}

	/**
	 * Get PDO type from Temis.ADO type
	 *
	 * @param string $sqlType  Temis.ADO type
	 * @access private
	 */
	private function _getPdoType( $sqlType )
	{
		$map = array(
			DBParamType_integer => PDO::PARAM_INT,
			DBParamType_string => PDO::PARAM_STR,
			DBParamType_lob => PDO::PARAM_LOB,
			DBParamType_bool => PDO::PARAM_INT,
			DBParamType_null => PDO::PARAM_NULL,
			DBParamType_real => PDO::PARAM_STR
		);

		if ( !array_key_exists($sqlType, $map)) return PDO::PARAM_STR;
		return $map[$sqlType];
	}

	/**
	 * Gets last intsert ID.
	 * @return integer
	 * @access public
	 */
	function lastID()
	{
		assert($this->link != null);
		return intval($this->link->lastInsertId());
	}

	function inTransaction()
	{
		return $this->link->inTransaction();
	}
	
	function beginTransaction()
	{
		$this->link->beginTransaction();
	}
	function commitTransaction()
	{
		$this->link->commit();
	}
}


?>