<?
function doc_read_menu3(&$db, $val, &$search){
	if (!$db->rows()) return $search;
?>
<? while($data = $db->next()){
	$id		= $db->id();
    $url	= getURL($db->url());
	$class	= currentPage() == $id?' class="current"':'';
	$menu	= doc_menu($id, $data, true);
?>
{beginAdmin}<a href="{$url}" title="{$data[title]}">{$data[title]}</a><? endAdmin($menu, $val?false:true) ?>
<? } ?>
<? return $search; } ?>