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

		//	Файлы для отслеживания изменений
		$GLOBALS['_COMPILED'] = array();

		/*****************************************/
		$siteFS	= getCacheValue('siteFS');
		array_walk($siteFS, function($path, $vpath) use(&$localModules)
		{
			//	Search configs
			if (preg_match('#(^|/)config\.(.*)\.php#', $vpath, $val)){
				include($path[0]);
				addCompiledFile($path[0]);
			}else
			//	Search modules
			if (preg_match('#^module_(.*)\.php$#', $vpath, $val)){
				$name	= $val[1];
				$localModules[$name] = $path[0];
			}
		});
		
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
/*******************************/
//	Поиск всех загружаемых модуле  и конфигурационных програм
function modulesInitialize($modulesPath, &$localModules)
{
	if (!$modulesPath) return;
	//	Поиск модулей в PHAR файлах
	$files	= findPharFiles($modulesPath);
	foreach($files as $name => $path){
		modulesInitialize($path, $localModules);
	}
	
	$dirs	= array();
	foreach (scanFolder($modulesPath) as $path)
	{
		$name	= basename($path);
		//	Сканировать поддиректории
		if (is_dir($path)){
			if ($name[0] == '_') $dirs[]	= $path;
		}else
		//	Поиск конфигурационных файлов и выполенение
		if (preg_match('#^config\..*\.php$#', $name)){
			include_once($path);
			addCompiledFile($path);
		}else
		//	Поиск модулей
		if (preg_match('#^module_(.*)\.php$#', $name, $v)){
			$name	= $v[1];
			$localModules[$name] = $path;
		}
	};

	foreach($dirs as $path){
		modulesInitialize($path, $localModules);
	};
}
/*****************************************/
function addCompiledFile($path)
{
	global $_COMPILED;
	$_COMPILED[$path]	= filemtime($path);
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