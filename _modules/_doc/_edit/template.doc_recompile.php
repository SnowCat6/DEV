<?
function doc_recompile($db, $id, $data)
{
	$ids = makeIDS($ids);
	if ($ids)
	{
		$db->setValue($ids, 'cache', NULL, false);
	}else{
		$table	= $db->table();
		$db->exec("UPDATE $table SET `cache` = NULL");
		
		$ddb	= module('doc');
		$db->open("`searchDocument` IS NULL");
		while($data = $db->next()){
			$d	= array();
			$d['searchTitle']	= docPrepareSearch($data['title']);
			$d['searchDocument']= docPrepareSearch($data['document']);
			$ddb->setValues($db->id(), $d);
			$db->clearCache();
		}
	}
	clearCache();
}
?>