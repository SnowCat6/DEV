<?
//	Копирование модулей
addEvent('config.start',	'config_start');
//	Компиляция модулей
addEvent('config.modules',	'config_modules');
//	Копирование дизайнерских файлов
addEvent('config.prepare',	'config_prepare');
//	Компиляция програмного кода, сюда можно вставить компиляцию шаблонов
addEvent('config.end',		'config_end');

//	Проверить изменились ли модули, скопировать и скомпилировать
function module_config_start(&$val, &$localCacheFolder)
{
	//	Вычислить время модификации самого свежего файла
	$maxModifyTime	= 0;
	$compiledPath	= $localCacheFolder.'/'.localCompiledCode;
	
	$localModules	= getCacheValue('modules');
	foreach($localModules as $modulePath){
		$maxModifyTime = max($maxModifyTime, filemtime($modulePath));
	}
	//	Если файл модифицировано после создания общего файла, пересоздать общий файл
	if ($maxModifyTime > filemtime($compiledPath))
	{
		$modules	= '';
		event('config.modules',	$modules);
		if (!file_put_contents_safe($compiledPath, $modules)){
			echo "Error write compiled modules to: $compiledPath";
			die;
		};
	}

	//	При необходимости вывести сообщения от модулей в лог
	$timeStart	= getmicrotime();
	ob_start();
	include_once($compiledPath);
	module('message:trace:modules', trim(ob_get_clean()));
	$time 		= round(getmicrotime() - $timeStart, 4);
	m("message:trace", "$time Included $compiledPath file");
}
//	Пройти по списку моулей и объеденить в один файл и оптимизировать для выполнения
function module_config_modules(&$val, &$modules)
{
	$localModules	= getCacheValue('modules');
	foreach($localModules as $name => &$modulePath){
		$modules .= file_get_contents($modulePath);
		$modules .= "\n";
	};
	//	Оптимизировать
	$modules= preg_replace('#(\?\>)\s*(\<\?)#',	'\\1\\2',	$modules);
	$modules= preg_replace('#([{}])\s+#',	'\\1',			$modules);
	$modules= preg_replace('#[ \t]+#',		' ',			$modules);
	$modules= preg_replace('#\r\n#',		"\n",			$modules);
	$modules= preg_replace('#\n+#',			"\n",			$modules);
	$modules= trim($modules);
}

function module_config_prepare(&$val, $localCacheFolder)
{
	$localModules	= getCacheValue('modules');
	$modulesPath	= $localCacheFolder.'/'.localSiteFiles;
	$bOK			&= pageInitializeCopy($modulesPath, $localModules);

	$ini		= getCacheValue('ini');

	$enable		= $ini[":enable"];
	if (!is_array($enable))	$enable = array();

	$packages	= $ini[":packages"];
	if (!is_array($packages))$packages = array();

	//	Initialize pages and copy desing files
	$localPages = array();
	//	_modules
	pagesInitialize(modulesBase,	$localPages, $enable);
	//	_templates
	pagesInitialize(templatesBase,	$localPages, $enable);
	//	_packages checked for compile
	foreach($packages as $path)	pagesInitialize($path, $localPages, $enable);
	//	sitepath/all files
	pagesInitialize(localHostPath,	$localPages, $enable);
	//	По списку файлов скопировать дизайнерские файлв и собрать модули и шаблоны
	$bOK	= pageInitializeCopy($localCacheFolder.'/'.localSiteFiles, $localPages);
	$bOK	&=pageInitializeCompile($localCacheFolder,	$localPages); 

	if (!$bOK)	echo 'Error copy design files';
}

function module_config_end($val, $data){
	m('htaccess');
}

//	Поиск всех страниц и шаблонов
function pagesInitialize($pagesPath, &$pages, &$enable)
{
	$module = basename($pagesPath);
	if (isset($enable[$module])) return;

	//	Поиск страниц сайта и шаблонов, запомниить пути для возможного копирования локальных файлов
	$files	= getFiles($pagesPath, '^(page\.|phone\.page\.|tablet\.page\.|template\.).*\.(php|php3)$');
	foreach($files as $name => $path){
		//	Получить просто имя модуля, без префиксов
		$name = preg_replace('#\.[^.]*$#', '', $name);
		$pages[$name] = $path;
	}

	$dirs = getDirs($pagesPath, '^_');
	foreach($dirs as $pagePath){
		//	Сканировать поддиректории
		pagesInitialize($pagePath, $pages, $enable);
	};
}

//	Копирование всех дизайнерских файлов из модуля в основной каталог сайта, за исключением системных файлов
function pageInitializeCopy($rootFolder, $pages)
{
	$bOK = true;
	makeDir($rootFolder);
	foreach($pages as $pagePath)
	{
		$baseFolder	= dirname($pagePath);

		//	Копирование файлов
		$files 	= getFiles($baseFolder);
		foreach($files as $name => $sourcePath)
		{
			//	Не копировать шиблоны страниц
			if (preg_match('#^(page\.|.*\.page\.)#', $name)) continue;
			//	Не копировать модули, конфиги, шаблоны
			if (preg_match('#^(module_|config\.|template\.)#', $name)) continue;

			$destPath = "$rootFolder/$name";
			if ($sourcePath == $destPath) continue;
			if (filemtime($sourcePath) == filemtime($destPath)) continue;

			if (!copy($sourcePath, $destPath)){
				$bOK = false;
				continue;
			}
			touch($destPath, filemtime($sourcePath));
		};
		
		//	Копирование папок
		$dirs		= getDirs($baseFolder, '^[^_].+');
		foreach($dirs as $name => $sourcePath)
		{
			if (is_int(strpos($sourcePath, images))) continue;
			$bOK &= copyFolder($sourcePath, "$rootFolder/$name");
		}
	};
	return $bOK;
}

//	Compile pages
//	Обработать список файлов, скомилировать и скопировать в кеш
function pageInitializeCompile($localCacheFolder, &$localPages)
{
	$pages				= array();
	$templates			= array();
	$comiledTemplates	= array();
	$compiledTmpName	= "$localCacheFolder/".localCompilePath."/compiled.php3";
	$compiledFileName	= localCacheFolder."/".localCompilePath."/compiled.php3";
	$comiledFileTime	= NULL;

	//	Пройти по всему списку файлов
	foreach($localPages as $name => &$pagePath)
	{
		$fileName	= basename($pagePath);
		//	Файлы с расширением php3 объеденяются в один файл
		if (strpos($fileName, ".php3") && preg_match('#^template\.#', $name))
		{
			$name					= preg_replace('#^template\.#', '', $name);
			$templates[$name]		= $compiledFileName;
			$comiledTemplates[$name]= $pagePath;
			$comiledFileTime		= max($comiledFileTime, filemtime($pagePath));
			$pagePath 				= $compiledFileName;
			continue;
		}
		//	Сравнить файл и файл в кеше, если даты модификации различаются, перекомпилировать
		$cachePagePath 		= localCacheFolder."/".localCompilePath."/$fileName";
		$compiledPagePath	= "$localCacheFolder/".localCompilePath."/$fileName";
		if (filemtime($pagePath) != filemtime($compiledPagePath))
		{
			//	Прочитать содержимое
			$compiledPage		= file_get_contents($pagePath);
			//	Компиляция
			event('page.compile', $compiledPage);
			//	Сохранить в кеше
			if (!file_put_contents_safe($compiledPagePath, $compiledPage)) return false;
			//	Присвоить время изменения аналогичное исходному файлу
			touch($compiledPagePath, filemtime($pagePath));
			//	Найти функции с названием модулей
			findAndAddModules($templates, $compiledPagePath, $cachePagePath);
		}
		//	Сохранить название модуля
		if (preg_match('#^template\.#', $name)){
			$name				= preg_replace('#^template\.#', '', $name);
			$templates[$name]	= $cachePagePath;
		}
		//	Сохранить название страницы
		if (preg_match('#^page\.#', $name)){
			$pages[$name]		= $cachePagePath;
		}
	}
	//	Собрать файлы в единый компилированный файл
	if ($comiledFileTime > filemtime($compiledTmpName))
	{
		$compiledTemplate	= '';
		//	Прочитать все файлы и объеденить в один
		foreach($comiledTemplates as $name => &$pagePath)
		{
			$compiledPage		= file_get_contents($pagePath);
/*			$compiledTemplate	.= "<? //	Template $name loaded from  $pagePath ?>\r\n";*/
			$compiledTemplate	.=$compiledPage;
		}
		//	Компилировать файл
		event('page.compile', $compiledTemplate);
		//	Сохранить файл
		file_put_contents_safe($compiledTmpName, $compiledTemplate);
		//	Найти функции с названием модулей
		findAndAddModules($templates, $compiledTemplate, $compiledFileName);
	}
	//	Сохранить названия модулей
	setCacheValue('templates',	$templates);
	//	Сохранить названия страниц
	setCacheValue('pages', 		$pages);
	
	return true;
}
//	Найти функции с названием модулей и добавть в список
function findAndAddModules(&$templates, &$src, $filePath)
{
	if (!preg_match_all('#function\s+module_([a-zA-Z_0-9]+)#', $src, $val)) return;
	foreach($val[1] as $m){
		$templates[$m]	= $filePath;
	}
}
?>