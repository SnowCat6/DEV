<?
function doc_read_menu(&$db, $val, &$search){
	return showDocMenuDeep($db, $search,  0);
}
function doc_read_menu_beginCache(&$db, $val, &$search)	{
	m('doc:menuTools');
	return menuBeginCache(1, $search);
}
?>