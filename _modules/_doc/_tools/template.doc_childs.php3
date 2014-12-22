<?
function doc_childs($db, $deep, &$search)
{
	$key	= $deep.':'.hashData($search);
	$cache	= getCache($key, 'file');
	if ($cache) return $cache;

	$tree	= array();
	$childs	= array();
	$d		= array();
	$deep	= (int)$deep;
	if ($deep < 1) return array();

	if (!$search['type']) $search['type'] = 'page,catalog';

	for($ix = 0; $ix < $deep; ++$ix)
	{
		$ids	= array();
		$db->open(doc2sql($search));
		while($data	= $db->next())
		{
			unset($data['cache']);
			$id		= $db->id();
			$ids[]	= $id;
			$d[$id]	= $data;
			$prop	= module("prop:get:$id");

			$parents= explode(', ', $prop[':parent']);
			foreach($parents as $parent){
				$parent = (int)$parent;
				$childs[$parent][$id] = array();
				if ($ix == 0) $tree[$parent] = array();
			}
		}
		$search	= array('parent'=>$ids, 'type'=>$search['type']);
	}

	foreach($tree as $parent => &$c)
	{
		$c		= $childs[$parent];
		if (!is_array($c)) $c = '';
		
		$stop	= array();
		docMaketree($tree, $childs, $stop);
	}
	$tree[':childs']= $childs;
	$tree[':data']	= $d;
	setCache($key, $tree, 'file');
	
	return $tree;
}

function docMakeTree(&$tree, &$childs, &$stop)
{
	foreach($tree as $parent => &$c)
	{
		if (isset($stop[$parent])) continue;
		$stop[$parent] = true;
		
		$c = $childs[$parent];
		if (!is_array($c)) $c = '';
		else docMakeTree($c, $childs, $stop);
	}
}
?>