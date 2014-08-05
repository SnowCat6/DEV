<?
//	Обновить кеш свойств
function prop_clear($db, $id, $data)
{
	if ($id){
		$ids		= makeIDS($id);
		$ddb		= module('doc');
		$table		= $db->dbValue->table();
		$docTable	= $ddb->table();
		$sql		= "UPDATE $docTable AS d INNER JOIN $table AS p ON d.`doc_id` = p.`doc_id` SET `property` = NULL  WHERE p.`prop_id` IN ($ids)";
		$ddb->exec($sql);
	}else{
		$ddb		= module('doc');
		$docTable	= $ddb->table();
		$sql		= "UPDATE $docTable SET `property` = NULL";
		$ddb->exec($sql);
	}

	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();
	$sql	= "DELETE vs FROM $table2 AS vs WHERE `values_id` NOT IN (SELECT `values_id` FROM $table)";
	$db->exec($sql);
	
	$dbDoc		= module('doc');
	$docTable	= $dbDoc->table();
	$sql		= "DELETE v FROM $table AS v WHERE `doc_id` NOT IN (SELECT doc_id FROM $docTable)";
	$db->exec($sql);

	memClear();
	$a	= array();
	setCache('prop:nameCache', $a);
	clearCache('prop:');
}
?>