<?php
require_once( dirname( __FILE__ ) . "/SQL.php" );
require_once( dirname( __FILE__ ) . "/SQLDic.php" );
require_once( dirname( __FILE__ ) . "/../../package.deps.php" );


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

	function createResultContainer()
	{
		return new DBDefaultResultContainer( new stdclass, false);
	}
}

/** DOc not use this class. required only for creating type info */
class DBObjectMock extends TM_DBObject 
{
	function getColumnDefinition() { return array(); }
	function table_name() { return ""; }

	/** returns primary key name (obsolete/internal use only)
	 * @return string primary key column name as \b string
	 */
	function primary_key()
	{
		return 'key';
	}
};



