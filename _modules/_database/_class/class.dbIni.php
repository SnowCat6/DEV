<?
class dbIni
{
	static function connect(&$db)
	{
		$dbIni	= $db->dbIni;
		$dbhost	= $dbIni['host'];
		$dbuser	= $dbIni['login'];
		$dbpass	= $dbIni['passw'];
		//	Если нет dbHost connect вылетает с ошибкой
		if (!$dbhost){
			module('message:error', 'Не задан адрес сервера.');
			return;
		}
	
		$timeStart	= getmicrotime();
		$db->dbLink->connect($dbhost, $dbuser, $dbpass);
		$time 		= round(getmicrotime() - $timeStart, 4);
	
		//	Записать в лог, если это не восстановление бекапа
		module('message:sql:trace', "$time CONNECT to $dbhost");
		//	Если ошибка, то зафиксируем ее
		if ($db->dbLink->error)
		{
			module('message:sql:error', $db->dbLink->error);
			module('message:error', 'Ошибка открытия базы данных.');
			return;
		}
		//	Все соедедено, продлжить
		$db->connected	= true;
		$db->dbExec("SET NAMES UTF8");
		return true;
	}
	
	static function get($dbIni = NULL)
	{
		$gIni	= getGlobalCacheValue('ini');
		$gIni	= $gIni[':db'];
		if (!is_array($gIni)) $gIni = array();
		
		if (!$dbIni){
			$dbIni	= getIniValue(':db');
		}
		
		if ($dbIni['login'] || $dbIni['passw']){
			$dbIni['login']	= $dbIni['login'];
			$dbIni['passw']	= $dbIni['passw'];
		}
		
		foreach($gIni as $name => $val)
		{
			if (array_key_exists($name, $dbIni)) continue;
			$dbIni[$name] = $val;
		}
		
		//
		$dbName	= $dbIni['db'];
		if (!$dbName)	$dbName	= siteFolder();
		$dbName	= preg_replace('#[^a-zA-Z0-9_-]#', '_', $dbName);
		$dbName	= preg_replace('#_+#', '_', $dbName);
		$dbIni['db']	= $dbName;
		
		//
		$dbPrefix	= $dbIni['prefix'];
		if (!$dbPrefix){
			$dbPrefix	= siteFolder();
			$dbPrefix	= preg_replace('#[^a-zA-Z0-9_]#', '_', $dbPrefix);
			$dbPrefix	= preg_replace('#_+#', '_', $dbPrefix);
		}
		$dbPrefix	= rtrim($dbPrefix, '_');
		$dbPrefix	.= '_';
		$dbIni['prefix']	= $dbPrefix;
		
		if (!$dbIni['host']){
			$dbIni['host'] = 'localhost';
		}
		
		setCacheValue('dbIni', $dbIni);
		
		return $dbIni;
	}
}
?>