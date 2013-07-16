<? function doc_config($db, $val, $data){
	switch($val){
	case 'type':
		$docTypes = getCacheValue('docTypes');
		if (!is_array($docTypes)) $docTypes = array();
		$docTypes = array_merge($docTypes, $data);
		setCacheValue('docTypes', $docTypes);
	break;
	case 'template':
		$docTemplates = getCacheValue('docTemplates');
		if (!is_array($docTemplates)) $docTemplates = array();
		$docTemplates = array_merge($docTemplates, $data);
		setCacheValue('docTemplates', $docTemplates);
	break;
	}
}?>
