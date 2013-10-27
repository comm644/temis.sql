<?php
/******************************************************************************
 Copyright (c) 2007 by Alexei V. Vasilyev.  All Rights Reserved.
 -----------------------------------------------------------------------------
 Module     : SELECT statement
 File       : SQLStatementSelect.php
 Author     : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description:
******************************************************************************/
require_once( dirname( __FILE__ ) . "/SQLLimit.php" );
require_once( dirname( __FILE__ ) . "/SQLOffset.php" );
require_once( dirname( __FILE__ ) . "/SQLCode.php" );


class SQLStatementSelect extends SQLStatement
{
	/** @access privatesection */

	var $sqlStatement="SELECT";
	var $sqlWhere="WHERE";
	var $sqlAs="AS";
	var $sqlOrder="ORDER BY";
	var $sqlFrom="FROM";
	var $sqlGroup="GROUP BY";

	var $columnDefs = null;
	var $table = null;
	var $expr = null;
	var $alsoTables = null;
	var $order = array();
	var $group = array();
	var $joins = array();
	var $limit = null;
	var $offset = null;

	/** \publicsection */

	
	/** construct statement

	@param $obj DBObject database object prototype
	 */
	function SQLStatementSelect( $obj )
	{
		parent::SQLStatement( $obj );
		if ( !method_exists( $this->object, "getColumnDefinition" )
			|| !method_exists( $this->object, "table_name" )
			) {
			Diagnostics::error( "given object does not implement DBObject class" );
		}

		$this->joins      = array();
	}

	/** reset column information */
	function resetColumns()
	{
		$this->columnDefs = array();
	}

	/** add column in query
	 @param DBColumnDefinition $def  target column 
	 */
	function addColumn( $def )
	{
		$this->columnDefs[$def->getAliasOrName() ] = $def;
	}

	/** add join condition specified by foreing key tag
	 @param $tag DBForeignKey  foreign key tag
	 */
	function addJoin( $tag )
	{
        $class = get_class( $tag );
        switch ( $class ) {
            case CLASS_DBForeignKey:
                $this->joins[] = SQLJoin::createByKey( $tag, $this->object );
                break;
            case CLASS_SQLJoin:
                $this->joins[] = $tag;
                break;
            default:
                trigger_error(
                        sprintf( "Invalid argument: 'tag' must be DBForeightKey or SQLJoin but got '%s'\n %s",
                            $class, Diagnostics::trace() ));
        }
	}

	/**
	 * add joinf expression
	 *
	 * @param SQLJoin $expr
	 */
	function addJoinExpr( $expr )
	{
		$this->joins[] = $expr ;
	}
	
	/**
	 * Add WHERE expression with AND clause or set expression
	 * if was empt not exists.
	 *
	 * @param $expr Expression object defined condition
	 * @return Expression  actual whichy will by applied in SQL query.
	 */
	function addExpression( $expr )
	{
		if ( $this->expr !== null ) {
			$this->expr = new ExprAND( $this->expr, $expr );
		}
		else {
			$this->setExpression( $expr );
		}
		return $this;
	}
	
	/**
	 *  Set WHERE expression directly.
	 * 
	 * @param $expr Expression object defined condition
	 * @return Expression  actual whichy will by applied in SQL query.
	 */
	function setExpression( &$expr )
	{
		$this->expr = $expr;
		return $this;
	}
	
	public function getExpression() {
		return $this->expr;
	}



	/** add external tables in query
	 * @param $tableNames, ...   \b string  additional table names
	 */
	function addTables()
	{
		$this->alsoTables = func_get_args();
	}

	/** add order condition
	 @param $column DBColumnDefinition  target column for order defintion
	 @param $ascending \b bool \a true ascending mode, else descending mode
	 */
	function addOrder( $column, $ascending=true )
	{
		if ( is_null( $column ) ) return;
		if ( !is_object($column) && $column == "" ) return;

		$this->addSqlOrder( new SQLOrder( $column, $ascending ) );
	}
	
	/**
	 * Add composed SQL statement as order.
	 *
	 * @param SQLOrder $stm
	 */
	function addSqlOrder( $stm )
	{
		$this->order[] = $stm;
	}

	/** add groupping condition
	 @param $column DBColumnDefinition  target column for grouping feature
	 @param $ascending bool \a true ascending mode, else descending mode
	 */
	function addGroup($column, $ascending=null )
	{
		if ( is_null( $column ) ) return;
		if ( !is_object($column) && $column == "" ) return;

		$this->group[] = new SQLGroup( $column, $ascending );
	}

	/** set LIMIT feature
	 @param $limit \b integer  number of records for limit
	 */
	function setLimit( $limit )
	{
		$this->limit = new SQLLimit( $limit );
	}

	/** set OFFSEET feature
	 @param $offset \b integer start offset for query
	 */
	function setOffset( $offset )
	{
		$this->offset = new SQLOffset( $offset );
	}

	/** @access private
	 get  array of all primary keys
	 
	 @return array of all primary keys
	 */
	function primaryKeys()
	{
		$pk = $this->object->primary_key();
		if (!is_array( $pk  ) ) $pk = array( $pk );
		return( $pk );
	}

	/** 
	 * @access protected
	 get SQL query (only MySQL supported now )
	 @param SQLGenerator $generator
	 @return generated SQL query
	 */
	function generate($generator, $generateConditionMethod='generateCondition')
	{
		$sql = $generator->getDictionary();

		$parts = array();
		$parts[] = $sql->sqlSelect;
		$parts[] = $this->getColumns($generator);
		$parts[] = $sql->sqlFrom;
		$parts[] = $this->_getTables($generator);

		if ( count( $this->joins ) != 0 ) {
			$parts[] = $this->getJoins($generator);
		}

		if ( count( $this->expr ) != 0 ) {
			$parts[] = $sql->sqlWhere;
			$parts[] = $this->$generateConditionMethod($this->expr, $generator);
		}
		if ( count( $this->group ) != 0 ) {
			$parts[] = $sql->sqlGroup;
			$parts[] = $this->_getOrder($this->group, $generator);
		}
		if ( count( $this->order ) != 0 ) {
			$parts[] = $sql->sqlOrder;
			$parts[] = $this->_getOrder($this->order, $generator);
		}
		if ( !is_null( $this->limit )) {
			$parts[] = $this->limit->generate($generator);
		}
		if ( !is_null( $this->offset )) {
			$parts[] = $this->offset->generate($generator);
		}
		return( implode( "\n ", $parts ) );
	}

	function generateCondition($expr, $generator)
	{
		return SQL::compileExpr( $expr, $generator );
	}

	/**
	 * Generate parametrized condition
	 * 
	 * @param Expression $expr
	 * @param SQLGenerator $generator
	 */
	function generateParametrizedCondition($expr, $generator )
	{
		$compiler = new ECompilerSQL($generator, true );	
		$sql    = $compiler->compile($expr);
		$params = $compiler->getParameters();

		$this->queryParams = $params;
		return $sql;
	}

	function generateQuery($generator)
	{
		$this->queryParams = array();
		$sql = $this->generate($generator, 'generateParametrizedCondition');

		return new DBQuery($sql, $this->queryParams );
	}

	/** 
	 get joins conditions
	 @access private
	 @return generated JOIN conditions
	 */
	function getJoins($generator)
	{
		$parts = array();
		foreach( $this->joins as $join ) {
			$parts[] = $join->generate($generator);
		}
		return( implode( " ", $parts ) );
	}
	/** @access private
	 get columns for query
	 @param SQLGenerator $generator
	 @return generated columns conditions
	 */
	function getColumns($generator)
	{
		$sql = $generator->getDictionary();
		
		$parts = array();
		foreach( $this->columnDefs as $def ) {
			$str = array();
			if ( get_class( $def ) == CLASS_SQLFunction ) {
				$str[] = $def->generate($generator);
			}
			else if ( get_class( $def ) == CLASS_SQLCode ) {
				$str[] = $def->generate($generator);
			}
			else {
				$table = ( $def->table ) ? $def->getTableAlias() : $this->table;
				$str[] = SQLName::getNameFull( $table, $def->name, $generator );

				if ( $def->alias ) {
					$str[] = $sql->sqlAs;
					$str[] = SQLName::getName( $def->alias, $generator );
				}
			}

			$parts[] = implode( " ", $str );
		}
		return( implode( ", ", $parts ));
	}


	/** @access private
	 get tables for query
	 @param SQLGenerator $generator
	 @return generated tables query
	 */
	function _getTables($generator)
	{
		$parts = array();
		$alias = new SQLAlias( $this->object->table_name(), null, $this->object->getTableAlias() );
		
		$parts[] = $alias->generate($generator);
		if ( $this->alsoTables ) {
			foreach( $this->alsoTables as $table ) {
				$parts[] = SQLName::getName( $table, $generator );
			}
		}
		$query = implode( ",", $parts );
		if ( count( $parts ) > 1 && count($this->joins) != 0) { //MySql 5.0 requirements
			$query = "(" . $query . ")";
		}
		return( $query );
	}
	/** @access private
	 get order  for query
	 @param SQLGenerator $generator
	 @param $list \b array of DBColumnDefinition  target columns
	 */
	function _getOrder( $list, $generator )
	{
		if ( count( $list ) == 0 ) return "";
		$parts  = array();
		foreach( $list as $column ) {
			$parts[] = $column->generate( $generator, $this->table  );
		}
		return( implode( ",", $parts ) );
	}

	/**
	 * create default result container
	 *
	 * @param bool $signUseID  set true if need use primary keys as array indexes
	 * @return SQLStatementSelectResult  
	 */
	function createResultContainer($signUseID=true)
	{
		return new SQLStatementSelectResult( $this, $signUseID );
	}

	function readSqlObject( $sqlObject )
	{
		$newobj = $this->object->cloneObject();
		
		foreach( $this->columnDefs as $def ) {
			$member = ( $def->alias ) ? $def->alias : $def->name;
			$newobj->$member = SQLValue::importValue( $sqlObject->$member, $def->type );
		}
		return $newobj;
	}
	function readSqlArray( $sqlArray )
	{
		$useIndex = false;
		if ( is_numeric(key($sqlArray)) ) {
			$useIndex = true;
		}
		$newobj = $this->object->cloneObject();
		$pos = 0;
		foreach( $this->columnDefs as $def ) {
			$member = $def->getAliasOrName();
			$index = $useIndex  ? $pos++ :  $member;
			$newobj->$member = SQLValue::importValue( $sqlArray[$index], $def->type );
		}
		return $newobj;
	}
}

define( "CLASS_SQLStatementSelect", get_class( new SQLStatementSelect(new DBObjectMock())));
?>