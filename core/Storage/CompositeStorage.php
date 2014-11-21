<?php

/**
 * Class CompositeStorage
 *
 *  CRUDL Adapter
 *
 * CREATE: Object with details must be stored in database.
 * READ: MUST be only ONE SELECT statement with joins but in object MUST be hierarchy.
 * UPDATE: Object with details must be stored in database.
 * LIST: MUST be only ONE SELECT statement with joins WITHOUT hierarchy.
 * DELETE: Object with details must be deleted from database.
 *
 * Case 1:  List of objects: only several fields required.
 * Case 2:  Create/Read/Update object: we works with full object hierarchy. required all fields of details.
 */
class CompositeStorage extends ExtendedObjectsStorage
{
	function __construct(IStatementRunner $runner, DBOBject $proto, $protoParent = NULL)
	{
		parent::__construct($runner, $proto, $protoParent); // TODO: Change the autogenerated stub
	}


	/**
	 * @param DBOBject| $object
	 */
	public function insert(DBOBject $object)
	{
		$rc = parent::insert($object);


		if ( !method_exists($object, 'members')) {
			return $rc;
		}
		/** @var $member DBOBject */
		foreach( $object->members() as $member ) {
			$member->set_parent_key_value( $object->get_parent_key_value() );
			$member->set_primary_key_value($this->database()->execute(new SQLStatementInsert($member)));
		}
		return $rc;
	}
	function update(DBOBject $object)
	{
		parent::update($object); // TODO: Change the autogenerated stub

		/** @var $member DBOBject */
		foreach( $object->members() as $member ) {
			if( !$member->isChanged()) {
				continue;
			}

			$this->database()->execute(new SQLStatementUpdate($member));
			$member->discardChangedState();
		}
	}


	/**
	 * @return SQLStatementSelect
	 */
	function stmSelect()
	{
		$stm = parent::stmSelect();

		/** @var $member DBOBject */
		foreach( $this->proto()->members() as $member ) {
			$stm->addJoin($member->getParentKey($this->protoParent));

			foreach ($member->getColumnDefinition() as $tag) {
				$stm->addColumn($tag);
			}
		}
		return $stm;
	}

	public function assignMemberFields(DBOBject $object)
	{
		/** @var $member DBOBject */
		foreach( $object as $member ) {
			if ( !is_a($member, "DBObject")) {
				continue;
			}
			$ignore = $member->getParentKey()->ownerTag()->getName();

			/** @var $tag DBColumnDefinition */
			foreach ($member->getColumnDefinition() as $tag) {
				$name = $tag->getName();
				if ( !isset( $object->$name ) ) {
					continue;
				}

				$member->$name = $object->$name;

				if ( $ignore == $name ) {
					continue;
				}
				unset( $object->$name);
			}
		}
	}


}