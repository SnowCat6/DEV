<? function doc_read_map($db, $val, $search)
{
	showMapTree($db, 0, 2);
	return $search;
} ?>
<? function showMapTree(&$db, $deep, $maxDeep)
{
	if (!$db->rows()) return;
	
	echo $deep?'<ul>':'<ul class="menu map">';
	while($data = $db->next()){
		$name	= htmlspecialchars($data['title']);
		$url	= getURL($db->url());
		echo "<li><a href=\"$url\">$name</a>";
		if ($deep < $maxDeep){
			$iid	= $db->id();
			$ddb	= module('doc');
			$ddb->open(doc2sql(array('parent'=>$iid, 'type' => 'page,catalog')));
			
			if ($deep == $maxDeep - 2){
				showMapTree($ddb, $deep+1, $maxDeep);
			}else{
				showMapTree2($ddb, $deep+1, $maxDeep);
			}
		}
		echo '</li>';
	}
	echo '</ul>';
}?>
<? function showMapTree2(&$db, $deep, $maxDeep){
	echo '<ul>';
	$split = '';
	while($data = $db->next()){
		$name	= htmlspecialchars($data['title']);
		$url	= getURL($db->url());
		echo "$split<a href=\"$url\">$name</a>";

		$split = ' | ';
	}
	echo '</ul>';
}?>