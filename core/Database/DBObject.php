<?php
/******************************************************************************
 Copyright (c) 2005 by Alexei V. Vasilyev.  All Rights Reserved.                         
 -----------------------------------------------------------------------------
 Module     : Database Object base class
 File       : DBObject.php
 Author     : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description: for describing datasets
 ******************************************************************************/
require_once (dirname(__FILE__) . "/DBColumnDefinition.php");


/** base class for DataObjects/table_definitions
 * @property bool _isChanged
 * @property array _changedColumns
 */
abstract class TM_DBObject extends Clonable
{
	/**
	 * Gets table name assciated with object.
	 *
	 * @return string  table name
	 */
	abstract function table_name();

	/** returns primary key name (obsolete/internal use only)
	 * @return string primary key column name as \b string
	 */
	abstract function primary_key ();

	/** returns primary key value
	 * @return mixed primary key value with type as defined in database
	 */
	function primary_key_value ()
	{

		return ("");
	}

	/**
	 * returns primary key tag
	 * @return DBColumnDefinition
	 */
	function getPrimaryKeyTag ()
	{

		$pkname = $this->primary_key();
		$tagmethod = "tag_" . $pkname;
		return $this->$tagmethod();
	}

	/** 
	 * return condition for searching by primary keys if used sevaral PKs
	 *	
	 * @return Expression for seaching by primary keys
	 */
	function get_condition ()
	{

		$pk = $this->primary_key();
		if (! is_array($pk)) $pk = array($pk);
		
		$cond = array();
		
		foreach ($pk as $key) {
			
			$cond[] = new ExprEQ($key, $this->$key);
		}
		$cond = new ExprAND($cond);
		
		return ($cond);
	}

	/** return unique ID  as text combined from several primary keys
	 * @return string  created unique ID as \b string
	 */
	function get_uid ()
	{

		$pk = $this->primary_key();
		if (! is_array($pk)) $pk = array($pk);
		
		$ids[] = array();
		foreach ($pk as $key) {
			$pkval = $this->$key;
			if (is_numeric($pkval)) $pkval = dechex($pkval);
			else if (is_string($pkval)) $pkval = md5($pkval);
			$ids[] = $pkval;
		}
		return (implode("-", $ids));
	}

	/** returns  \a true if object was changed.
	 * @return \b bool value , \a true if object was changed or \a false
	 */
	function isChanged ()
	{

		if (! array_key_exists("_isChanged", $this)) return (false);
		return ($this->_isChanged);
	}

	/**
	 * returns true if object newly created and not stored in database
	 *
	 * @return bool true if newly created
	 */
	function isNew ()
	{

		$val = $this->primary_key_value();
		return ($val === 0 || $val === - 1);
	}

	/** return ASSOC array with new values
	 * @return array|mixed associative \b array  of changed columns
	 */
	function getUpdatedFields ()
	{

		$updated = array();
		foreach ($this->_changedColumns as $name => $dummy) {
			$updated[$name] = $this->{$name};
		}
		return ($updated);
	}

	/** return Column Definition array
	 * @return array|DBColumnDefinition items - object relation scheme
	 */
	function getColumnDefinition ()
	{

		$columnDefinition = array();
		foreach (get_class_vars(get_class($this)) as $name => $value) {
			$columnDefinition[$name] = new DBColumnDefinition($name, gettype($this->{$name}));
		}
		return ($columnDefinition);
	}

	/** set property value with state control

	* @param [in] $name  \b string member name
	* @param [in] $value \b mixed  new member value
	* @return \b bool \a true  if new value is equals, or \a false  if member was changed
	* */
	function setValue ($name, $value)
	{

		if ($this->{$name} === $value) return (TRUE);
		$this->setChanged($name);
		$this->{$name} = $value;
		return (FALSE);
	}

	/** shows  changed state for selected member
	* @param[in] $name  member name
	* @return \b bool  \a true if member value was changed
	*/
	function isMemberChanged ($name)
	{

		if (! $this->isChanged()) return (false);
		return (array_key_exists($name, $this->_changedColumns));
	}

	/** get previous value of changed member
	 @param[in] $name \b string member name
	 */
	function getPreviousValue ($name)
	{

		if (! $this->isMemberChanged($name)) return ($this->{$name});
		return ($this->_changedColumns[$name]);
	}

	/** revert changed for selected member

	 set to member previous value , as was before any changes
	 @param[in] $name \b string  member name
	 */
	function revertMemberChanges ($name)
	{

		if (! $this->isMemberChanged($name)) return (true);
		$this->{$name} = $this->getPreviousValue($name);
		unset($this->_changedColumns[$name]);
		if (count($this->_changedColumns) == 0) {
			$this->_isChanged = false;
		}
	}

	/** get value for selected member
	 @param[in] $name \b string  member name
	 @return  \b mixed  member value
	 */
	function getValue ($name)
	{

		return ($this->{$name});
	}

	/** Force set state as changed because all changes
	 can be blocked if new value indentical to current

	 @param $name \b string  member name
	 */
	function setChanged ($name)
	{

		$this->_isChanged = true;
		$this->_changedColumns[$name] = $this->{$name};
	}

	/** discard changed state

	can be used for hiding from database engine any changes.
	 */
	function discardChangedState ()
	{

		if (! $this->isChanged()) return;
		unset($this->_isChanged);
		unset($this->_changedColumns);
	}

	/** return table alias

	@return \b string  table alias described for object table (ORM bindings)
	 */
	function getTableAlias ()
	{

		if (! array_key_exists("_tableAlias", $this)) return (null);
		return $this->_tableAlias;
	}

	/** set table alias
	 @param $alias  \b string  table alias for current object instance (ORM bindings)
	 */
	function setTableAlias ($alias)
	{

		$this->_tableAlias = $alias;
	}

	/**
	 * Gets parent object prototype if base class (table) defined.
	 *
	 * @return DBObject|null
	 */
	function parentPrototype()
	{
		return NULL;
	}

	/**
	 * Sets parent object UID
	 */
	function set_parent_key_value($value)
	{
	}

	/**
	 * Gets parent object UID
	 */
	function get_parent_key_value()
	{
	}

	/**
	 * Sets primary key value.
	 *
	 * @param $value
	 */
	function set_primary_key_value($value)
	{
		$name = $this->primary_key();
		$this->$name = $value;
	}

	/**
	 * Gets primary key value.
	 *
	 * @return mixed
	 */
	function get_primary_key_value()
	{
		$name = $this->primary_key();
		return $this->$name;
	}

	/**
	 * Gets parent class foreign key.
	 * @return DBForeignKey
	 */
	function getParentKey()
	{
		return NULL;
	}

	//reflection. routines for changing object properties via inheritance
	

	/** reset all object fields for inheritance routines and simplyfiyng object
	 */
	function reflection_resetFields ()
	{

		//remove orig fields
		foreach ($this as $key => $var) {
			unset($this->$key);
		}
	}

	function reflection_addField ($tag)
	{

		$this->{$tag->name} = "";
	}

	/**
	 * Compose object fields according to Database defition.
	 * should be executed from custructor.
	 */
	function reflection_compose()
	{
		$this->reflection_resetFields();
		foreach( $this->getColumnDefinition() as $def ) {
			$this->reflection_addField( $def );
		}
	}
}

if( !class_exists( 'DBObject')) {
	abstract class DBOBject extends TM_DBObject {}
}

define("CLASS_DBObject", 'TM_DBObject');

?>