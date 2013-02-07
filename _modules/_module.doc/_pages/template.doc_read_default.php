<?
function doc_read_default(&$db, &$search, &$data){
	startDrop($search, '');
	if (!$db->rows()) return endDrop($search, '');
?>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$menu	= doc_menu($id, $data);
?>
{beginAdmin}
<div><a href="{$url}">{$data[title]}</a></div>
{endAdminTop}
<? } ?>
<? endDrop($search) ?>
<? } ?>