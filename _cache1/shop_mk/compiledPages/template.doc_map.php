<? function doc_map($db, $template, $data){

	if (!$template) $template = 'map';
	
	$s	= array();
	$s['prop']['!place']= 'map';
	module("doc:read:$template", $s);
} ?>