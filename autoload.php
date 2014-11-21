<?php


global $TEMIS_SQL_DIRS;
$TEMIS_SQL_DIRS = array(
	__DIR__ .'',
	__DIR__ .'/core',
	__DIR__ .'/core/Sql',
	__DIR__ .'/core/Database',
	__DIR__ .'/core/Expressions',
	__DIR__ .'/core/Storage',
	__DIR__ .'/core/Relations',
	__DIR__ .'/core/MySql',
	__DIR__ .'/core/Sqlite'
);
function temis_sql__autoload($class)
{
	global $TEMIS_SQL_DIRS;

	//echo "<br>\n\n search $class\n";
	foreach( $TEMIS_SQL_DIRS  as $dir ) {
		$file = $dir .'/' . $class . '.php';
		//echo "scan $file\n<br>";
		if ( file_exists($file))  {
			//echo "found $file<br>\n";
			require_once $file;
			return;
		}
	}
	//echo "not found $class<br>\n";
};

spl_autoload_register('temis_sql__autoload');

require_once __DIR__ . '/core/Database/DBColumnDefinition.php';
require_once __DIR__ . '/core/Database/DBDataSource.php';
require_once __DIR__ . '/core/Database/DBDefaultResultContainer.php';
require_once __DIR__ . '/core/Database/DBForeignKey.php';
require_once __DIR__ . '/core/Database/DBObject.php';
require_once __DIR__ . '/core/Database/DBParam.php';
require_once __DIR__ . '/core/Database/DBQuery.php';
require_once __DIR__ . '/core/Database/DBResultContainer.php';
require_once __DIR__ . '/core/Database/DBValueType.php';
require_once __DIR__ . '/core/Database/IDataSource.php';
require_once __DIR__ . '/core/DatabaseException.php';
require_once __DIR__ . '/core/Expressions/ECompilerSQL.php';
require_once __DIR__ . '/core/Expressions/Expressions.php';
require_once __DIR__ . '/core/Relations/DBRelationAdapter.php';
require_once __DIR__ . '/core/Relations/DBRelationInfo.php';
require_once __DIR__ . '/core/Sql/SQL.php';
require_once __DIR__ . '/core/Sql/SQLAlias.php';
require_once __DIR__ . '/core/Sql/SQLCode.php';
require_once __DIR__ . '/core/Sql/SQLColumnExpr.php';
require_once __DIR__ . '/core/Sql/SQLDic.php';
require_once __DIR__ . '/core/Sql/SQLFunction.php';
require_once __DIR__ . '/core/Sql/SQLGenerator.php';
require_once __DIR__ . '/core/Sql/SQLGroup.php';
require_once __DIR__ . '/core/Sql/SQLJoin.php';
require_once __DIR__ . '/core/Sql/SQLLimit.php';
require_once __DIR__ . '/core/Sql/SQLName.php';
require_once __DIR__ . '/core/Sql/SQLOffset.php';
require_once __DIR__ . '/core/Sql/SQLOrder.php';
require_once __DIR__ . '/core/Sql/SQLParam.php';
require_once __DIR__ . '/core/Sql/SQLStatement.php';
require_once __DIR__ . '/core/Sql/SQLStatementChange.php';
require_once __DIR__ . '/core/Sql/SQLStatementDelete.php';
require_once __DIR__ . '/core/Sql/SQLStatementInsert.php';
require_once __DIR__ . '/core/Sql/SQLStatementSelect.php';
require_once __DIR__ . '/core/Sql/SQLStatementSelectResult.php';
require_once __DIR__ . '/core/Sql/SQLStatementUpdate.php';
require_once __DIR__ . '/core/Sql/SQLValue.php';
require_once __DIR__ . '/core/Storage/CompositeStorage.php';
require_once __DIR__ . '/core/Storage/ExtendedObjectsStorage.php';
require_once __DIR__ . '/core/Storage/IDBCompositeObject.php';
require_once __DIR__ . '/core/Storage/IStatementRunner.php';
require_once __DIR__ . '/core/Storage/SimpleStatementRunner.php';
