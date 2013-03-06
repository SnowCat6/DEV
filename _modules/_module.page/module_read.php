<?
function module_read($name, $data)
{
	$cache			= getCacheValue('textBlocks');
	$textBlockName	= "$name.html";
	if (!isset($cache[$textBlockName]))
	{
		$val = @file_get_contents(images."/$textBlockName");
		event('document.compile', &$val);
		$cache[$textBlockName] = $val;
		setCacheValue('textBlocks', $cache);
	}
	
	$menu = array();
	if (access('write', "text:$name")){
		$menu['Изменить#ajax_edit']	= getURL("read_edit_$name");
		$menu['Удалить#ajax']		= getURL("read_edit_$name", 'delete');
	};
	
	beginAdmin();
	showDocument($cache[$textBlockName]);
	endAdmin($menu, $data?false:true);
}

function module_read_access($mode, $data)
{
	switch($mode){
		case 'read': return true;
	}
	return hasAccessRole('admin,developer,writer');
}
?>
