<?
function doc_page_default(&$db, &$menu, &$data){
	$id = $db->id();
?>
{beginAdmin}
<h1>{$data[title]}</h1>
<div>{{prop:read=id:$id;group:Свойства товара}}</div>
<div>{{prop:read=id:$id;group:Характеристики}}</div>
{endAdminTop}
<? } ?>