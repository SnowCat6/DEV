<?
function doc_read_catalog(&$db, &$search, &$data){
	if (!$db->rows()) return;
?>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data);
?>
<p>
{beginAdmin}
<a href="{$url}">{$data[title]}</a>
<div>{{prop:read=id:$id;group:Свойства товара}}</div>
{endAdminTop}
</p>
<? } ?>
<? } ?>