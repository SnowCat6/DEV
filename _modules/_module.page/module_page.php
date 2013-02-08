<?
function module_page($fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("page_$fn");
	return $fn?$fn($val, &$data):NULL;
}
function module_display($val, &$data){
	return page_display($val, &$data);
}

function page_header(){
?><title><? module("page:title") ?></title>
<?
module("page:meta");
module("page:style");
module("page:script");
}

function page_title($val, &$data)
{
	if (!$val) $val = 'title';

	@$store = &$GLOBALS['_CONFIG']['page']['title'];
	if (!is_array($store)) $store = array();
	
	if ($data){
		$store[$val] = is_array($data)?implode(', ', $data):$data;
	}else{
		@$title = &$store[$val];
		echo htmlspecialchars($title);
		return $title;
	}
}

function page_meta($val, $data)
{
	@$store = &$GLOBALS['_CONFIG']['page']['meta'];
	if (!is_array($store)) $store = array();

	if (!$val){
		foreach($store as $name => $val) page_meta($name, NULL);
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
		foreach($store as $style){
			$style = htmlspecialchars($style);
			echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$style\"/>\r\n";
		}
	}
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
		foreach($store as $script){
			echo $script, "\r\n";
		}
	}
}

?>