<? function merlion_fix($val)
{
	if (!hasAccessRole('admin')) return;
	
	if (testValue('fixDuplicate'))	merlionFixDuplicates();
	if (testValue('removeDeleted'))	merlionFixRemoveDeleted();
	if (testValue('RemoveUnused'))	merlionRemoveUnusedImages();
}
function merlionRemoveUnusedImages(){
	$db			= module('doc');
	$db->sql	= '`deleted` = 0';
	$count		= 0;
	$pass		= 0;

	$dir		= $db->images;
	$d			= opendir($dir);
	
	while($file = readdir($d)){
		if (sessionTimeout() < 1) break;
		if ($file == '..' || $file == '.') continue;
		$db->clearCache();
		++$pass;
		if ($db->openID($file)) continue;
		delTree("$dir/$file");
		++$count;
	}
	echo "Deleted folders $count, passed $pass";
}
function merlionFixRemoveDeleted()
{
	$db			= module('doc');
	$db->sql	= '`deleted`=1';
	$count		= 0;
	$db->open();
	while($data = $db->next()){
		$id	= $db->id();
		m("doc:update:$id:delete");
		$db->clearCache();
		++$count;
		if (sessionTimeout() < 1) break;
	}
	echo "Deleted $count";
}
function merlionFixDuplicates()
{
	$db			= module('doc');
	$db->sql	= '`deleted`=0';
	$db->order	= '`doc_id` ASC';
	
	$s	= array();
	$s['prop'][':import']	= 'merlion';
	$s['type']				= 'product';
	$db->open(doc2sql($s));

	$count		= 0;
	$price		= array();
	$avalible	= array();
	while($data = $db->next())
	{
		$iid	= $db->id();
		$prop	= $data['fields']['any']['merlion'];
		$price[$iid]	= $data['price'];
		$parentID	= $prop[':merlion_parentID'];
		$itemID		= $prop[':merlion_itemID'];

//		if (!$parentID && !$itemID)
			$avalible[$data['title']][$iid]	= $prop;
		
		$db->clearCache();
	}

	foreach($avalible as $title => $ids)
	{
		if (count($ids) < 2) continue;

		$id		= 0;
		$prop	= array();
		$propBase	= array();
		$delete		= array();
		$priceLast	= 0;
		foreach($ids as $iid => &$p){
			if (!$id){
				$id			= $iid;
				$propBase	= $p;
			}else $delete[$iid]	= $iid;
			
			$priceLast	= $price[$iid];
			dataMerge($prop, $p);
			
			//	Не менять свойства, которых нет у старого товара
			$prop[':merlion_image']		= $propBase[':merlion_image'];
			$prop[':merlion_property']	= $propBase[':merlion_property'];
		}
		echo "<div>$title: $priceLast</div>";
		
		$d	= array();
		$d['fields']['any']['merlion']	= $prop;
		if ($priceLast) $d['price']		= $priceLast;
		foreach($ids as $iid => &$p){
			$d['deleted']	= $delete[$iid]?1:0;
			$d['visible']	= $priceLast?1:0;
			m("doc:update:$iid:edit", $d);
		}
		
//		foreach($delete as $id) m("doc:update:$id:delete");
		
		++$count;
		if (sessionTimeout() < 1) break;
		$db->clearCache();
	}
	echo "Updated $count";
}
?>