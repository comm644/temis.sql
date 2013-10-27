<?php
require_once( dirname( __FILE__ ) . "/Clonable.php" );

/**
 * This class defines meta information about database column.
 *
 * Ususally in generated code this class used as return valie in tag_*() methods.
 */
class DBColumnDefinition extends Clonable
{
	var $name;
	var $type;
	var $alias = null;
	var $table;
	var $disableGetByReference=false;
	
	function DBColumnDefinition( $name=null, $type=null, $alias=null, $table=null, $disableGetByReference=false )
	{
		$this->name = $name;
		$this->type = $type;


		if ( $alias != null ) {
			$this->alias = $alias;
		}
		else {
			//Sqlite returns full column name if join ('table.column' instead 'column' )
			$this->alias = $name;
		}
		$this->table = $table;
		$this->disableGetByReference = $disableGetByReference;
	}

	/**
	 * Gets column name. Methods retuns raw column name.
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Gets column alias if defined.
	 * If column alias is not defined then method returns column name.
	 *
	 * @return string
	 */
	function getAlias()
	{
		return( $this->alias );
	}


	/**
	 * Gets alias or name for binding destination column name.
	 * Main idea is : value in result set can be binded to another member.
	 * member name in this case need set by Alias, another words, column name
	 * always have using as member name.
	 *
	 * @return string
	 */
	function getAliasOrName()
	{
		if ( !$this->alias ) {
			return $this->name;
		}
		return $this->alias;
	}

	/**
	 * Gets table alias.
	 * Method returns table alias if alias defined. If table alias is not defined
	 * then method returns table name.
	 *
	 * If table not defined for column then method returns null
	 * 
	 * @return string  table alias
	 */
	function getTableAlias()
	{
		if ( is_null( $this->table ) ) return (null );

		$alias =$this->table->getTableAlias();
		if ( is_null( $alias ) ) $alias = $this->table->table_name();
		return( $alias );
	}

	/**
	 * Gets raw table name.
	 *
	 * @return string
	 */
	function getTableName()
	{
		if ( $this->table == null ) return (null );
		return( $this->table->table_name());
	}
}
define( "CLASS_DBColumnDefinition", get_class( new DBColumnDefinition() ) );
?>