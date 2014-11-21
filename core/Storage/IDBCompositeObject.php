<?php

/**
 * Interface IDBCompositeObject provides emthods for discovering member objects.
 */
interface IDBCompositeObject
{
	/**
	 * Should return array of member objects. For empty owner must be returned array with empty objects.
	 *
	 * @return array|DBObject
	 */
	function members();
}
