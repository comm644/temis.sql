<?php

/**
 * Class SimpleStatementRunner provides simple strategy to executing SQL statements.
 */
class SimpleStatementRunner implements IStatementRunner
{
	private $connection;

	function __construct(IDataSource $connection )
	{
		$this->connection = $connection;
	}

	/**
	 * Execute statement and return result as return value.
	 *
	 * @param SQLStatement $stm  Statement.
	 * @return array|DBObject|int  result set of objects or last ID.
	 */
	function execute(SQLStatement $stm)
	{
		if ( !$stm ) {
			return array();
		}
		$container = $stm->createResultContainer(true);
		$this->connection->queryStatement($stm, $container);

		$lastID = -1;
		if ( get_class( $stm) == CLASS_SQLStatementInsert) {
			$lastID = $this->connection->lastID();
			$pkname = $stm->object->primary_key();
			$stm->object->$pkname = $lastID;
		}
		if ( $lastID != -1 ) {
			return $lastID;
		}
		return $container->getResult();
	}

	/**
	 * Execute select statement and return only first value.
	 *
	 * @param IDataSource $db  data source.
	 * @param SQLStatementSelect $stm  select statement
	 * @return DBOBject
	 */
	function executeSelectOne( SQLStatementSelect $stm)
	{
		$objs = $this->execute($stm);
		return array_shift( $objs );
	}
}