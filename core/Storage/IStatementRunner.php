<?php

/**
 * Interface IStatementRunner provides abstract access to Database interaction level.
 */
interface IStatementRunner
{
	function execute(SQLStatement $stm);
}