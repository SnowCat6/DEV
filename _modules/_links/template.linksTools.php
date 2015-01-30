<?
//	+function links_set
function links_set($db, $nativeURL, $links)
{
	if (!is_array($links)) return;
	
	$sql	= array();
	foreach($links as $ix => &$rawLink)
	{
		$rawLink	= links_quote($db, $val, $rawLink);
		if ($rawLink){
			$sql[]	= dbEncString($db, $rawLink);
		}else{
			unset($links[$ix]);
		}
	}

	if (!$sql)
		return links_delete($db, $nativeURL);

	$undo	= links_get($db, $nativeURL);
	addUndo("Ссылки '$nativeURL' изменены", "links:$nativeURL",
		array('action' => "links_undo:$nativeURL", 'data' => $undo)
	);

	$sql	= implode(',', $sql);
	$v		= dbEncString($db, $nativeURL);
	$sql	= "`link` IN ($sql) OR `nativeURL` = $v";
	
	$db->open($sql);
	while($data = $db->next())
	{
		$link	= $db->id();
		$ix		= array_search($link, $links);
		if ($ix === false){
			$db->delete($link);
			continue;
		}
		if ($data['nativeURL'] != $nativeURL ||
			$data['user_id']	!= userID())
			{
			$d	= array();
			$d['nativeURL']	= $nativeURL;
			$d['user_id']	= userID();
			$db->setValues($db->id(), $d);
		}
		unset($links[$ix]);
	}
	foreach($links as $link)
	{
		$d	= array();
		$d['link']		= $link;
		$d['nativeURL']	= $nativeURL;
		$d['user_id']	= userID();
		$db->update($d);
	}
	
	$a	= NULL;
	setCache('links', 		$a, 'ini');
	setCache('nativeLink',	$a, 'ini');
}
//	+function links_add
function links_add(&$db, $nativeURL, $url)
{
	$links		= links_get($db, $nativeURL);
	$links[$url]= $url;
	return links_set($db, $nativeURL, $links);
}
//	+function links_delete
function links_delete(&$db, $nativeURL)
{
	$undo	= links_get($db, $nativeURL);
	addUndo("Ссылкаи '$nativeURL' удалены", "links:$nativeURL",
		array('action' => "links_undo:$nativeURL", 'data' => $undo)
	);
	
	$db->deleteByKey('nativeURL', $nativeURL);
	reloadLinks($db);
}
//	+function links_quote
function links_quote(&$db, $val, $url)
{
	$url= preg_replace('#^.*://#',	'', $url);
	$a	= preg_quote("-._~/[]()", '#');
	$url= preg_replace("#[^a-zA-Z\d$a]#",	'',	$url);
	
	if (!$url) return;

	$url = strtolower(trim($url, '/'));
	return $url?"/$url":'/';
}
//	+function links_undo
function links_undo($db, $nativeURL, $links)
{
	if (!access('write', 'undo')) return;
	links_set($db, $nativeUR, $links);
	return true;
}
?>