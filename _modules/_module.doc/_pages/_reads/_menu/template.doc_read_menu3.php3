<?
function doc_read_menu3(&$db, $val, &$search){
	return showDocMenuDeep($db, $search, 2);
}
function doc_read_menu3_beginCache(&$db, $val, &$search){
	m('doc:menuTools');
	return menuBeginCache(3, $search);
}
?>