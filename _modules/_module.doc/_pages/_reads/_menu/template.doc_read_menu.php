<?
function doc_read_menu(&$db, $val, &$search){
	if (!$db->rows()) return;
?>
<ul>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$split	= $db->ndx == 1?' id="first"':'';
	$class	= currentPage() == $id?' class="current"':'';
?>
<li {!$split}{!$class}><a href="{$url}" title="{$data[title]}">{$data[title]}</a></li>
<? } ?>
</ul>
<? } ?>