<?
//	Корреткировка ссылок на ресурсы для использования в редактировании и обратно
function module_prepare($val, &$data)
{
	list($val, $folder)	= explode(':', $val);
	switch($val)
	{
	case '2public':	//	Преобразовать пути к ресурсам из относительного пути хранения в пути с полным наименованием
			return local2public($data, $folder);
	case '2local':	//	Преобразовать пути с полным наименованием в пути для хранения
			return public2local($data, $folder);
	case '2fs':		//	Преобразовать пути к ресурсам из относиьельного в пути файловой системы
			return local2fs($data, $folder);
	}
}
//	Скорректировать ссыли так, чтобы указывали на абсолютный путь к файлу на сайте
//	images/image.jpg => /dev/_sires/siteFolder/images/image.jpg
function local2fs(&$data)
{
	if (is_array($data)){
		foreach($data as $name => &$v) local2fs($v);
	}else{
		$publicPath = globalRootPath.'/'.localRootPath.'/';
		$data		= preg_replace('#(src\s*=\s*[\'\"])(?!\w+://)([\w\d])#i', "\\1$publicPath\\2", $data);
	}
}
//	Подготовить для отображения в редакторе
//	Скорректировать ссыли так, чтобы указывали на абсолютный путь к файлу на сайте
//	images/image.jpg => /dev/_sites/siteFolder/images/image.jpg
function local2public(&$data)
{
	if (is_array($data)){
		foreach($data as $name => &$v) local2public($v);
	}else{
		$publicPath = localRootURL.'/';
		$data		= preg_replace('#(src\s*=\s*[\'\"])(?!\w+://)([\w\d])#i', "\\1$publicPath\\2", $data);
		//	make snippet visual
		if (module('snippets:visual')){
			$data	= preg_replace('#\[\[([^\]]+)\]\]#', '<p class="snippet \\1"></p>', $data);
		}
	}
}
//	Подготовить для хранения в базе данных
//	Скорректировать ссылки так, чтобы абсолютный путь к файлу, стал относительным
//	/dev/_sires/siteFolder/images/image.jpg => images/image.jpg
function public2local(&$data, $baseFolder)
{
	if (is_array($data)){
		foreach($data as $name => &$v) public2local($v, $baseFolder);
		return;
	}

	$publicPath	= preg_quote(localRootURL.'/', '#');
	$publicPath2= preg_quote(globalRootURL.'/', '#');
	$serverURL	= preg_quote("http://$_SERVER[HTTP_HOST]", '#');

	$data	= preg_replace("#([\'\"])$serverURL#i",			"\\1", 		$data);
	$data	= preg_replace("#([\'\"])(?!//)[/]?$publicPath#i",	"\\1", 	$data);
	$data	= preg_replace("#([\'\"])(?!//)[/]?$publicPath2#i",	"\\1", 	$data);
	$data	= preg_replace("#([\'\"])(?!//)/([^\'\"]*)#i",	"\\1\\2", 	$data);
	//	snippet back to normal
	if (module('snippets:visual')){
		$data	= preg_replace('#<p\s+class\s*=\s*"snippet\s*([^"]+)"\s*>[^<]*</p>#', '['.'['.'\\1'.']'.']', $data);
	}
	//	Автоматически копировать ресурсы с внешнего источника в baseFolder папку
	moduleEx("contentCopy:$baseFolder", $data);
}
?>
<?
//	Автоматически копировать ресурсы с внешнего источника в baseFolder папку
function module_contentCopy($baseFolder, &$data)
{
//	print_r($baseFolder); die;
	if (!$baseFolder) return;
	
	$compile	= new tagImageCopy('img');
	$data		= $compile->compile($data, array(
		'baseFolder'	=> $baseFolder
	));
}
?>

<?
class tagImageCopy extends tagCompileSingle
{
	function onTagCompile($tagName, $property, $content, $options)
	{
		$p		= self::makeLower($property);
		$src	= strtolower($p['src']);
		
		if (strncmp($src, 'http://', 7) &&
			strncmp($src, 'https://', 8))	return;
		
		$curl	= new Curl();
		$image	= $curl->get($p['src']);
		if (!$image) return;
		
		$imageName	= basename($p['src']);
		moduleEx('translit', $imageName);
		$filePath	= "$options[baseFolder]/$imageName";

		unlinkAutoFile($filePath);
		makeDir($options['baseFolder']);
		file_put_contents($filePath, $image);
		fileMode($filePath);
		event('file.upload', $filePath);
		
		$p['src']	= ltrim(imagePath2local($filePath), '/');
		return self::makeTag($tagName, $p, $content);
	}
}
?>