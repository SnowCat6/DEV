<?
function menuBeginCache($name, $search)
{
	if (userID()) return;
	$search['currentPage']	= currentPage();
	return	 hashData($search);
}
function showDocMenuDeep($db, &$search, $deep)
{
	$db2	= module('doc');
	$ids	= array();
	while($db->next()) $ids[] = $db->id();
	$db->seek(0);

	$tree	= module('doc:childs:' . $deep, array('parent' => $ids, 'type' => @$search['type']));
	$d		= &$tree[':data'];
?>
<ul>
<? while($data = $db->next())
{
	$id		= $db->id();
	$url	= $db->url();
	$fields= $data['fields'];
	$draggable	=docDraggableID($id, $data);
	$class = $id == currentPage()?'current':'';
	
	ob_start();
	@$childs	= &$tree[$id];
	if (showDocMenuDeepEx($db2, $childs, $d)) $class = 'parent';
	$p = ob_get_clean();
	
	if (@$c	= $fields['class']) $class .= " $c";
	if ($class) $class = " class=\"$class\"";
	if ($db->ndx == 1) $class .= ' id="first"';
?>
	<li<?= $class?>>
    	<a href="<? module("getURL:$url")?>"<?= $draggable?> title="<?= htmlspecialchars($data['title'])?>">
        <span>{$data[title]}</span><?= $note?>
        </a>
        <?= $p?>
	</li>
<? } ?>
</ul>
<?
	return $search;
}

function showDocMenuDeepEx($db2, &$tree, &$d)
{
	if (!$tree) return;
	
	$bFirst		= true;
	$bCurrent	= false;
	echo '<ul>';
	foreach($tree as $id => &$childs)
	{
		$data	= $d[$id];
		if ($data) $db2->setData($data);
		
		$data	= $db2->openID($id);
		$url	= getURL($db2->url($id));
		$fields= $data['fields'];
		$title	= htmlspecialchars($data['title']);
		
		ob_start();
		$class = $id == currentPage()?'current':'';
		if (showDocMenuDeepEx($db2, $childs, $d)) $class = 'parent';
		if ($class) $bCurrent = true;
		$p = ob_get_clean();
		
		if (@$c	= $fields['class']) $class .= " $c";
		if ($class) $class = " class=\"$class\"";
		if ($bFirst) $class .= ' id="first"';
		$bFirst = false;
		echo "<li$class><a href=\"$url\" title=\"$title\"><span>$title</span></a>$p</li>";
	}
	echo '</ul>';
	return $bCurrent;
}?>