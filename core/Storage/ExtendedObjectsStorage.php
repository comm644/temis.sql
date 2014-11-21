<?php

/**
 * Class ExtendedObjectsStorage provides simple storage for 1 level inheritance
 */
class ExtendedObjectsStorage
{
	/**
	 * @var DBOBject
	 */
	var $proto;

	/**
	 * @var DBOBject
	 */
	var $protoParent;

	/**
	 * Statement runner.
	 * @var IStatementRunner
	 */
	var $runner;

	function __construct(IStatementRunner $runner, DBOBject $proto, $protoParent = NULL)
	{
		if (!$protoParent) {
			$protoParent = $proto->parentPrototype();
		}

		$this->proto = $proto;
		$this->protoParent = $protoParent;
		$this->runner= $runner;
	}

	/**
	 * @return \DBOBject
	 */
	public function proto()
	{
		return $this->proto;
	}

	/**
	 * @return \DBOBject
	 */
	public function protoParent()
	{
		return $this->protoParent;
	}

	/**
	 * @return \IStatementRunner
	 */
	protected function database()
	{
		return $this->runner;
	}



	/**
	 * Gets parent object for given object.
	 *
	 * @param DBOBject $obj
	 * @return DBOBject
	 */
	function getParentObject(DBOBject $obj)
	{
		$parent = $obj->parentPrototype();
		foreach (get_object_vars($parent) as $name => $value) {
			$parent->$name = $obj->$name;
			if ($obj->isMemberChanged($name)) {
				$parent->setChanged($name);
			}
		}
		return $parent;
	}

	/**
	 * @param DBOBject $obj
	 * @return DBOBject
	 */
	function getSelfObject(DBOBject $obj)
	{
		$class = get_class($obj);

		$object = new $class;

		foreach ( $obj->getColumnDefinition() as $name => $def) {
			$object->$name = $obj->$name;
			if ($obj->isMemberChanged($name)) {
				$object->setChanged($name);
			}
		}
		return $object;
	}

	function insert(DBOBject $obj)
	{
		if ( $obj->getParentKey() == NULL) {
			$obj->set_primary_key_value($this->database()->execute(new SQLStatementInsert($obj)));
			return $obj->get_primary_key_value();
		}

		if ( $obj->get_parent_key_value() <= 0) {
			$obj->set_parent_key_value($this->insert($this->getParentObject($obj)));
		}

		$obj->set_primary_key_value($this->database()->execute(new SQLStatementInsert($obj)));
		return $obj->get_primary_key_value();
	}

	function update(DBOBject $obj)
	{
		if ( $obj->getParentKey() == NULL) {
			if ( $obj->isChanged()) {
				$this->database()->execute(new SQLStatementUpdate($obj));
			}
			return;
		}

		$this->update($this->getParentObject($obj));

		$self = $this->getSelfObject($obj);

		if ($self->isChanged()) {
			$this->database()->execute(new SQLStatementUpdate($self));
		}
	}

	function smartUpdate(DBOBject $obj)
	{
		if ( $obj->isNew()) {
			$this->insert($obj);
		}
		else {
			$this->update($obj);
		}
	}

	function delete(DBOBject $obj)
	{
		$parent = $this->getParentObject($obj);

		//must be ondelete-cascade - check on generating step!!.
		$this->database()->execute(new SQLStatementDelete($parent));
	}


	function stmSelect()
	{
		$stm = new SQLStatementSelect($this->proto);


		$parentKey = $this->proto->getParentKey($this->protoParent);
		if ( !$parentKey ) {
			return $stm;
		}

		$object = $this->proto;
		$parent = $object->parentPrototype();

		while( $parent != NULL) {
			$parentKey = $object->getParentKey($parent);
			$stm->addJoin( SQLJoin::createByPair($parentKey->ownerTag(), $parentKey->foreignTag()));

			foreach ($parent->getColumnDefinition() as $tag) {
				$stm->addColumn($tag);
			}
			$object = $parent;
			$parent = $object->parentPrototype();
		}
		return $stm;
	}
	function stmSelectByDetailsId($id)
	{
		if ( !is_array($id)) {
			$id = array( $id );
		}
		$stm = $this->stmSelect();
		$stm->addExpression( new ExprIN($this->proto()->getPrimaryKeyTag(), $id) );
		return $stm;
	}
	function stmSelectByParentId($id)
	{
		if ( !is_array($id)) {
			$id = array( $id );
		}
		$stm = $this->stmSelect();
		$stm->addExpression( new ExprIN($this->protoParent()->getPrimaryKeyTag(), $id) );
		return $stm;
	}

	function stmAddJoins(SQLStatementSelect $stm )
	{
		$parentKey = $this->proto->getParentKey($this->protoParent);
		if ( !$parentKey ) {
			return $stm;
		}

		$object = $this->proto;
		$parent = $object->parentPrototype();

		while( $parent != NULL) {
			$parentKey = $object->getParentKey($parent);
			$stm->addJoin( SQLJoin::createByPair($parentKey->ownerTag(), $parentKey->foreignTag()));

			$object = $parent;
			$parent = $object->parentPrototype();
		}
	}
}