<?
/*
global $_TRANSLATOR;
$_TRANSLATOR		= NULL;

addEvent('config.copyFile',	'translator_copyFile');
addEvent('page.compile',	'translator_page_compile');

function module_translator_copyFile($val, &$ev)
{
	if (!preg_match('#^translate\.#', basename($ev['source'])))
		return;
	$ev['source']	= '';
}

function module_translator_page_compile($val, &$ev)
{
	loadPageTranslator($ev['source']);
	$thisPage	= &$ev['content'];
	//	(_LABEL_#_DEFAULT TEXT_)
	$thisPage	= preg_replace_callback('/\(([\w\d\.]*)#([^])]*)\)/m', 
	function($val)
	{
		global $_TRANSLATOR;
		if (is_null($_TRANSLATOR))
			return $val[2];
	
		$id		= $val[1];
		$value	= $val[2];
		if (!$id) $id = $value;
		$v	= $_TRANSLATOR['id'][$id];
	
		if ($v) return $v;
		
		return $val[2];
	}, $thisPage);
}
function loadPageTranslator($filePath)
{
	global $_TRANSLATOR;
	
	$lang	= 'en';
	$dir	= explode('/', dirname($filePath));
	while($dir){
		$path	= implode('/', $dir);
		array_pop($dir);
		
		if ($_CONFIG[':translator'][$path]) return;
		$_CONFIG[':translator'][$path] = true;

		$files	= getFiles($path, "^translate\.$lang\..*\.txt");
		foreach($files as $path){
			loadPageTranslatorFile($_TRANSLATOR, $path);
		}
	}
}
function loadPageTranslatorFile(&$_TRANSLATOR, $path)
{
	$file	= file($path);
	foreach($file as $row)
	{
		$row	= rtrim($row);
		if (preg_match("/^#(.+)\t(.*)/", $row, $val))
		{
			$_TRANSLATOR['id'][$val[1]] = $val[2];
		};
	}
}
*/
?>