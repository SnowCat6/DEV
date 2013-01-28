<?
function module_read($name, $data)
{
	$textBlockName = "$name.html";
	if (!testCacheValue($textBlockName)){
		setCacheValue("text/$textBlockName", @file_get_contents(images."/$textBlockName"));
	}
	
	$menu = array();
	if (access('write', "text:$name")){
		$menu['Изменить#popup']	= getURL("read_edit_$name");
	};
	
	beginAdmin();
	echo getCacheValue("text/$textBlockName");
	endAdmin($menu);
}

function module_read_access($mode, $data)
{
	switch($mode){
		case 'write':
		@$user = $GLOBALS['_CONFIG']['user']['data'];
		return @$user['access'] == 'admin';
	}
	return true;
}
?>
