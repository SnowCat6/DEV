<?
function doc_read_menu(&$db, $val, &$search){ return showDocMenuDeep($db, $search,  0); }
function doc_read_menu2(&$db, $val, &$search){ return showDocMenuDeep($db, $search, 1); }
function doc_read_menu3(&$db, $val, &$search){ return showDocMenuDeep($db, $search, 3); }

function showDocMenuDeep($db, &$search, $deep)
{
	$db2	= module('doc');
	$ids	= array();
	while($db->next()) $ids[] = $db->id();
	$db->seek(0);

	$tree = module('doc:childs:' . $deep, array('parent' => $ids, 'type' => @$search['type']));
?>
<ul>
<? while($data = $db->next()){
	$id		= $db->id();
	$url	= $db->url();
	@$fields= $data['fields'];
	$draggable	=docDraggableID($id, $data);
	$class = $id == currentPage()?'current':'';
	
	ob_start();
	@$childs	= &$tree[$id];
	if (showDocMenuDeepEx($db2, $childs)) $class = 'parent';
	$p = ob_get_clean();
	
	if (@$c	= $fields['class']) $class .= " $c";
	if ($class) $class = " class=\"$class\"";
?>
    <li {!$class}><a href="{{getURL:$url}}"{!$draggable}><span>{$data[title]}</span>{!$note}</a>
    {!$p}
    </li>
<? } ?>
<? return $search; } ?>
<? function showDocMenuDeepEx($db, &$tree)
{
	if (!$tree) return;
	
	$bCurrent = false;
	echo '<ul>';
	foreach($tree as $id => &$childs)
	{
		$data	= $db->openID($id);
		$url	= getURL($db->url($id));
		@$fields= $data['fields'];
		$title	= htmlspecialchars($data['title']);
		
		ob_start();
		$class = $id == currentPage()?'current':'';
		if (showDocMenuDeepEx($db2, $childs)) $class = 'parent';
		if ($class) $bCurrent = true;
		$p = ob_get_clean();
		
		if (@$c	= $fields['class']) $class .= " $c";
		if ($class) $class = " class=\"$class\"";
		echo "<li$class><a href=\"$url\"><span>$title</span></a>$p</li>";
	}
	echo '<ul>';
	return $bCurrent;
}?>