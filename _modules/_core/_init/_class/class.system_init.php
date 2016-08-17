<?
class system_init
{
	static function init($cacheRoot)
	{
		set_time_limit(60*2);

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

		//	Переместить шаблоны на постоянное место в случае пересборки кода
		addEvent('config.rebase',	'config_rebase');

		//	Initialize classes and common modules by siteFS
		self::init_system_files($cacheRoot);

		//	Обработать модули
		event('config.start',	$cacheRoot);
		//	Скомпилировать шаблоны, скопировать измененные файлы
		event('config.prepare',	$cacheRoot);
		//	Инициализировать с загруженными модулями
		event('config.end',		$cacheRoot);

		$bOK &= self::copy_design_files($cacheRoot);
		
		//	 Создать файл .htaccess
		systemHtaccess::htaccessMake();
		
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
			if (filemtime($dest) == $path[1]) continue;

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
		return true;
	}
	/******************************************/
	static function init_system_files($cacheRoot)
	{
		//	Execute all configs and system files in siteFS
		$siteFS	= getCacheValue('siteFS');
		$classes= getCacheValue(':classes');

		//	Вычислить время модификации самого свежего файла
		$localModules	= array();
		$localPages		= array();
		$cacheFolder	= "$cacheRoot/" . localCompilePath;
		makeDir($cacheFolder);

		$regExpConfig	= '#(^|/)config\.(.*)\.php$#';
		self::addExcludeRegExp($regExpConfig);
		$regExpModule	= '#^module_(.*)\.php$#';
		self::addExcludeRegExp($regExpModule);
		$regExpClass	= '#(^|/)class\.([a-zA-Z\d_-]+)\.php#';
		self::addExcludeRegExp($regExpClass);
		$regExpTemplates	= '#((page|phone\.page|tablet\.page|template|.*\.template)\.(.*))\.(php|php3)$#';
		self::addExcludeRegExp($regExpTemplates);
		
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
			}else
			//	Collect all possibly classes inside class. files
			if (preg_match($regExpClass, $vpath, $val)){
				$classPath		= "$cacheFolder/" . basename($path[0]);
				$class			= $val[2];
				$classes[$class]= $classPath;
				$content		= file_get_contents($path[0]);
				self::scanCotentForClass($classes, $content, $classPath);
				//	Copy class to cache
				copy($path[0], $classPath);
			}else
			//	Collect pages and templates
			if (preg_match($regExpTemplates, $vpath, $val)){
				$name				= $val[1];
				$localPages[$name]	= $path;
			}
		}
		setCacheValue('siteFS', $siteFS);
		//	Сохранить список классов
		setCacheValue(':classes', $classes);
		//	Сохранить список моулей
		setCacheValue('modules',$localModules);
		
		/***************************************************/
		//	Если файл модифицирован после создания общего файла, пересоздать общий файл
		$compiledPath	= $cacheRoot.'/'.localCompiledCode;
		if (!file_put_contents_safe($compiledPath, self::collectModules($localModules))){
			echo "Error write compiled modules to: $compiledPath";
			die;
		};

		$timeStart	= getmicrotime();
		ob_start();
		include_once($compiledPath);
		module('message:trace:modules', trim(ob_get_clean()));
		$time 		= round(getmicrotime() - $timeStart, 4);
		m("message:trace", "$time Included $compiledPath file");
		
		self::pageInitializeCompile($cacheRoot, $localPages); 
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
	//	Найти функции с названием модулей и добавть в список
	static function findAndAddModules(&$templates, $src, $filePath)
	{
		//	Modules and functions
		if (preg_match_all('#//\s+\+\s*function\s+([\w\d_]+)#', $src, $val))
		{
			foreach($val[1] as $m){
				$templates[$m]	= $filePath;
			}
		}
	
		//	classes
		$classes	= getCacheValue(":classes");
		if (self::scanCotentForClass($classes, $src, $filePath)){
			setCacheValue(":classes", $classes);
		}
	}
	//	Compile pages
	//	Обработать список файлов, скомилировать и скопировать в кеш
	static function pageInitializeCompile($cacheRoot, $localPages)
	{
		$pages				= array();	//	Страницы
		$templates			= array();	//	Шаблоны
		
		//	Собрать файлы в единый компилированный файл
		$compiledTemplate	= '';
		$compiledTmpName	= "$cacheRoot/".localCompilePath."/compiled.php3";
	
		//	Пройти по всему списку файлов
		foreach($localPages as $name => &$pagePath)
		{
			//	Файлы с расширением php3 объеденяются в один файл
			if (preg_match('#^(template)\.(.*)\.php3$#', $name, $v))
			{
				$name				= $v[2];
				$templates[$name]	= $compiledTmpName;
				$compiledPage		= file_get_contents($pagePath[0]);
				//	Компилировать файл
				$ev	= array('source' => $pagePath[0], 'content' => &$compiledPage);
				event('page.compile', $ev);

				$compiledTemplate	.=$compiledPage;
				continue;
			}
			
			$compiledPagePath	= "$cacheRoot/".localCompilePath."/$name";
			//	Прочитать содержимое
			$compiledPage	= file_get_contents($pagePath[0]);
			//	Компиляция
			$ev	= array('source' => $pagePath[0], 'content' => &$compiledPage);
			event('page.compile', $ev);
			//	Сохранить в кеше
			if (!file_put_contents_safe($compiledPagePath, $compiledPage))
				return false;

			//	Найти функции с названием модулей
			self::findAndAddModules($templates, $compiledPage, $compiledPagePath);
			
			//	Сохранить название модуля
			if (preg_match('#^(template|.*\.template)\.#', $name))
			{
				$name	= preg_replace('#^template\.#', '', 			$name);
				$name	= preg_replace('#^(.*)\.template\.#', '\\1_',	$name);
				$templates[$name]		= $compiledPagePath;
			}else
			//	Сохранить название страницы
			if (preg_match('#^(page|.*\.page)\.#', $name)){
				$pages[$name]		= $compiledPagePath;
			}
		}
		
		//	Сохранить файл
		file_put_contents_safe($compiledTmpName, $compiledTemplate);
		//	Найти функции с названием модулей
		self::findAndAddModules($templates, $compiledPage, $compiledTmpName);
		
		//	Сохранить названия модулей
		setCacheValue('templates',		$templates);
		//	Сохранить названия страниц
		setCacheValue('pages', 			$pages);
		
		return true;
	}

}

//	Переместить все ссылки на исполняемые функции на новое место
//	Вызывается после пересобрания всей системы во временном месте
function module_config_rebase($val, $thisPath)
{
	fnMoveSysFiles('templates', $thisPath);
	fnMoveSysFiles('pages', 	$thisPath);
	fnMoveSysFiles(':classes', 	$thisPath);
}
function fnMoveSysFiles($cacheName, $thisPath)
{
	$nLen	= strlen($thisPath);
	$files	= getCacheValue($cacheName);
	foreach($files as &$path){
		if (strncmp($path, $thisPath, $nLen)) continue;
		$path	= cacheRoot . substr($path, $nLen);
	}
	setCacheValue($cacheName, $files);
}
?>

<?
//	FIRST executed config
//	Define one time used functions
$ini	= getIniValue(':');
if (!is_array($ini)){
	$ini['useCache']			= 1;
	$ini['compress']			= 'gzip';
	$ini['checkCompileFiles']	= 1;
	setIniValue(':', $ini);
}

//	Добавть обработчик события
function addEvent($eventName, $eventModule)
{
	$event = getCacheValue('localEvent');

	//	Можно задавать место выполнения события
	//	addEvent('config.end:before', ...);
	list($eventName, $postfix)		= explode(':', $eventName, 2);
	if (!$postfix) $postfix = 'fire';
	//	Добавить событие
	$event[$eventName][$postfix][$eventModule]	= $eventModule;

	setCacheValue('localEvent', $event);
}

//	Добавить обработчки URL страницы
function addUrl($parseRule, $parseModule){
	addUrlEx("#^/$parseRule\.htm$#i", $parseModule);
}
//	Добавить обработчки URL страницы
function addUrlEx($parseRule, $parseModule)
{
	$localURLparse = getCacheValue('localURLparse');
	$localURLparse[$parseRule]	= $parseModule;
	setCacheValue('localURLparse', $localURLparse);
}

//	Добавить обработку правила доступа к объектам
function addAccess($parseRule, $parseModule){
	$localAccessParse = getCacheValue('localAccessParse');
	$localAccessParse[$parseRule][$parseModule]	= $parseModule;
	setCacheValue('localAccessParse', $localAccessParse);
}

//	Добавить групповую роль для администрирования
function addRole($roleName, $roleAccess){
	$localUserRoles = getCacheValue('localUserRoles');
	$localUserRoles[$roleAccess]	= $roleName;
	setCacheValue('localUserRoles', $localUserRoles);
}

//	Добавить фиксированный сниппет
function addSnippet($snippetName, $value){
	$localSnippets = getCacheValue('localSnippets');
	$localSnippets[$snippetName]	= $value;
	if (!$value) unset($localSnippets[$snippetName]);
	setCacheValue('localSnippets', $localSnippets);
}
?>