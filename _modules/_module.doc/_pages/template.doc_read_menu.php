<?
function doc_read_menu(&$db, &$search, &$data){
	if (!$db->rows()) return;
?>
<ul>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
?>
<li><a href="{$url}" title="{$data[title]}">{$data[title]}</a></li>
<? } ?>
</ul>
<? } ?>