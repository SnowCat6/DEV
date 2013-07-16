<? function doc_read_map($db, $val, $search)
{
	showMapTree($db, 0, 2);
	return $search;
} ?>
<? function showMapTree(&$db, $deep, $maxDeep)
{
	if (!$db->rows()) return;
	
	echo $deep?'<ul>':'<ul class="menu map">';
	while($data = $db->next())
	{
		$id		= $db->id();
		$name	= htmlspecialchars($data['title']);
		$url	= getURL($db->url());
		$drag	= docDraggableID($id, $data);
		echo "<li><a href=\"$url\"$drag>$name</a>";
		
		if ($deep < $maxDeep)
		{
			$iid	= $db->id();
			$ddb	= module('doc');
			$s		= array('parent'=>$iid, 'type' => 'page,catalog');
			$ddb->open(doc2sql($s));
			
			startDrop($s, 'map');
			if ($deep == $maxDeep - 2){
				showMapTree($ddb, $deep+1, $maxDeep);
			}else{
				showMapTree2($ddb, $deep+1, $maxDeep);
			}
			endDrop($s, 'map');
		}
		echo '</li>';
	}
	echo '</ul>';
}?>
<? function showMapTree2(&$db, $deep, $maxDeep){
	echo '<ul>';
	$split = '';
	while($data = $db->next())
	{
		$id		= $db->id();
		$name	= htmlspecialchars($data['title']);
		$url	= getURL($db->url());
		$drag	= docDraggableID($id, $data);
		echo "$split<a href=\"$url\"$drag>$name</a>";

		$split = ' | ';
	}
	echo '</ul>';
}?>