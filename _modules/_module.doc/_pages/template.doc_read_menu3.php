<?
function doc_read_menu3(&$db, &$search, &$data){
	if (!$db->rows()) return $search;
?>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$class	= currentPage() == $id?' class="current"':'';
	$menu	= doc_menu($id, $data);
?>
{beginAdmin}<a href="{$url}" title="{$data[title]}">{$data[title]}</a>{endAdmin}
<? } ?>
<? return $search; } ?>