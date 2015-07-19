<?
function doc_search2($db, $val, $search)
{
	list($id, $group)	= explode(':', $val);
	if (!$group) $group	= 'productSearch';
	
	//	Откроем документ
	$data	= $db->openID($id);
	if (!$data) return;
	
	$names	= $data['fields']['any']['searchProps'];
	$names	= (is_array($names) && $names)?'!'.implode(',' , $names):NULL;
	///////////////////
	//	Табличка поиска
	//	Подготовим базовый SQL запрос
	$s				= $search;
	$s['parent*'] 	= "$id:catalog";
	if (! $s['type']) $s['type']		= 'product';
	$s['options']	= array(
		'groups'	=> $group,
		'names'		=> $names,
		'hasChoose'	=> true
	);
	removeEmpty($s);


	return module('doc:searchPanel:default2', $s);
}
?>

