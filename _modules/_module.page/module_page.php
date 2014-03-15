<?
function module_page($fn, &$data)
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
	?>
    <title><? module("page:title:siteTitle") ?></title>
	<?
	module("page:meta");
	module("page:style");
	module("page:script");
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
		}
		echo htmlspecialchars(strip_tags($title));
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

	if ($data){
		if (is_array($data)){
			dataMerge($store, $data);
		}else{
			$store[$data] = $data;
		}
	}else{
		$root	= globalRootURL;
		$r		= $store;	// array_reverse($store);
		makeStyleFile($r);
		foreach($r as &$style){
			$s = htmlspecialchars($style);
			echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$root/$s\"/>\r\n";
		}
	}
}
function makeStyleFile(&$styles)
{
	if (count($styles) < 3) return;
	if (!localCacheExists()) return;
	$ini	= getCacheValue('ini');
	if ($ini[':']['unionCSS'] != 'yes') return;

	$md5	= hashData($styles);
	$cache	= getCacheValue('cacheStyle');
	$name	= $cache[$md5];
	if (!$name)
	{
		foreach($styles as &$style){
			$stylePath	 = localCacheFolder.'/'.localSiteFiles."/$style";
			if (!is_file($stylePath)) continue;
//			$css .= "/* $style */\r\n";
			$css .= file_get_contents($stylePath);
		}
		
		$name	= time().$md5;
		$name	= hashData($name);
		$root	= globalRootURL;
		$name	= "$name.css";
		$file	= localCacheFolder.'/'.localSiteFiles."/$name";
		file_put_contents($file, $css);
		$cache[$md5]	= $name;
		setCacheValue('cacheStyle', $cache);
	}
	$styles	= array($name);
}

function page_script($val, $data)
{
	@$store = &$GLOBALS['_CONFIG']['page']['scripts'];
	if (!is_array($store)) $store = array();

	if ($val){
		if (is_array($data)){
			dataMerge($store, $data);
		}else{
			$store[$val] = $data;
		}
	}else{
		foreach($store as &$script){
			echo $script, "\r\n";
		}
	}
}
function module_page_access($val, &$content){
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
?>