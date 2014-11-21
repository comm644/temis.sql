<?php
class DBForeignKey
{
	/**
	 * tag from owner object
	 * @var DBColumnDefinition  owner primary key
	 */
	var $ownerTag;

	/**
	 * tag from foreign object
	 *
	 * @var DBColumnDefinition  foreight key
	 */
	var $foreignTag;

	function DBForeignKey($ownerTag, $foreignTag)
	{

		$this->ownerTag = $ownerTag;
		$this->foreignTag = $foreignTag;
	}

	/**
	 * @return \DBColumnDefinition
	 */
	public function foreignTag()
	{
		return $this->foreignTag;
	}

	/**
	 * @return \DBColumnDefinition
	 */
	public function ownerTag()
	{
		return $this->ownerTag;
	}


}

define( "CLASS_DBForeignKey", get_class( new DBForeignKey(null, null)));
