<? function doc_read_menu(&$db, $val, &$search){
	if (!$db->rows()) return $search;
	$split		= ' id="first"';
?>
<ul>
<? while($data = $db->next()){
	$id			= $db->id();
	$draggable	= docDraggableID($id, $data);
    $url		= getURL($db->url());
	$class		= currentPage() == $id?'current':'';
	@$fields	= $data['fields'];
	if (@$c	= $fields['class']) $class .= " $c";
	if ($class) $class = " class=\"$class\"";
?>
<li {!$split}{!$class}><a href="{$url}" {!$draggable} title="{$data[title]}">{$data[title]}</a></li>
<? $split = ''; } ?>
</ul>
<? return $search; } ?>