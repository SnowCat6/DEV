<?
function module_read($name, $data)
{
	$textBlockName	= "$name.html";
	$filePath		= images."/$textBlockName";
	
	$menu = array();
	if (access('write', "text:$name")){
		$menu['Изменить#ajax_edit']	= getURL("read_edit_$name");
		$menu['Удалить#ajax']		= getURL("read_edit_$name", 'delete');
	};
	
	beginAdmin();
	if (beginCache($textBlockName)){
		@$val = file_get_contents($filePath);
		if (!is_string($val)) @$val = file_get_contents(localCacheFolder.'/'.localSiteFiles."/images/$textBlockName");
		event('document.compile', $val);
		echo $val;
		endCache($textBlockName);
	}
	endAdmin($menu, $data?false:true);
}

function module_read_access($mode, $data)
{
	switch($mode){
		case 'read': return true;
	}
	return hasAccessRole('admin,developer,writer,SEO');
}
?>
