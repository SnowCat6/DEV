<?
function module_page($fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("page_$fn");
	return $fn?$fn($val, $data):NULL;
}
function module_display($val, $data){
	return page_display($val, $data);
}
//	Load any type file to page
function module_fileLoad($val, $data)
{
	$ext	= explode('.', $data);
	$ext	= end($ext);
	$ext	= strtolower($ext);
	switch($ext){
	case 'js':	return module_scriptLoad($val, $data);
	case 'css':	return module_styleLoad($val, $data);
	}
}
//	Attach style file ti page
function module_styleLoad($val, $data)
{
	if (!$data) return;
	setCacheData("styleLoad", $data);

	$store	= config::get(':styles', array());
	if ($store[$data]) return;
	
	$store[$data] = $data;
	config::set(':styles', $store);
}

function page_style($val, $data)
{
	return module_styleLoad('', $data);
}
//	Attach script file ti page
function module_scriptLoad($val, $data)
{
	if (!$data) return;
	setCacheData("scriptLoad", $data);
	
	$store	= config::get(':scripts', array());
	if ($store[$data]) return;

	$store[$data] = $data;
	config::set(':scripts', $store);
}

function isloadScriptAtEnd()
{
	if (access('use', 'adminPanel')) return false;
	$ini	= getIniValue(':');
	return $ini['scriptLoad'] == 'end';
}

function page_header($val)
{
	event('site.header', $val);
	//	Вывести заголовок
	$title	= m("page:title:siteTitle");
	echo "<title>$title</title>\r\n";
	if (!testValue('ajax'))
	{
		//	Вывести метатеги
		module("page:meta");
		module('page:display:head');
	}
	
	//	Вывести стили и скрипты в зависимости от настроек
	define('headerLoaded', true);
	if (isloadScriptAtEnd())
	{
		pageStyleLoad();
		pageStyle();
	}else{
		pageStyleLoad();
		pageScriptLoad();
		pageStyle();
		pageScript();
	}
}
function page_script($val, &$renderedPage)
{
	if (!isloadScriptAtEnd()) return;
	if (!defined('headerLoaded')) return;

	ob_start();
	pageScriptLoad();
	pageScript();
	insertContent($renderedPage, ob_get_clean());
}
function page_get($store, $name){
	if (!$store) $store = 'layout';
	$page	= config::get("page_$store");
	return $page[$name];
}
function page_title($val, &$data)
{
	if (!$val) $val = 'title';

	$store	= config::get("page_title", array());

	if (is_string($data)){
		$store[$val] = is_array($data)?implode(', ', $data):$data;
		return config::set("page_title", $store);
	}

	$title = $store[$val];
	if ($val == 'siteTitle' && !$title)
	{
		$title	= $store['title'];
		$ini	= getCacheValue('ini');
		$seo	= $ini[':SEO'];
		$seoTitle	= $seo['title'];
		if ($title){
			$title	= $seoTitle?str_replace('%', $title, snippets::compile($seoTitle)):$title;
		}else{
			$title = snippets::compile($seo['titleEmpty']);
		}
		echo htmlspecialchars(strip_tags($title));
	}else{
		echo htmlspecialchars($title);
	}
	return $title;
}

function page_meta($val, $data)
{
	$store	= config::get("page_meta", array());

	if (!$val){
		$ini	= getCacheValue('ini');
		$seo	= $ini[':SEO'];
		if (is_array($seo))
		{
			foreach($seo as $name => $val){
				if ($name == 'title' || $name == 'titleEmpty') continue;
				if (isset($store[$name])) continue;
				$store[$name] = $val;
			}
			config::set("page_meta", $store);
		}

		foreach($store as $name => $val){
			if ($name == ':HEAD'){
				echo snippets::compile($val);
			}else page_meta($name, NULL);
		}

		$metaRaw	= getIniValue(':SEO-raw');
		$headRaw	= base64_decode($metaRaw['head']);
		echo snippets::compile($headRaw);
		return;
	}
	
	if ($data){
		$store[$val] = is_array($data)?implode(', ', $data):$data;
		return config::set("page_meta", $store);
	}

	$title = snippets::compile($store[$val]);
	if (!$title) return;
	echo '<meta name="', $val, '" content="', htmlspecialchars($title), '" />', "\r\n";
	return $title;
}

function page_display($val, &$data)
{
	if (!$val) $val = 'body';
	if ($bClear = ($val[0] == '!')) $val = substr($val, 1);

	$store	= config::get("page_layout", array());

	if (is_string($data)){
		if ($bClear) $store[$val] = $data;
		else $store[$val] .= $data;
		return config::set("page_layout", $store);
	}

	echo "<!-- begin $val -->\r\n";
	echo $store[$val];
	echo "<!-- end $val -->\r\n";
	if (!$bClear) return;
	
	$store[$val] = '';
		return config::set("page_layout", $store);
}

function module_page_access($val, &$url)
{
	$ini	= getCacheValue('ini');
	$access	= $ini[':siteAccess'];
	if (!$access) return;
	
	$access		= array_keys($access);
	$access[]	= 'admin';
	$access[]	= 'developer';
	if (hasAccessRole($access)) return;
	
	config::set("page_layout", array());
	setTemplate('login');

	switch($url)
	{
	case '/user_lost.htm':
	case '/user_login.htm':
	case '/user_register.htm':
		break;
	default:
		$url	= '/user_login.htm';
	}
}
/*********************************/
function pageStyleLoad()
{
	$r		= config::get(':styles', array());
	//	External styles
	$root	= globalRootURL;

	$ini	= getCacheValue('ini');
	//	Объеденить файлы в один
	if ($ini[':']['unionCSS'] == 'yes' && localCacheExists())
	{
		//	Разобрать стили по каталогам
		$styles	= array();
		foreach($r as &$style)
		{
			$folder	= dirname($style);
			$file	= getSiteFile($style);
			if ($file){
				$css	= file_get_contents($file);
				if (!preg_match('#url\s*\(\s*([\'"](?!data:)|[^\'"])#i', $css)){
					$folder	= 'css';
				}
				$style = "$root/$style";
			}
			$styles[$folder][]	= $style;
		}
		//	Сформировать список стилей по группам
		$r	= array();
		foreach($styles as $folder => &$style){
			makeStyleFile($folder, $style);
			$r	= array_merge($r, $style);
		}
	}

	foreach($r as $style){
		if (!isUnionPath($style)){
			$style = "$root/$style";
		}
		$s = htmlspecialchars($style);
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$s\"/>\r\n";
	}
}
function pageStyle(){
		//	Inline styles
		$style	= config::get(':style', array());
		foreach($style as $val) echo $val, "\r\n";
}
/*********************************/
function pageScriptLoad()
{
	if (defined('scriptLoaded')) return;
	define('scriptLoaded', true);
	
	$root	= globalRootURL;
	$scripts= config::get(':scripts', array());
	$ini	= getCacheValue('ini');
	
	//	Объеденить файлы в один
	if ($ini[':']['unionJScript'] == 'yes' && localCacheExists())
	{
		$union	= array();
		foreach($scripts as $ix => $val)
		{
			$bNotUnion	= $val[0] == '/';
			if ($bNotUnion) continue;
			
			$scriptPath	= getSiteFile($val);
			if (!is_file($scriptPath)) continue;
			
			unset($scripts[$ix]);
			$union[]	= $val;
		}
		makeScriptFile($union);
		$scripts	= array_merge($scripts, $union);
	}
	
	foreach($scripts as $val)
	{
		if (isUnionPath($val)){
			echo "<script type=\"text/javascript\" src=\"$val\"></script>\r\n";
		}else{
			echo "<script type=\"text/javascript\" src=\"$root/$val\"></script>\r\n";
		}
	}
}
function isUnionPath($val){
	return $val[0] == '/' || strpos($val, "://") > 0;
}
function pageScript()
{
	echo preg_replace(
		'#</script>\s*<script>#i', '',
		implode("\r\n", config::get(':script', array()))
		);
}
function makeScriptFile(&$scripts)
{
	if (count($scripts) < 2) return;

	$md5	= hashData($scripts);
	$cache	= getCacheValue('cacheScript');
	$name	= $cache[$md5];
	if (!$name)
	{
		$script	= '';
		foreach($scripts as $val){
			$scriptPath	= getSiteFile($val);
			$script .= file_get_contents($scriptPath) . "\r\n";
		}
		
		$name	= hashData($script);
		$name	= "script/script_$name.js";
		$file	= cacheRootPath."/$name";
		mkDir(dirname($file));
		file_put_contents($file, $script);
		$cache[$md5]	= $name;
		setCacheValue('cacheScript', $cache);
	}
	$scripts	= array($name);
}
/*********************************/
function makeStyleFile($folder, &$styles)
{
	if (count($styles) < 2) return;

	$md5	= hashData($styles);
	$cache	= getCacheValue('cacheStyle');
	$name	= $cache[$md5];
	if (!$name)
	{
		if ($folder == '.') $folder = '';
		else $folder = "$folder/";
		
		foreach($styles as &$style){
			$stylePath	 = getSiteFile($style);
			if (!$stylePath) continue;
			$css .= file_get_contents($stylePath) . "\r\n";
		}
		$name	= hashData($css);
		$name	= $folder."style_$name.css";
		$root	= globalRootURL;
		$file	= cacheRootPath."/$name";
		mkDir(dirname($file));
		file_put_contents($file, $css);
		$cache[$md5]	= $name;
		setCacheValue('cacheStyle', $cache);
	}
	$styles	= array($name);
}

function script_holder($val)
{
	if (!isloadScriptAtEnd()) return;

	ob_get_clean();
	pageScriptLoad();
	ob_start();
}
?>