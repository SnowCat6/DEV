<?
class dbWrite
{
	static function insertRow($db, $table, $array, $bDelayed)
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

	static function delete($db, $id)
	{
		$table	=	$db->table();
		$key 	=	$db->key();
		$id		=	makeIDS($id);
		$key 	=	dbMakeField($key);
		$table	=	dbMakeField($table);
		$db->execSQL("DELETE FROM $table WHERE $key IN ($id)");
	}
	
	static function deleteByKey($db, $key, $id)
	{
		$key	= dbMakeField($key);
		$table	= $db->table();
		$ids	= makeIDS($id);
		$sql	= "DELETE FROM $table WHERE $key IN ($ids)";
		return $db->exec($sql);
	}
	
	static function sortByKey($db, $sortField, $orderTable, $startIndex = 0)
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
};