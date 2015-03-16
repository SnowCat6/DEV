<?
function doc_search($db, $val, $search)
{
	list($id, $group)	= explode(':', $val);
	if (!$group) $group	= 'productSearch';
	
	//	Откроем документ
	$data	= $db->openID($id);
	if (!$data) return;
	
	///////////////////
	//	Табличка поиска
	//	Подготовим базовый SQL запрос
	$s				= $search;
	$s['parent*'] 	= "$id:catalog";
	$s['type']		= 'product';
	$s['options']	= array(
		'groups'	=> $group,
		'hasChoose'	=> true
	);
	removeEmpty($s);

	return module('doc:searchPanel', $s);
}
?>

