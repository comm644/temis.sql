<?php
require_once(dirname( __FILE__ ) . "/../ADO.php" );

class A 
{
	var $a_id;

	function table_name() { return( "tableA" ); }
	function primary_key() { return( "a_id" ); }
	function primary_key_value() { return( $this->a_id ); }
}

class B
{
	var $b_id;
	function table_name() { return( "tableB" ); }
	function primary_key() { return( "b_id" ); }
	function primary_key_value() { return( $this->b_id ); }
}

class R
{
	var $a_id;
	var $b_id;

	function table_name() { return( "tableR" ); }
	function primary_key() { return( "" ); }
	function primary_key_value() { return( "" ); }
}



class ABRel extends DBRelationAdapter
{
	function getObject( $oid, $mid )
	{
		$o = new R;
		$o->a_id = $oid;
		$o->b_id = $mid;
		return( $o );
	}
	function getDataObject( $oid )
	{
		$o = new A;
		$o->a_id = $oid;
		return( $o );
	}
	function getMemberObject( $mid )
	{
		$o = new B;
		$o->b_id = $mid;
		return( $o );
	}
	function getForeignKeys()
	{
		return( array( "a_id", "b_id" ) );
	}
}


class xtestRelations extends  PhpTest_TestSuite
{

	function test1()
	{

		$rel = new ABRel();

		$str = $rel->stmSelectChilds( 1);

		TS_ASSERT_EQUALS(
			"SELECT tableB.`b_id` FROM tableB,tableR WHERE "
			."(tableR.`b_id`=tableB.`b_id`) AND (tableR.`a_id`='1')", $str );
		
	}
}
?>