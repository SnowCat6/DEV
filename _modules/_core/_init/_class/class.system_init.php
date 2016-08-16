<?
class system_init
{
	static function init($cacheRoot)
	{
		set_time_limit(0);
		
		ob_start();
		$bOK	= true;
		$ini	= getCacheValue('ini');
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

		//	Initialize classes and common modules by siteFS
		self::init_system_files($cacheRoot);

		//	Обработать модули
		event('config.start',	$cacheRoot);
		//	Скомпилировать шаблоны, скопировать измененные файлы
		event('config.prepare',	$cacheRoot);
		//	Инициализировать с загруженными модулями
		event('config.end',		$cacheRoot);
		
		$bOK &= self::copy_design_files($cacheRoot);
		
		ob_end_clean();

		$ini		= getCacheValue('ini');
		if (!$ini[':']['checkCompileFiles']){
			setCacheValue('siteFS', NULL);
		}
		
		return $bOK;
	}
	/******************************************/
	//	Exclude file from final file copy from siteFS to cache siteFiles
	static function addExcludeFile($vpath)
	{
		$files	= config::get(":siteFSexclude", array());
		$files[$vpath]	= $vpath;
		config::set(":siteFSexclude", $files);
	}
	static function addExcludeRegExp($filter)
	{
		$files	= config::get(":siteFSexcludeRegExp", array());
		$files[$filter]	= $filter;
		config::set(":siteFSexcludeRegExp", $files);
	}
	/******************************************/
	static function scanCotentForClass(&$classes, $content, $path)
	{
		if (!preg_match_all('#(class|interface)\s+([\w\d_]+)\s*(|(extends|implements)\s+[\w\d_]+)\s*{#', $content, $val)) return;
	
		foreach($val[2] as $m){
			$classes[$m]= $path;
		}
		return true;
	}
	/******************************************/
	static function copy_design_files($cacheRoot)
	{
		self::addExcludeFile('dwsync.xml');
		self::addExcludeFile('vssver2.scc');
		
		$siteFS				= getCacheValue('siteFS');
		$siteFSexcludeRegExp= config::get(":siteFSexcludeRegExp", array());
		$siteFSexclude		= config::get(":siteFSexclude", array());
		$siteCache			= $cacheRoot.'/'.localSiteFiles;
		
		foreach($siteFS as $vpath => $path)
		{
			if (isset($siteFSexclude[$vpath]))
				continue;
			
			$bIfnore = false;
			foreach($siteFSexcludeRegExp as $exp)
			{
				if (!preg_match($exp, $vpath)) continue;
				$bIfnore = true;
				break;
			}
			if ($bIfnore) continue;
			if (!is_file($path[0])) continue;

			$dest	= "$siteCache/$vpath";
			if (filemtime($dest) != $path[1])
			{
				//	First try copy
				if (!copy($path[0], $dest))
				{
					//	Make dir for second try copy
					makeDir(dirname($dest));
					if (!copy($path[0], $dest)){
						echo "Error copy design files $path[0] => $dest";
						return false;
					}
				};
				touch($dest, $path[1]);
			}
		}
		return true;
	}
	/******************************************/
	static function init_system_files($cacheRoot)
	{
		//	Execute all configs and system files in siteFS
		$siteFS	= getCacheValue('siteFS');
		$classes= getCacheValue(':classes');

		//	Вычислить время модификации самого свежего файла
		$maxModifyTime	= 0;
		$localModules	= array();

		$regExpConfig	= '#(^|/)config\.(.*)\.php$#';
		self::addExcludeRegExp($regExpConfig);
		$regExpModule	= '#^module_(.*)\.php$#';
		self::addExcludeRegExp($regExpModule);
		$regExpClass	= '#(^|/)class\.([a-zA-Z\d_-]+)\.php#';
		self::addExcludeRegExp($regExpClass);
		
		foreach($siteFS as $vpath => $path)
		{
			//	Search configs
			if (preg_match($regExpConfig, $vpath, $val)){
				include($path[0]);
			}else
			//	Search modules
			if (preg_match($regExpModule, $vpath, $val)){
				$name				= $val[1];
				$localModules[$name]= $path[0];
				$maxModifyTime 		= max($maxModifyTime, $path[1]);
			}else
			//	Collect all possibly classes inside class. files
			if (preg_match($regExpClass, $vpath, $val)){
				//	Remove class path form possibly check change files (classes not copy to cache)
				unset($siteFS[$vpath]);
				
				$class			= $val[2];
				$classes[$class]= $path[0];
				$content		= file_get_contents($path[0]);
				self::scanCotentForClass($classes, $content, $path[0]);
			}
		};
		setCacheValue('siteFS', $siteFS);

		//	Сохранить список классов
		setCacheValue(':classes', $classes);
		//	Сохранить список моулей
		setCacheValue('modules',$localModules);
		
		/***************************************************/
		//	Если файл модифицирован после создания общего файла, пересоздать общий файл
		$compiledPath	= $cacheRoot.'/'.localCompiledCode;
		if ($maxModifyTime > filemtime($compiledPath))
		{
			$modules	= self::collectModules($localModules);
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
	}

	//	Пройти по списку моулей и объеденить в один файл и оптимизировать для выполнения
	static function collectModules($localModules)
	{
		$modules	 = '';
		foreach($localModules as $name => $modulePath)
		{
			$modules .= file_get_contents($modulePath);
			$modules .= "\n";
		};
	
		$ini		= getIniValue(':');
		$bOptimize	= $ini['optimizePHP'];
		if ($bOptimize != 'yes') return $modules;
	
		//	Оптимизировать
		$modules= preg_replace('#(\?\>)\s*(\<\?)#',	'\\1\\2',	$modules);
		$modules= preg_replace('#([{}])\s+#',	'\\1',			$modules);
		$modules= preg_replace('#[ \t]+#',		' ',			$modules);
		$modules= preg_replace('#\r\n#',		"\n",			$modules);
		$modules= preg_replace('#\n+#',			"\n",			$modules);
		$modules= trim($modules);
		
		return $modules;
	}

}
?>