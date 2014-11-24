<?
//	+function db_connect
function db_connect(&$val, &$db)
{
	$dbIni	= $db->dbIni;
	$dbhost	= $dbIni['host'];
	$dbuser	= $dbIni['login'];
	$dbpass	= $dbIni['passw'];
	//	Если нет dbHost connect вылетает с ошибкой
	if (!$dbhost) return;

	$timeStart	= getmicrotime();
	$db->dbLink->connect($dbhost, $dbuser, $dbpass);
	$time 		= round(getmicrotime() - $timeStart, 4);
	//	Записать в лог, если это не восстановление бекапа
	if (!defined('restoreProcess')){
		module('message:sql:trace', "$time CONNECT to $dbhost");
		module('message:sql:error', $db->dbLink->error);
	}
	//	Если ошибка, то зафиксируем ее
	if ($db->dbLink->error){
		module('message:sql:error', $db->dbLink->error);
		module('message:error', 'Ошибка открытия базы данных.');
		return;
	}
	//	Все соедедено, продлжить
	$db->connected	= true;
	$db->dbExec("SET NAMES UTF8");
	return true;
}

//	+function database_insertRow
function database_insertRow(&$db, &$table, &$array, $bDelayed)
{
	reset($array);
	$table	= dbMakeField($table);
	$fields	=''; $comma=''; $values='';
	foreach($array as $field => &$value)
	{
		$field	= dbMakeField($field);
		$fields	.= "$comma$field";
		$values	.= "$comma$value";
		$comma	= ',';
	}
	
	if ($bDelayed) $res = $db->dbLink->dbExec("INSERT DELAYED INTO $table ($fields) VALUES ($values)", 0, 0);
	else $res =  $db->dbLink->dbExecIns("INSERT INTO $table ($fields) VALUES ($values)", 0);

	unset($table);
	unset($fields);
	unset($values);
	unset($comma);

	return $res;
}
?>