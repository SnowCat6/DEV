<?
//	show style="backgeound: url(...) center top"
//	add admin tools
function file_background($name, $data)
{
	if (!$name) $name = 'background';
	
	$ini	= getCache('background', 'ini');
	if (!is_array($ini)) $ini	= array();
	$file	= $ini[$name];
	if (is_null($file))
	{
		$folder	= images."/background/$name";
		$files	= getFiles($folder);
		list(, $file)	= each($files);
		$ini[$name]		= $file?$file:'';
		setCache('background', $ini, 'ini');
	}
	global $_CONFIG;
	$_CONFIG['background:'][$name]	= $file;
	if (!$file) return;
	
	$file	= imagePath2local($file);
	echo "style=\"background-image: url($file); background-position: center top; background-repeat: repeat-x\"";
}
?>