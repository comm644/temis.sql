<?php

/**
 * This class incapsulates easy joining tables engine.
 *
 * You no need think about "how to join tables". Just amek join tables via
 * column pair or foreign key.  All DBObject objects already contains
 * all necessary metadata for joining.
 */
class SQLJoin
{

	/**
	 * tag from owner object
	 * @var DBColumnDefinition
	 */
	var $ownerTag;

	/**
	 * tag from fereign object
	 * @var DBColumnDefinition
	 */
	var $foreignTag;
	
	/**
	 * user expression for join
	 * @var Expr
	 */
	var $expr = null;


	private function SQLJoin( )
	{
	}

	/**
	 * Construct join for specified forign key.
	 *
	 * @param DBForeignKey $key
	 * @param DBObject $ownerObject  owner object for detecting base table.
	 * @return SQLJoin
	 */
	static function createByKey( $key, $ownerObject=null )
	{
		$join = new SQLJoin();
        if ( get_class( $key ) != CLASS_DBForeignKey ) {
           trigger_error(sprintf( "'Key' parameter must be DBForiegnKey type, but got '%s' \n%s",get_class($key), Diagnostics::trace()), E_USER_ERROR);
        }

		if ( $ownerObject && $ownerObject->table_name() != $key->ownerTag->getTableName() ) {
			$join->ownerTag = $key->foreignTag;
			$join->foreignTag = $key->ownerTag;
		}
		else {
			$join->ownerTag = $key->ownerTag;
			$join->foreignTag = $key->foreignTag;
		}

		return( $join );
	}

	/**
	 *  Construct Jon by column pair.
	 *
	 * @param DBColumnDefinition $ownerTag   owner object column
	 * @param DBColumnDefinition $foreignTag  join object column
	 * @return SQLJoin
	 */
	static function createByPair( $ownerTag, $foreignTag )
	{
		$join = new SQLJoin();
		$join->ownerTag = $ownerTag; 
		$join->foreignTag = $foreignTag;
		return($join );
		
	}

	/**
	 * Create by key strictly. without smart logic.
	 * @param DBForeignKey $key
	 * @return SQLJoin
	 */
	public static function createByMasterKey(DBForeignKey $key)
	{
		$join = new SQLJoin();
		$join->ownerTag = $key->ownerTag();
		$join->foreignTag = $key->foreignTag();
		return($join );
	}

	/**
	 * Create by key strictly. without smart logic.
	 * @param DBForeignKey $key
	 * @return SQLJoin
	 */
	public static function createBySlaveKey(DBForeignKey $key)
	{
		$join = new SQLJoin();
		$join->ownerTag = $key->foreignTag();
		$join->foreignTag = $key->ownerTag();
		return($join );
	}

	/** create auto link
	 */
	function createWithExpr( $obj, $member, $tableAlias, $expr=null )
	{
		$join = SQLJoin::createAuto( $obj, $member, $tableAlias );
		$join->expr = $expr;
		return( $join );
	}
	
	/**
	 * Add expression for Join.
	 * 
	 * @param Expression $expr
	 */
	function addExpression(Expression $expr )
	{
		$this->expr = $expr;
	}
	
	function generate($generator)
	{
		$sql = $generator->getDictionary();
		
		$parts = array();

		$tableName = new SQLAlias(
			$this->foreignTag->getTableName(),
			 null,
			 $this->foreignTag->table->getTableAlias() );

		$expr =	new ExprEQ( $this->foreignTag, $this->ownerTag );

		if ( !is_null( $this->expr ) ) {
			$expr = new ExprAnd( $expr, $this->expr );
		}

		$parts[] = $sql->sqlLeftJoin;
		$parts[] = $tableName->generate($generator);
		$parts[] = $sql->sqlOn;
		$parts[] = SQL::compileExpr( $expr, $generator );
				
		return( implode( " ", $parts ) );
	}
}
define( "CLASS_SQLJoin", get_class( SQLJoin::createByPair(null, null)));