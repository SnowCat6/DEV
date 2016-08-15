<?
class system_init
{
	static function init($cacheRoot)
	{
		ob_start();
		$ini		= getCacheValue('ini');
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
		//	Execute all configs and system files in siteFS
		$siteFS	= getCacheValue('siteFS');
		$classes= getCacheValue(':classes');
		$localModules	= array();
		//	Secondary scan
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
			}else
			//	Collect all possibly classes inside class. files
			if (preg_match('#(^|/)class\.([a-zA-Z\d_-]+)\.php#', $vpath, $val)){
				$class			= $val[2];
				$classes[$class]= $path[0];
				$content		= file_get_contents($path[0]);
				scanCotentForClass($classes, $content, $path[0]);
			}
		};
		setCacheValue(':classes', $classes);

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