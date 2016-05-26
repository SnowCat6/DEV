<?
function module_read($name, $data)
{
	if (strpos($name, '/') === false)
		$name	= "reads/$name";

	if (hasAccessRole('edit') && access('write', "text:$name"))
		return module("readAdmin:$name", $data);

	if (!beginCache("read/$name", 'ini')) return;

	$val	= module("read_get:$name");
	$val	= $val?$val:$data['default'];
	if ($data['fx']) $val = m("text:$data[fx]|show", $val);
	show($val);
	
	endCache();
}
//	+function module_read_get
function module_read_get($name, $content)
{
	$path	= images."/$name.html";
	$val	= file_get_contents($path);
	if ($val) return $val;

	return file_get_contents(cacheRootPath."/images/$name.html");
}

function module_read_access($mode, &$data)
{
	return hasAccessRole('admin,developer,writer,SEO');
}
function module_read_file_access(&$mode, &$data)
{
	$readPath	= images;
	$path		= localRootPath . imagePath2local($data[1]);
	if (strncmp(images, $path, strlen(images)) != 0) return;
	return access($mode, "text:");
}
?>
