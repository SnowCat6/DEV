<? function doc_map($db, $template, $data){

	if (!$template) $template = access('write', 'doc:0')?'mapAdmin':'map';
	
	$s	= array();
	$s['prop']['!place']= 'map';
	module("doc:read:$template", $s);
} ?>