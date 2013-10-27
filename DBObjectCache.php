<?php
require_once( dirname(__FILE__ ) . "/package.deps.php" );

class DBObjectCacheImpl extends Singleton
{
	var $cache = array();

	/** store objet to cache
	 * 
	 * @param mixed $keyValue  indexer for stored objects, usually used DBOject::primary_key_value()
	 * @param mixed $obj object for storing
	 * 	 
	 */
	function store( $keyValue, &$obj )
	{
		$clsName = get_class( $obj );
		$this->cache[$clsName][ $keyValue ] = $obj;
	}

	/** retrieve object from cache
	 * @param mixed $keyValue  indexer for stored objects, usually used DBOject::primary_key_value()
	 * @param mixed $proto what an object need retrieve from cache
	 */
	function get( $keyValue, $proto )
	{
		$clsName = get_class( $proto );
		if ( !array_key_exists( $clsName, $this->cache ) ) return null;
		if ( !array_key_exists( $keyValue, $this->cache[$clsName] ) ) return null;
		
		return( $this->cache[$clsName][$keyValue] );
	}
	
	/**
	 * reset cache
	 *
	 * @param mixed $proto class prototype which describes cache
	 */
	function reset( $proto )
	{
		$clsName = get_class( $proto );
		$this->cache[$clsName] = array();
	}

	/**
	 * Init frontend DBObjectCache  
	 * \static
	 */
	static function initInstance()
	{
		$obj = new DBObjectCacheImpl();
		$obj->createFrontend("DBObjectCache");
	}
}
DBObjectCacheImpl::initInstance();

?>