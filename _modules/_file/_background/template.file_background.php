<?
//	show style="backgeound: url(...) center top"
//	add admin tools
function file_background($name, $data)
{
	if (!$name) $name = 'background';
	if (strpos($name, '/') === false)
		$name	= "background/$name";
	
	$ini	= getCache('background', 'ini');
	if (!is_array($ini)) $ini	= array();
	$file	= $ini[$name];
	if (is_null($file))
	{
		$folder	= images."/$name/background";
		$files	= getFiles($folder);
		list(, $file)	= each($files);
		$ini[$name]		= $file?$file:'';
		setCache('background', $ini, 'ini');
	}
	$bk	= config::get('background:');
	$bk[$name] 	= $file;
	config::set('background:', $bk);
	if (!$file) return;
	
	$file	= imagePath2local($file);
	echo "style=\"background-image: url($file); background-position: center top; background-repeat: repeat-x\"";
}
?>