<?
class system_init
{
	static function init($cacheRoot)
	{
		ob_start();
		$ini 		= readIniFile(localConfigName);
		if (!is_array($ini)) $ini	= array();
		setCacheValue('ini', $ini);
	
		//	Initialize image path
		$localImagePath = $ini[':images'];
		if (!$localImagePath) $localImagePath = localRootPath.'/images';
		setCacheValue('localImagePath', $localImagePath);
		
		//	Задать путь хранения изображений
		define('images', $localImagePath);
	
		$a = array();
		//	Initialize event array
		setCacheValue('localEvent', $a);
		//	Access rule parse
		setCacheValue('localAccessParse', $a);
		//	Initialize url parse values
		$localURLparse = $ini[':URLparse'];
		if (!is_array($localURLparse)) $localURLparse = array();
		setCacheValue('localURLparse', $localURLparse);

		/*****************************************/
		$siteFS	= getCacheValue('siteFS');
		foreach($siteFS as $vpath => $path)
		{
			//	Search configs
			if (preg_match('#(^|/)config\.(.*)\.php#', $vpath, $val)){
				include($path[0]);
			}else
			//	Search modules
			if (preg_match('#^module_(.*)\.php$#', $vpath, $val)){
				$name				= $val[1];
				$localModules[$name]= $path[0];
			}
		};
		
		/***************************************************/
		//	Сохранить список моулей
		setCacheValue('modules',$localModules);
		//	Обработать модули
		event('config.start',	$cacheRoot);
		//	Скомпилировать шаблоны, скопировать измененные файлы
		event('config.prepare',	$cacheRoot);
		//	Инициализировать с загруженными модулями
		event('config.end',		$cacheRoot);
		ob_end_clean();
		
		return true;
	}
}
/******************************************/
function findPackages()
{
	$packages	= array();
	$folders	= array();

	$files		= findPharFiles('./');
	foreach($files as $path)	$folders[]	= "$path/_packages";

	$files		= findPharFiles('_packages');
	foreach($files as $path)	$folders[]	= $path;

	$folders[]	= '_packages';
	
	foreach(getDirs($folders) as $name => $path) $packages[$name] = $path;

	return $packages;
}
?>