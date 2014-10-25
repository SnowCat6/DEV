<?
addEvent('admin.settings.site',	'systemPHAR_tools');

//	Копирование дизайнерских файлов
addEvent('config.prepare:after',	'config_prepare_sytemPHAR');
function module_config_prepare_sytemPHAR(&$val, &$cacheRoot)
{

	//	USE PHAR & ZIP
	$ini	= getCacheValue('ini');
	if ($ini[':']['parSystem'] == 'yes' &&
		localCacheExists() &&
		extension_loaded("phar") &&
		extension_loaded("zip"))
	{
		$zipName= "$cacheRoot/".localCompilePath.".zip";
		
		$zip 	= new ZipArchive();
		$zip->open($zipName, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
		
		//	Сохранить названия модулей
		$files	= getCacheValue('templates');
		module_packZIP($zip, $files);
		//	Сохранить названия страниц
		$files2	= getCacheValue('pages');
		module_packZIP($zip, $files2);

		//	Check if all success compiled
		if ($zip->close() && $files && $files2)
		{
			setCacheValue('templates',	$files);
			setCacheValue('pages', 		$files2);
//			delTree("$cacheRoot/".localCompilePath);
		}else{
			unlink($zipName);
		}
	}
}

function module_packZIP(&$zip, &$files)
{
	$zipName	= cacheRoot."/".localCompilePath . '.zip';
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