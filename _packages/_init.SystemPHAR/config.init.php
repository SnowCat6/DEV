<?
addEvent('admin.settings.site',	'systemPHAR_tools');

$ini	= getCacheValue('ini');
if ($ini[':']['parSystem'] == 'yes' &&
	localCacheExists() &&
	extension_loaded("phar") &&
	extension_loaded("zip"))
	{
		addEvent('config.prepare:after',	'config_prepare_sytemPHAR');
		addEvent('config.rebase',			'config_rebase_sytemPHAR');
	}

//	Копирование дизайнерских файлов
function module_config_prepare_sytemPHAR(&$val, &$cacheRoot)
{
	//	USE PHAR & ZIP
	$zipName= "$cacheRoot/".localCompilePath.".zip";
	$zip 	= new ZipArchive();
	$zip->open($zipName, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
	
	$bOK	= true;
	$store	= array();
	$caches	= array('templates', 'pages', ':classes');
	foreach($caches as $cacheName)
	{
		$files	= getCacheValue($cacheName);
		if (!$files) continue;
		
		module_packZIP($zip, $cacheRoot, $files);
		$store[$cacheName] = $files;
		$bOK	&= count($files) > 0;
	}

	//	Check if all success compiled
	if ($zip->close() && $bOK)
	{
		foreach($store as $cacheName => $files){
			setCacheValue($cacheName,	$files);
		}
		delTree("$cacheRoot/".localCompilePath);
	}else{
		unlink($zipName);
	}
}

function module_config_rebase_sytemPHAR($val, $thisPath)
{
	$thisPath	= "phar://$thisPath";
	$nLen		= strlen($thisPath);
	$templates	= getCacheValue('templates');
	foreach($templates as &$templatePath){
		if (strncmp($templatePath, $thisPath, $nLen)) continue;
		$templatePath	= 'phar://' . cacheRoot . substr($templatePath, $nLen);
	}
	setCacheValue('templates', $templates);
	
	$pages		= getCacheValue('pages');
	foreach($pages as &$pagePath){
		if (strncmp($pagePath, $thisPath, $nLen)) continue;
		$pagePath	= 'phar://' . cacheRoot . substr($pagePath, $nLen);
	}
	setCacheValue('pages', $pages);
}

function module_packZIP(&$zip, $cacheRoot, &$files)
{
	$zipName	= $cacheRoot."/".localCompilePath . '.zip';
	foreach($files as &$path)
	{
		$fileName	= basename($path);
		 // добавляем файлы в zip архив
		if (!$zip->addFile($path, $fileName))
			return $files	= NULL;
		//	Заменить путь на новый
		
		$path	= "phar://$zipName/$fileName";
	}
}

?>