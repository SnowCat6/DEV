<?
function doc_read_menu2(&$db, &$search, &$data){
	if (!$db->rows()) return $search;
?>
<ul>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$class	= currentPage() == $id?' class="current"':'';
	$menu	= doc_menu($id, $data);
?>
<li {!$class}>{beginAdmin}<a href="{$url}" title="{$data[title]}">{$data[title]}</a>{endAdmin}</li>
<? } ?>
</ul>
<? return $search; } ?>