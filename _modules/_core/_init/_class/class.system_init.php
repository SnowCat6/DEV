<?
class system_init
{
	static function init($cacheRoot)
	{
		set_time_limit(0);
		
		ob_start();
		$ini		= getCacheValue('ini');
		//	Initialize image path
		$localImagePath = $ini[':images'];
		if (!$localImagePath) $localImagePath = localRootPath.'/images';
		setCacheValue('localImagePath', $localImagePath);
		
		//	Задать путь хранения изображений
		define('images', $localImagePath);
		
		/*****************************************/
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
		//	Execute all configs and system files in siteFS
		$siteFS	= getCacheValue('siteFS');
		$classes= getCacheValue(':classes');

		//	Вычислить время модификации самого свежего файла
		$maxModifyTime	= 0;
		$localModules	= array();

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
				$maxModifyTime 		= max($maxModifyTime, $path[1]);
			}else
			//	Collect all possibly classes inside class. files
			if (preg_match('#(^|/)class\.([a-zA-Z\d_-]+)\.php#', $vpath, $val)){
				$class			= $val[2];
				$classes[$class]= $path[0];
				$content		= file_get_contents($path[0]);
				scanCotentForClass($classes, $content, $path[0]);
			}
		};
		
		//	Сохранить список классов
		setCacheValue(':classes', $classes);
		//	Сохранить список моулей
		setCacheValue('modules',$localModules);
		
		/***************************************************/
		//	Если файл модифицирован после создания общего файла, пересоздать общий файл
		$compiledPath	= $cacheRoot.'/'.localCompiledCode;
		if ($maxModifyTime > filemtime($compiledPath))
		{
			$modules	= '';
			event('config.modules',	$modules);
			if (!file_put_contents_safe($compiledPath, $modules)){
				echo "Error write compiled modules to: $compiledPath";
				die;
			};
		}

		$timeStart	= getmicrotime();
		ob_start();
		include_once($compiledPath);
		module('message:trace:modules', trim(ob_get_clean()));
		$time 		= round(getmicrotime() - $timeStart, 4);
		m("message:trace", "$time Included $compiledPath file");

		/***************************************************/
		//	Обработать модули
		event('config.start',	$cacheRoot);
		//	Скомпилировать шаблоны, скопировать измененные файлы
		event('config.prepare',	$cacheRoot);
		//	Инициализировать с загруженными модулями
		event('config.end',		$cacheRoot);
		ob_end_clean();

		$ini		= getCacheValue('ini');
		if (!$ini[':']['checkCompileFiles']){
			setCacheValue('siteFS', NULL);
		}
		
		return true;
	}
}
/******************************************/
function scanCotentForClass(&$classes, $content, $path)
{
	if (!preg_match_all('#(class|interface)\s+([\w\d_]+)\s*(|(extends|implements)\s+[\w\d_]+)\s*{#', $content, $val)) return;

	foreach($val[2] as $m){
		$classes[$m]= $path;
	}
	return true;
}
?>