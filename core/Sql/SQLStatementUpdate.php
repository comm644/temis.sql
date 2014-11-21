<?php

class SQLStatementUpdate extends SQLStatementChange
{
	var $sqlStatement="UPDATE";
	var $signForceUpdate = false;


	function SQLStatementUpdate( $obj )
	{
		parent::SQLStatementChange( $obj );
	}

	function generate($generator)
	{
		$parts = array();
		$parts[] = parent::generate($generator);
		$parts[] = $this->sqlWhere;
		$parts[] = $this->getCondition($generator);
		if ( $generator->updateLimitEnabled() ) {
			$parts[] = $this->sqlLimit;
			$parts[] = SQLValue::getValue( 1, DBValueType_integer, $generator  );
		}
		
		return( implode( " ", $parts ) );
	}
	
	function _generateParametrizedQuery($names, $pholders, $generator)
	{
		$parts = array();
		$parts[] = parent::_generateParametrizedQuery($names, $pholders, $generator);
		$parts[] = $this->sqlWhere;
		$parts[] = $this->getCondition($generator);
		
		if ( $generator->updateLimitEnabled() ) {
			$parts[] = $this->sqlLimit;
			$parts[] = SQLValue::getValue( 1, DBValueType_integer, $generator  );
		}
		
		return( implode( " ", $parts ) );
	}

	function getCondition($generator)
	{
		$pk   = $this->primaryKeys();
		$expr = array();
		foreach( $pk as $name ){
			if ($this->signEnablePK && $this->object->isChanged() && $this->object->isMemberChanged( $name )) {
				$expr[] = new ExprEQ( $name, $this->object->getPreviousValue($name) );
			}
			else {
				$expr[] = new ExprEQ( $name, $this->object->{$name} );
			}
		}
		if ( count( $expr ) > 1 ) {
			$expr = new ExprAND( $expr);	
		}
		else {
			$expr = array_shift( $expr );
		}
		return( SQL::compileExpr( $expr, $generator  ));
	}

	function _isMemberChanged( $name )
	{
		if ( $this->signForceUpdate ) return true;
		return parent::_isMemberChanged( $name );
	}
}
define( "CLASS_SQLStatementUpdate", get_class( new SQLStatementUpdate(new DBObjectMock())));

?>
