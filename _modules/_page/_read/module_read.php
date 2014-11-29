<?
function module_read($name, $data)
{
	if (!is_array($data)) $data = array();
	
	if (access('write', "text:$name"))
	{
		$menu = $data['adminMenu'];
		if (!is_array($menu)) $menu = array();

		$bTop						= $data['bottom']?false:true;
		$menu[':class'][]			= 'adminGlobalMenu';
		$menu['Изменить#ajax_edit']	= getURL("read_edit_$name", makeQueryString($data['edit'], 'edit'));
		if ($data[':hasDelete']) $menu['Удалить#ajax'] = getURL("read_edit_$name", 'delete');
		
		$inline	= array(
			'action'	=>getURL("read_edit_$name", "ajax&inline"),
			'folder'	=>images."/$name",
			'dataName'	=>'document',
			'data'		=>$val
			);
		$menu[':inline']	= $inline;
	};

	beginAdmin($menu, $bTop);
	if (beginCache("$name.html", 'ini'))
	{
		$val	= file_get_contents(images."/$name.html");
		if (!is_string($val)) $val = file_get_contents(cacheRootPath."/images/$name.html");
		show($val?$val:$data['default']);

		endCache();
	}
	endAdmin();
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