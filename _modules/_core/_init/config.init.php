<?
//	Компиляция модулей
addEvent('config.modules',	'config_modules');
//	Копирование дизайнерских файлов
addEvent('config.prepare',	'config_prepare');
//	Компиляция програмного кода, сюда можно вставить компиляцию шаблонов
addEvent('config.end',		'config_end');
//	Переместить шаблоны на постоянное место в случае пересборки кода
addEvent('config.rebase',	'config_rebase');

//	Просканировать все модули
function module_config_prepare($val, $cacheRoot)
{
	$localPages	= array();
	$siteFS		= getCacheValue('siteFS');
	
	$regExpTemplates	= '#((page|phone\.page|tablet\.page|template|.*\.template)\.(.*))\.(php|php3)$#';
	system_init::addExcludeRegExp($regExpTemplates);

	foreach($siteFS as $vpath => $path)
	{
		//	Получить просто имя модуля, без префиксов
		if (preg_match($regExpTemplates, $vpath, $val))
		{
			$name				= $val[1];
			$localPages[$name]	= $path[0];
		}
	}

	$siteCache	= $cacheRoot.'/'.localSiteFiles;
	$bOK	= pageInitializeCompile($cacheRoot, $localPages); 
	
	if (!$bOK)	echo 'Error copy design files';
}
//	Завершить конфигурирование для запуска
function module_config_end($val, $data)
{
	systemHtaccess::htaccessMake();
}

//	Compile pages
//	Обработать список файлов, скомилировать и скопировать в кеш
function pageInitializeCompile($cacheRoot, &$localPages)
{
	$pages				= array();	//	Страницы
	$pagesSource		= array();	//	Страницы с оригинальным путем

	$templates			= array();	//	Шаблоны
	$templatesSource	= array();	//	Пути к оригинальным файлам шаблонов для динамическом компиляции при отладке
	
	$comiledFileTime	= NULL;
	$comiledTemplates	= array();	//	Шаблоны для компиляции в один файл
	$compiledTmpName	= "$cacheRoot/".localCompilePath."/compiled.php3";

	//	Пройти по всему списку файлов
	foreach($localPages as $name => &$pagePath)
	{
		$fileName	= basename($pagePath);
		//	Файлы с расширением php3 объеденяются в один файл
		if (preg_match('#^(template)\.(.*)\.php3$#', $name, $v))
		{
			$name					= $v[2];
			$comiledFileTime		= max($comiledFileTime, filemtime($pagePath));
			
			$templates[$name]		= $compiledTmpName;
			$comiledTemplates[$name]= $pagePath;
			$templatesSource[$name]	= $pagePath;
			//	Заменить путь в массиве на общий для всех файлов
			$pagePath 				= $compiledTmpName;
			continue;
		}
		
		$compiledPagePath	= "$cacheRoot/".localCompilePath."/$fileName";
		//	Сравнить файл и файл в кеше, если даты модификации различаются, перекомпилировать
		if (filemtime($pagePath) != filemtime($compiledPagePath))
		{
			//	Прочитать содержимое
			$compiledPage	= file_get_contents($pagePath);
			//	Компиляция
			$ev	= array('source' => $pagePath, 'content' => &$compiledPage);
			event('page.compile', $ev);
			//	Найти функции с названием модулей
			findAndAddModules($templates, $compiledPage, $compiledPagePath);
			//	Сохранить в кеше
			if (!file_put_contents_safe($compiledPagePath, $compiledPage))
				return false;
			//	Присвоить время изменения аналогичное исходному файлу
			touch($compiledPagePath, filemtime($pagePath));
		}else{
			//	Прочитать содержимое
			$compiledPage	= file_get_contents($compiledPagePath);
			//	Найти функции с названием модулей
			findAndAddModules($templates, $compiledPage, $compiledPagePath);
		}
		//	Сохранить название модуля
		if (preg_match('#^(template|.*\.template)\.#', $name))
		{
			$name	= preg_replace('#^template\.#', '', 			$name);
			$name	= preg_replace('#^(.*)\.template\.#', '\\1_',	$name);
			
			$templates[$name]		= $compiledPagePath;
			$templatesSource[$name]	= $pagePath;
		}else
		//	Сохранить название страницы
		if (preg_match('#^(page|.*\.page)\.#', $name)){
			$pages[$name]		= $compiledPagePath;
			$pagesSource[$name]	= $pagePath;
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
			//	Компилировать файл
			$ev	= array('source' => $pagePath, 'content' => &$compiledPage);
			event('page.compile', $ev);
			
			$compiledTemplate	.=$compiledPage;
		}
		//	Найти функции с названием модулей
		findAndAddModules($templates, $compiledPage, $compiledTmpName);
		//	Сохранить файл
		file_put_contents_safe($compiledTmpName, $compiledTemplate);
	}else{
		$compiledTemplate	= file_get_contents($compiledTmpName);
		//	Найти функции с названием модулей
		findAndAddModules($templates, $compiledTemplate, $compiledTmpName);
	}
	
	//	Сохранить названия модулей
	setCacheValue('templates',			$templates);
	setCacheValue('templates_source',	$templatesSource);
	//	Сохранить названия страниц
	setCacheValue('pages', 			$pages);
	setCacheValue('pages_source', 	$pagesSource);
	
	return true;
}
//	Найти функции с названием модулей и добавть в список
function findAndAddModules(&$templates, $src, $filePath)
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
	if (system_init::scanCotentForClass($classes, $src, $filePath)){
		setCacheValue(":classes", $classes);
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