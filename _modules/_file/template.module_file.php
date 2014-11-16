<?
function module_file($val, $data=''){
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
	$content= &$ev['content'];

	if ($id && $id != 'ini') return;
	
	switch($mode){
	case 'set':
		$storage	= getIniValue(':storage');
		if (!is_array($storage)) $storage = array();
		$storage[$name]	= base64_encode(serialize($content));
		setIniValue(':storage', $storage);
		return true;
	case 'get':
		$storage	= getIniValue(':storage');
		$content	= $storage[$name];
		$content	= unserialize(base64_decode($content));
		return true;
	}
}

?>