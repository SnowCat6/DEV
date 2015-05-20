<?
//	+function database_dbIni
function database_dbIni($dbIni = NULL)
{
	$gIni	= getGlobalCacheValue('ini');
	$gIni	= $gIni[':db'];
	if (!is_array($gIni)) $gIni = array();
	
	if (!$dbIni){
		$ini	= getCacheValue('ini');
		$dbIni	= $ini[':db'];
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
	
	setCacheValue('dbIni', $dbIni);
	
	return $dbIni;
}

// +function database_delete
function database_delete(&$db, $id)
{
	$table	=	$db->table();
	$key 	=	$db->key();
	$id		=	makeIDS($id);
	$key 	=	dbMakeField($key);
	$table	=	dbMakeField($table);
	$db->execSQL("DELETE FROM $table WHERE $key IN ($id)");
}

// +function database_deleteByKey
function database_deleteByKey(&$db, $key, $id)
{
	$key	= dbMakeField($key);
	$table	= $db->table();
	$ids	= makeIDS($id);
	$sql	= "DELETE FROM $table WHERE $key IN ($ids)";
	return $db->exec($sql);
}

// +function database_sortByKey
function database_sortByKey(&$db, $sortField, $orderTable, $startIndex = 0)
{
	if (!is_array($orderTable)) return;
	
	$sortField	= dbMakeField($sortField);
	$key		= $db->key();
	$table		= $db->table();

	$sql	= '';
	$nStep	= (int)$startIndex;
	foreach($orderTable as $id){
		$nStep += 1;
		$id	= dbEncString($db, $id);
		$db->exec("UPDATE $table SET $sortField = $nStep WHERE $key=$id");
	}
}
?>