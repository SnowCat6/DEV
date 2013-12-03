<?
//	Компиляция шаблоново, загружаемых модулей
//	Копирование дизайнерских файлов
addEvent('config.prepare',	'config_prepare');
//	Компиляция програмного кода, сюда можно вставить компиляцию шаблонов
addEvent('config.end',		'config_end');
//	Компиляция програмного кода, сюда можно вставить компиляцию шаблонов
addEvent('page.compile',	'page_compile');

function module_config_prepare($val, $localCacheFolder)
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
	foreach($packages as $path)	pagesInitialize($path,		$localPages, $enable);
	//	sitepath/all files
	pagesInitialize(localHostPath,	$localPages, $enable);

	$bOK	= pageInitializeCopy($localCacheFolder.'/'.localSiteFiles, 		$localPages);
	$bOK	&=	pageInitializeCompile($localCacheFolder,	$localPages);
	if ($bOK)	setCacheValue('pages', $localPages);
	else echo 'Error copy design files';
}

function module_config_end($val, $data){
	m('htaccess');
}

function module_page_compile($val, &$thisPage)
{
	$GLOBALS['_CONFIG']['page']['compile']		= array();
	$GLOBALS['_CONFIG']['page']['compileLoaded']= array();

	//	<img src="" ... />
	//	Related path, like .href="../../_template/style.css"
	$thisPage	= preg_replace('#((href|src)\s*=\s*["\'])([^"\']+_[^\'"/]+/)#i',	'\\1', 	$thisPage);

	//	{{moduleName=values}}
	$thisPage	= preg_replace_callback('#{{([^}]+)}}#', 'parsePageFn', 	$thisPage);

	//	{$variable} htmlspecialchars out variable
	$thisPage	= preg_replace_callback('#{(\$[^}]+)}#', 'parsePageValFn', $thisPage);

	//	{!$variable} direct out variable
	$thisPage	= preg_replace_callback('#{!(\$[^}]+)}#','parsePageValDirectFn', $thisPage);

	//	{beginAdmin}  {endAdmin}
	$thisPage	= str_replace('{beginAdmin}',	'<? beginAdmin() ?>',		$thisPage);
	$thisPage	= str_replace('{endAdmin}',		'<? endAdmin($menu) ?>',	$thisPage);
	$thisPage	= str_replace('{endAdminTop}',	'<? endAdmin($menu, true) ?>',$thisPage);
	$thisPage	= str_replace('{endAdminBottom}','<? endAdmin($menu, false) ?>',$thisPage);

	//	{push} {pop:layout}
	$thisPage	= str_replace('{push}',				'<? ob_start() ?>',		$thisPage);
	$thisPage	= preg_replace('#{pop:([^}]+)}#',	'<? module("page:display:\\1", ob_get_clean()) ?>',$thisPage);

	//	<link rel="stylesheet" ... /> => use CSS module
	$thisPage	= preg_replace_callback('#<link[^>]+href\s*=\s*[\'"]([^>\'"]+)[\'"][^>]*>#i','parsePageCSS', $thisPage);

	//	{beginCompile:compileName}  {endCompile:compileName}
	$thisPage	= preg_replace('#{beginCompile:([^}]+)}#', '<?  if (beginCompile(\$data, "\\1")){ ?>', $thisPage);
	$thisPage	= preg_replace('#{endCompile:([^}]+)}#', '<?  endCompile(\$data, "\\1"); } ?>', $thisPage);
	$thisPage	= str_replace('{document}',	'<? document($data) ?>',$thisPage);

	//	Remove HTML comments
	$thisPage	= preg_replace('#<!--(.*?)-->#', 	'', 		$thisPage);
	$thisPage	= preg_replace('#(\?\>)\s*(\<\?)#', '\\1\\2',	$thisPage);

	//	Remove PHP white space
	$thisPage	= preg_replace('#^\s*(\<\?)#',	'\\1',		$thisPage);
	$thisPage	= preg_replace('#(\?\>)\s*$#',	'\\1',		$thisPage);
	
	$thisPage	= $thisPage.implode('', array_reverse($GLOBALS['_CONFIG']['page']['compileLoaded']));

	$root		=	globalRootURL;
	//	Ссылка не должна начинаться с этих символов
	$notAllow	= preg_quote('/#\'"<{', '#');
	$thisPage	= preg_replace("#((href|src)\s*=\s*[\"\'])(?!\w+://|//)([^$notAllow])#i", "\\1$root/\\3", 	$thisPage);
}
function quoteArgs($val){
	$val	= str_replace('"', '\\"', $val);
	$val	= str_replace('(', '\\(', $val);
	$val	= str_replace(')', '\\)', $val);
	return $val;
}
function parsePageFn(&$matches)
{	//	module						=> module("name")
	//	module=name:val;name2:val2	=> module("name", array($name=>$val));
	//	module=val;val2				=> module("name", array($val));
	$data		= array();
	$baseCode	= $matches[1];
	list($moduleName, $moduleData) = explode('=', $baseCode, 2);

	$bPriorityModule = $moduleName[0] == '!';
	if ($bPriorityModule) $moduleName = substr($moduleName, 1);
	
	//	name:val;nam2:val
	$d = explode(';', $moduleData);
	foreach($d as $row)
	{
		//	val					=> [] = val
		//	name:val			=> [name] = val
		//	name.name.name:val	=> [name][name][name] = val;
		$name = NULL; $val = NULL;
		list($name, $val) = explode(':', $row, 2);
		if (!$name) continue;
		
		if (isset($val)){
			$name	= str_replace('.', '"]["', $name);
			$data[] = "\$module_data[\"$name\"] = \"$val\"; ";
		}else{
			$data[] = "\$module_data[] = \"$name\"; ";
		}
	}
	
	if ($data){
		//	new code
		$code = "\$module_data = array(); ";
		$code.= implode('', $data);
		$code.= "moduleEx(\"$moduleName\", \$module_data);";
	}else{
		$code = "module(\"$moduleName\");";
	}

	if (!$bPriorityModule) return "<? $code ?>";

	$GLOBALS['_CONFIG']['page']['compileLoaded'][] = "<? \$p = ob_get_clean(); $code echo \$p; ?>";
	return "<? ob_start(); ?>";
}
function parsePageValFn(&$matches)
{
	$val = $matches[1];
	//	[value:charLimit OR in future function]
	$val= explode('=', $val, 2);
	//	[value] => ['value']
	$v = preg_replace('#\[([^\]]*)\]#', "[\"\\1\"]", $val[0]);
	//	$valName //	$valName[xx][xx]
	//	isset($valName[xx][xx])?$valName[xx][xx]:''
	if (count($val) == 1)
		return "<? if(isset($v)) echo htmlspecialchars($v) ?>";

	$v1	= $val[1];
	if (!$v1) $v1 = 50;
	return "<? if(isset($v)) echo htmlspecialchars(makeNote($v, \"$v1\")) ?>";
}

function parsePageValDirectFn(&$matches)
{
	$val = $matches[1];
	//	[value:charLimit OR in future function]
	$val= explode('=', $val, 2);
	//	[value] => ['value']
	$v	= preg_replace('#\[([^\]]*)\]#', "[\"\\1\"]", $val[0]);
	if (count($val) == 1)
		return "<? if(isset($v)) echo $v ?>";

	$v1	= $val[1];
	if (!$v1) $v1 = 100;
	return "<? if(isset($v)) echo makeNote($v, \"$v1\") ?>";
}
function parsePageCSS(&$matches)
{
	$val = $matches[1];
	return "<? module(\"page:style\", '$val') ?>";
}

//	Поиск всех страниц и шаблонов
function pagesInitialize($pagesPath, &$pages, &$enable)
{
	$module = basename($pagesPath);
	if (isset($enable[$module])) return;

	//	Поиск страниц сайта и шаблонов, запомниить пути для возможного копирования локальных файлов
	$files	= getFiles($pagesPath, '^(page\.|phone\.page\.|tablet\.page\.|template\.).*\.(php|php3)$');
	foreach($files as $name => $path){
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
function pageInitializeCompile($localCacheFolder, &$pages)
{
	$templates			= array();
	$comiledTemplates	= array();
	$compiledTmpName	= "$localCacheFolder/".localCompilePath."/compiled.php3";
	$compiledFileName	= localCacheFolder."/".localCompilePath."/compiled.php3";
	$comiledFileTime	= NULL;

	foreach($pages as $name => &$pagePath)
	{
		$fileName	= basename($pagePath);
		if (strpos($fileName, ".php3") && preg_match('#^template\.#', $name))
		{
			$name					= preg_replace('#^template\.#', '', $name);
			$templates[$name]		= $compiledFileName;
			$comiledTemplates[$name]= $pagePath;
			$comiledFileTime		= max($comiledFileTime, filemtime($pagePath));
			$pagePath 				= $compiledFileName;
			continue;
		}

		$compiledPagePath	= "$localCacheFolder/".localCompilePath."/$fileName";
		if (filemtime($pagePath) != filemtime($compiledPagePath))
		{
			$compiledPage		= file_get_contents($pagePath);
			event('page.compile', $compiledPage);
			
			if (!$compiledPage) continue;
			if (!file_put_contents_safe($compiledPagePath, $compiledPage)) return false;
			touch($compiledPagePath, filemtime($pagePath));
		}
		
		$pagePath = localCacheFolder."/".localCompilePath."/$fileName";
		if (preg_match('#^template\.#', $name)){
			$name				= preg_replace('#^template\.#', '', $name);
			$templates[$name]	= $pagePath;
		}
	}
	
	if ($comiledFileTime > filemtime($compiledTmpName))
	{
		$compiledTemplate	= '';
		foreach($comiledTemplates as $name => &$pagePath)
		{
			$compiledPage		= file_get_contents($pagePath);
/*			$compiledTemplate	.= "<? //	Template $name loaded from  $pagePath ?>\r\n";*/
			$compiledTemplate	.=$compiledPage;
		}
		event('page.compile', $compiledTemplate);
		file_put_contents_safe($compiledTmpName, $compiledTemplate);
	}

	setCacheValue('templates', $templates);
	return true;
}
?>