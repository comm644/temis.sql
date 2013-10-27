<?php
/******************************************************************************
 Copyright (c) 2007 by Alexei V. Vasilyev.  All Rights Reserved.                         
 -----------------------------------------------------------------------------
 Module     : DB result container
 File       : DBResultcontainer.php
 Author     : Alexei V. Vasilyev
 -----------------------------------------------------------------------------
 Description:
******************************************************************************/


  /** DB result container abstract class. implement saving result STRATEGY 
   */
class DBResultContainer
{
	var $data = array();
		
	/** methods will be called on begin reading stream
	 */
	function begin()
	{
		$this->data = array();
	}

	/** method should to add object to result container
	 */
	function add( $object )
	{
		$this->data[] = $object;
	}

	/** 
	 * method should convert read associative array to object hich will be stored via method  add()
	 * 
	 * @param array $sqlLine  read row from SQL as associative array.   
	 */
	function fromSQL( $sqlLine )
	{
		return $sqlLine;
	}

	/** method will be called on end of reading sql result
	 */
	function end()
	{
	}

	
	/** method returns result
	 * @return array  
	 */
	function getResult()
	{
		return $this->data;
	}
}

?>