<?php
require_once( dirname( __FILE__ ) . "/SQL.php" );
require_once( dirname( __FILE__ ) . "/SQLDic.php" );
require_once( dirname( __FILE__ ) . "/package.deps.php" );


class SQLStatement
{
	var $sqlStatement="--- undefined --";
	var $sqlWhere="WHERE";
	var $sqlLimit="LIMIT";

	/**
	 * Object with metainformation for creating default statement. 
	 *
	 * @var DBObject
	 */
	var $object = null;

	/**
	 * Colums for selection
	 * @var array(DBColumnDefiniton)
	 */
	var $columnDefs = null;
	var $table = null;


	function SQLStatement( $obj )
	{
		if ( !is_subclass_of($obj, CLASS_DBObject) ) {
			Diagnostics::error( "given ".get_class($obj)." object does not implement DBObject class" );
		}
		else if ( !method_exists( $obj, "getColumnDefinition" ) ) {
			Diagnostics::error( "given ".get_class($obj)." object does not implement getColumnDefinition() method" );
		}
		else if ( !method_exists( $obj, "table_name" )) {
			Diagnostics::error( "given ".get_class($obj)." object does not implement table_name() method " );
		}
		
		$this->object = $obj;
		$this->columnDefs = $this->object->getColumnDefinition();
		$this->table      = $this->object->table_name();
		
	}
	function primaryKeys()
	{
		$pk = $this->object->primary_key();
		if (!is_array( $pk  ) ) $pk = array( $pk );
		return( $pk );
	}
}

/** DOc not use this class. required only for creating type info */
class DBObjectMock extends TM_DBObject 
{
	function getColumnDefinition() { return array(); }
	function table_name() { return ""; } 	
};


require_once( dirname( __FILE__ ) . "/SQLName.php" );
require_once( dirname( __FILE__ ) . "/SQLValue.php" );
require_once( dirname( __FILE__ ) . "/SQLStatementChange.php" );
require_once( dirname( __FILE__ ) . "/SQLStatementInsert.php" );
require_once( dirname( __FILE__ ) . "/SQLStatementUpdate.php" );
require_once( dirname( __FILE__ ) . "/SQLStatementSelect.php" );
require_once( dirname( __FILE__ ) . "/SQLStatementSelectResult.php" );
require_once( dirname( __FILE__ ) . "/SQLStatementDelete.php" );
require_once( dirname( __FILE__ ) . "/SQLJoin.php" );


require_once( dirname( __FILE__ ) . "/SQLOrder.php" );
require_once( dirname( __FILE__ ) . "/SQLGroup.php" );
require_once( dirname( __FILE__ ) . "/SQLFunction.php" );
require_once( dirname( __FILE__ ) . "/SQLColumnExpr.php" );
