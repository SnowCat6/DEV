<?
function doc_page_default(&$db, &$menu, &$data){
	$id = $db->id();
?>
{beginAdmin}
<h1>{$data[title]}</h1>
{beginCompile:page}
<div>{{prop:read=id:$id}}</div>
<p>{!$data[document][document]}</p>
{endCompile:page}
{endAdminTop}
<? } ?>