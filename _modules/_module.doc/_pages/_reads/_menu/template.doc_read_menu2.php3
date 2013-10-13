<?
function doc_read_menu2(&$db, $val, &$search){
	return showDocMenuDeep($db, $search, 1);
}
function doc_read_menu2_beginCache(&$db, $val, &$search){
	m('doc:menuTools');
	return menuBeginCache(2, $search);
}
?>