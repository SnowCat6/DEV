<?
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