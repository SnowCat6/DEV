<? function doc_read_menu(&$db, $val, &$search){
	if (!$db->rows()) return $search;
	$split		= ' id="first"';
?>
<ul>
<? while($data = $db->next()){
	$id			= $db->id();
	$draggable	=docDraggableID($id, $data);
    $url		= getURL($db->url());
	$class		= currentPage() == $id?' class="current"':'';
?>
<li {!$split}{!$class}><a href="{$url}" {!$draggable} title="{$data[title]}">{$data[title]}</a></li>
<? $split = ''; } ?>
</ul>
<? return $search; } ?>