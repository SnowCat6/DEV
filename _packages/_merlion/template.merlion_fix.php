<? function merlion_fix($val)
{
	$db	= module('doc');
	$db->sql	= '`deleted`=0';
	$db->order	= '`doc_id` ASC';
	
	$s	= array();
	$s['prop'][':import']	= 'merlion';
	$s['type']				= 'product';
	$db->open(doc2sql($s));

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
		$delete	= array();
		$priceLast	= 0;
		foreach($ids as $iid => &$p){
			if (!$id) $id = $iid;
			else $delete[$iid]	= $iid;
			$priceLast	= $price[$iid];
			dataMerge($prop, $p);
		}
		echo "<div>$title: $priceLast</div>";
		
		$d	= array();
		$d['fields']['any']['merlion']	= $prop;
		if ($priceLast) $d['price']	= $priceLast;
		foreach($ids as $iid => &$p){
			$d['deleted']	= $delete[$iid]?1:0;
			m("doc:update:$iid:edit", $d);
		}
		
//		foreach($delete as $id) m("doc:update:$id:delete");
		
		$db->clearCache();
	}
}?>