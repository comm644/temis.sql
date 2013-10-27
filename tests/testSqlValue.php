<?php

class testSqlValue extends phpTest_TestSuite
{
    function testCanParseDateTime()
    {
	$expected=array( "2010", "12", "16", "12", "00", "00" );
	
	
	$parts = preg_split("/[ T:\-]/", "2010-12-16 12:00:00" );
	$this->assertEquals( $expected, $parts );
	
	$parts = preg_split("/[ T:\-\.]/", "2010.12.16T12:00:00" );
	$expected=array( "2010", "12", "16", "12", "00", "00" );
	$this->assertEquals( $expected, $parts );
    }
}