<?
function module_page(&$fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("page_$fn");
	return $fn?$fn($val, $data):NULL;
}
function module_display(&$val, &$data){
	return page_display($val, $data);
}

function page_header()
{
	
	$title	= m("page:title:siteTitle");
	echo "<title>$title</title>";
	module("page:meta");

	pageStyleLoad();
	pageScriptLoad();
	pageStyle();
	pageScript();
}
function page_script(&$val, &$renderedPage)
{
	return;
	ob_start();
	pageScript();
	$script = ob_get_clean();
	
	$n	= stripos($renderedPage, '</body');
	if ($n){
		$renderedPage = substr($renderedPage, 0, $n) . $script . substr($renderedPage, $n);
	}else{
		$renderedPage .= $script;
	}
}
function page_get($store, $name){
	if (!$store) $store = 'layout';
	return $GLOBALS['_CONFIG']['page'][$store][$name];
}
function page_title($val, &$data)
{
	if (!$val) $val = 'title';

	@$store = &$GLOBALS['_CONFIG']['page']['title'];
	if (!is_array($store)) $store = array();

	if (!is_null($data)){
		$store[$val] = is_array($data)?implode(', ', $data):$data;
	}else{
		@$title = &$store[$val];
		if ($val == 'siteTitle' && !$title){
			$title	= @$store['title'];
			$ini	= getCacheValue('ini');
			@$seo	= $ini[':SEO'];
			@$seoTitle	= $seo['title'];
			if ($title){
				$title	= $seoTitle?str_replace('%', $title, $seoTitle):$title;
			}else{
				@$title = $seo['titleEmpty'];
			}
			echo htmlspecialchars(strip_tags($title));
		}else{
			echo htmlspecialchars($title);
		}
		return $title;
	}
}

function page_meta($val, $data)
{
	@$store = &$GLOBALS['_CONFIG']['page']['meta'];
	if (!is_array($store)) $store = array();

	if (!$val){
		$ini	= getCacheValue('ini');
		@$seo	= $ini[':SEO'];
		if (is_array($seo)){
			foreach($seo as $name => $val){
				if ($name == 'title' || $name == 'titleEmpty') continue;
				if (isset($store[$name])) continue;
				$store[$name] = $val;
			}
		}
		foreach($store as $name => &$val) page_meta($name, NULL);
		return;
	}
	
	if ($data){
		$store[$val] = is_array($data)?implode(', ', $data):$data;
	}else{
		@$title = &$store[$val];
		if (!$title) return;
		echo '<meta name="', $val, '" content="', htmlspecialchars($title), '" />', "\r\n";
		return $title;
	}
}

function page_display($val, &$data)
{
	if (!$val) $val = 'body';
	if ($bClear = ($val[0] == '!')) $val = substr($val, 1);

	@$store = &$GLOBALS['_CONFIG']['page']['layout'];
	if (!is_array($store)) $store = array();

	if (is_string($data)){
		if ($bClear) $store[$val] = $data;
		else @$store[$val] .= $data;
	}else{
		echo "<!-- begin $val -->\r\n";
		echo @$store[$val];
		if ($bClear) $store[$val] = '';
		echo "<!-- end $val -->\r\n";
	}
}

function page_style($val, $data)
{
	@$store = &$GLOBALS['_CONFIG']['page']['styles'];
	if (!is_array($store)) $store = array();

	if (!$data) return;

	if (is_array($data)){
		dataMerge($store, $data);
	}else{
		$store[$data] = $data;
	}
}

function module_page_access($val, &$content)
{
	$ini	= getCacheValue('ini');
	$access	= $ini[':siteAccess'];
	if (!$access) return;
	
	$access		= array_keys($access);
	$access[]	= 'admin';
	$access[]	= 'developer';
	if (hasAccessRole($access)) return;
	
	ob_start();
	$config = &$GLOBALS['_CONFIG'];
	$config['page']['layout'] = array();
	setTemplate('login');
	
	switch(getRequestURL())
	{
	case '/user_lost.htm':
	case '/user_login.htm':
	case '/user_register.htm':
		renderPage(getRequestURL());
		break;
	default:
		renderPage('/login.htm');
	}
	
	$content = ob_get_clean();
}
/*********************************/
function pageStyleLoad()
{
	$r		= $GLOBALS['_CONFIG']['page']['styles'];
	//	External styles
	$root	= globalRootURL;

	$ini	= getCacheValue('ini');
	//	Объеденить файлы в один
	if ($ini[':']['unionCSS'] == 'yes' && localCacheExists()){
		//	Разобрать стили по каталогам
		$styles	= array();
		foreach($r as &$style){
			$styles[dirname($style)][basename($style)]	= $style;
		}
		//	Сформировать список стилей по группам
		$r	= array();
		foreach($styles as &$style){
			makeStyleFile($style);
			$r	= array_merge($r, $style);
		}
	}

	foreach($r as &$style){
		$s = htmlspecialchars($style);
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$root/$s\"/>\r\n";
	}
}
function pageStyle(){
		//	Inline styles
		$style = &$GLOBALS['_SETTINGS']['style'];
		if (!$style) return;
		foreach($style as &$val) echo $val, "\r\n";
}
/*********************************/
function pageScriptLoad()
{
	$root	= globalRootURL;
	$scripts= &$GLOBALS['_SETTINGS']['scriptLoad'];
	if (!$scripts) $scripts = array();
	
	$ini	= getCacheValue('ini');
	
	//	Объеденить файлы в один
	if ($ini[':']['unionJScript'] == 'yes' && localCacheExists())
	{
		$union	= array();
		foreach($scripts as $ix => &$val)
		{
			$bNotUnion	= $val[0] == '/';
			if ($bNotUnion) continue;
			
			$scriptPath	= localCacheFolder.'/'.localSiteFiles."/$val";
			if (!is_file($scriptPath)) continue;
			
			unset($scripts[$ix]);
			$union[]	= $val;
		}
		makeScriptFile($union);
		$scripts	= array_merge($scripts, $union);
	}
	
	foreach($scripts as &$val)
	{
		$bNotUnion	= $val[0] == '/';
		if ($bNotUnion){
			echo "<script type=\"text/javascript\" src=\"$val\"></script>\r\n";
		}else{
			echo "<script type=\"text/javascript\" src=\"$root/$val\"></script>\r\n";
		}
	}
}
function pageScript(){
	$script = &$GLOBALS['_SETTINGS']['script'];
	if (!$script) $script = array();
	foreach($script as &$val) echo $val, "\r\n";
}
function makeScriptFile(&$scripts)
{
	if (count($scripts) < 3) return;
	$md5	= hashData($scripts);
	$cache	= getCache('cacheScript');
	$name	= $cache[$md5];
	if (!$name)
	{
		$script	= '';
		foreach($scripts as &$val){
			$scriptPath	= localCacheFolder.'/'.localSiteFiles."/$val";
			$script .= file_get_contents($scriptPath) . "\r\n";
		}
		
		$name	= time().$md5;
		$name	= hashData($name);
		$name	= "script_$name.js";
		$file	= localCacheFolder.'/'.localSiteFiles."/$name";
		file_put_contents($file, $script);
		$cache[$md5]	= $name;
		setCache('cacheScript', $cache);
	}
	$scripts	= array($name);
}
/*********************************/
function makeStyleFile(&$styles)
{
	if (count($styles) < 3) return;

	$md5	= hashData($styles);
	$cache	= getCache('cacheStyle');
	$name	= $cache[$md5];
	if (!$name)
	{
		foreach($styles as &$style){
			$stylePath	 = localCacheFolder.'/'.localSiteFiles."/$style";
			if (!is_file($stylePath)) continue;
			$css .= file_get_contents($stylePath) . "\r\n";
		}
		
		$name	= time().$md5;
		$name	= hashData($name);
		$name	= "style_$name.css";
		$root	= globalRootURL;
		$file	= localCacheFolder.'/'.localSiteFiles."/$name";
		file_put_contents($file, $css);
		$cache[$md5]	= $name;
		setCache('cacheStyle', $cache);
	}
	$styles	= array($name);
}

?>