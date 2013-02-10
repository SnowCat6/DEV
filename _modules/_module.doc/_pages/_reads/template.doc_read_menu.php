<?
function doc_read_menu(&$db, &$search, &$data){
	if (!$db->rows()) return;
?>
<ul>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$split	= $db->ndx == 1?' id="first"':'';
?>
<li {!$split}><a href="{$url}" title="{$data[title]}"{!$class}>{$data[title]}</a></li>
<? } ?>
</ul>
<? } ?>