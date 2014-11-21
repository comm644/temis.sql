<?php
/******************************************************************************
 Copyright (c) 2007-2008 by Alexei V. Vasilyev.  All Rights Reserved.                         
 -----------------------------------------------------------------------------
 Module     : DB default result container
 File       : DBDefaultREsultContainer.php
 Author     : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description:
******************************************************************************/


if ( !defined( "__CLASS_STDCLASS__" ) ) define("__CLASS_STDCLASS__", get_class( new stdclass ) );

class DBDefaultResultContainer extends DBResultContainer
{
	var $proto;

	function DBDefaultResultContainer($class, $signUseID)
	{
		if ( $class != null && !is_object( $class ) ) {
			$class = new $class;
		}
			
		$this->proto = $class;
		$this->signUseID = $signUseID;
	}
	
	/**
	 * Default result container performs copying all incoming row keys as object members. 
	 *
	 * @param array $row  associtive array read from SQL
	 * @return object  created via cloning
	 */
	function fromSQL( $row )
	{
		$obj = null;
		
		if ( $this->proto != null ) {
			if ( get_class( $this->proto ) == __CLASS_STDCLASS__ ) {
				$class = __CLASS_STDCLASS__;
				$obj = new $class;
			}
			else {			
				$obj = $this->proto->cloneObject();
			}
		}
		copy_object_vars( $obj, $row );
		return $obj;
	}
	function add( $obj )
	{
		if ( $this->signUseID == true && $this->proto != null) {
			$pos = $obj->primary_key_value();
			$this->data[ $pos ] = $obj;
		}
		else {
			$this->data[] = $obj;
		}
	}
}

