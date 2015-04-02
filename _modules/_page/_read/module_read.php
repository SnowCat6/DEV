<?
function module_read($name, $data)
{
	if (!is_array($data)) $data = $data == 'bottom'?array('bottom' => 'bottom'):array();
	
	if (access('write', "text:$name"))
	{
		$menu = $data['adminMenu'];
		if (!is_array($menu)) $menu = array();

		$menu[':type']				= $data['bottom']?'bottom':'';
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

	beginAdmin($menu);
	if (beginCache("$name.html", 'ini'))
	{
		$val	= module("read_get:$name");
		$val	= $val?$val:$data['default'];
		if ($data['fx']) $val = m("text:$data[fx]|show", $val);
		show($val);
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
	$path	= $data[1];
	$name	= substr($path, strlen(images)+1);
	$name	= explode('/', $name);
	$name	= $name[0];
	if (!is_dir(images."/$name") &&
		!is_file(images."/$name.html")) return false;
	return access($mode, "text:$name");
}
?>
