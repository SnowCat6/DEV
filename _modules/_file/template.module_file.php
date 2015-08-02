<?
function module_file($val, &$data)
{
	//	Попробовать загрузить дополнительный модуль
	@list($val, $v)=explode(':', $val, 2);
	$fn = getFn("file_$val");
	if ($fn) return $fn($v, $data);
}
//	+function file_storage
function file_storage($mode, &$ev)
{
	$id		= $ev['id'];
	$name	= $ev['name'];

	if ($id && $id != 'ini') return;
	
	switch($mode){
	case 'set':
		$storage	= getIniValue(':storage');
		if (!is_array($storage)) $storage = array();
		$storage[$name]	= base64_encode(serialize($ev['content']));
		setIniValue(':storage', $storage);
		return true;
	case 'get':
		$storage		= getIniValue(':storage');
		$content		= $storage[$name];
		$ev['content']	= unserialize(base64_decode($content));
		return true;
	}
}
//	+function module_file_file_access
function module_file_file_access($mode, $data)
{
	$path	= localRootPath . imagePath2local($data[1]);
	if (strncmp($path, images, strlen(images)) != 0) return;

	return hasAccessRole('admin,developer,writer');
}
?>