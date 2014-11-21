<?php

class SQLStatementDelete extends SQLStatementChange
{
	var $sqlStatement="DELETE FROM";
	var $expr = null;
	
	function SQLStatementDelete( $obj )
	{
		parent::SQLStatementChange( $obj );

		if ( is_a( $obj, 'DBObjectMock')) {
			return;
		}

		if ( $obj->primary_key_value() == 0 || $obj->primary_key_value() == -1 ) {
			return;
		}
		$this->setExpression(new ExprEQ($obj->getPrimaryKeyTag(), $obj->primary_key_value()));
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
	}

	/**
	 * Add WHERE expression with AND clause or set expression
	 * if was empt not exists.
	 *
	 * @param $expr Expression object defined condition
	 * @return Expression  actual whichy will by applied in SQL query.
	 */
	function addExpression( &$expr )
	{
		if ( $this->expr !== null ) {
			$this->expr = new ExprAND( $this->expr, $expr );
		}
		else {
			$this->setExpression( $expr );
		}
		return $this;
	}
	
	function generate($generator)
	{
		if ( $this->object->getTableAlias() != null ) {
			Diagnostics::error('Aliases for SQL DELETE does not supported. Check prototype.');
		}
		$parts = array();
		$parts[] = $this->sqlStatement;
		
		$parts[] = SQLName::getName( $this->table, $generator );
		if ( count( $this->expr ) != 0 ) {
			$parts[] = $this->sqlWhere;
			$parts[] = SQL::compileExpr( $this->expr, $generator );
		}
		
		return( implode( " ", $parts ) );
	}
}

define( "CLASS_SQLStatementDelete", get_class( new SQLStatementDelete(new DBObjectMock())));
