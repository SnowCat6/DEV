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
		if (!is_string($val)) @$val = file_get_contents(cacheRootPath."/images/$textBlockName");
		event('document.compile', $val);
		echo $val;
		endCache($textBlockName);
	}
	if ($menu){
		$inline	= array(
			'action'=>getURL("read_edit_$name", "ajax&inline"),
			'folder'=>images."/$name",
			'dataName'=>'document'
			);
		$menu[':inline']	= $inline;
	}
	endAdmin($menu, $data?false:true);
}

function module_read_access(&$mode, &$data)
{
	switch($mode){
		case 'read': return true;
	}
	return hasAccessRole('admin,developer,writer,SEO');
}
function module_read_file_access(&$mode, &$data)
{
	$name	= $data[1];
	if (!is_dir(images."/$name") &&
		!is_file(images."/$name.html")) return false;
	return access($mode, "text:$name");
}
?>
