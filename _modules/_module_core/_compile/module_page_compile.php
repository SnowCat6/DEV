<?
function module_page_compile($val, &$thisPage){
	$GLOBALS['_CONFIG']['page']['compile']		= array();
	$GLOBALS['_CONFIG']['page']['compileLoaded']= array();

	//	Related path, like .href="../../_template/style.css"
	$thisPage	= preg_replace('#(href\s*=\s*["\'])([^"\']+_[^\'"/]+/)#i', '\\1', 	$thisPage);
	//	{{moduleName=values}}
	$thisPage	= preg_replace_callback('#{{([^}]+)}}#', parsePageFn, 	$thisPage);
	//	{$variable} htmlspecialchars out variable
	$thisPage	= preg_replace_callback('#{(\$[^}]+)}#', parsePageValFn, $thisPage);
	//	{!$variable} direct out variable
	$thisPage	= preg_replace_callback('#{!(\$[^}]+)}#',parsePageValDirectFn, $thisPage);
	//	{beginAdmin}  {endAdmin}
	$thisPage	= str_replace('{beginAdmin}',	'<? beginAdmin() ?>',		$thisPage);
	$thisPage	= str_replace('{endAdmin}',		'<? endAdmin($menu) ?>',	$thisPage);
	$thisPage	= str_replace('{endAdminTop}',	'<? endAdmin($menu, true) ?>',$thisPage);
	//	<link rel="stylesheet" ... /> => use CSS module
	$thisPage	= preg_replace_callback('#<link\s+rel\s*=\s*[\'"]stylesheet[\'"][^>]*href\s*=\s*[\'"]([^>\'"]+)[\'"][^>]*/>#',parsePageCSS, $thisPage);

	$thisPage	= implode('', $GLOBALS['_CONFIG']['page']['compile']).	$thisPage;
}
function quoteArgs($val){
	$val	= str_replace('"', '\\"', $val);
	$val	= str_replace('(', '\\(', $val);
	$val	= str_replace(')', '\\)', $val);
	return $val;
}
function parsePageFn($matches)
{	//	module						=> module("name")
	//	module=name:val;name2:val2	=> module("name", array($name=>$val));
	//	module=val;val2				=> module("name", array($val));
	$data = array();
	@list($moduleName, $moduleData) = explode('=', $matches[1], 2);

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
		
		if ($val){
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
		$code.= "module(\"$moduleName\", \$module_data);";
	}else{
		$code = "module(\"$moduleName\");";
	}

	if (!$bPriorityModule) return "<? $code ?>";
	if (isset($GLOBALS['_CONFIG']['page']['compileLoaded'][$moduleName])) return '';
	$GLOBALS['_CONFIG']['page']['compileLoaded'][$moduleName] = true;
	
	$GLOBALS['_CONFIG']['page']['compile'][] = "<? ob_start(); $code; module(\"page:display:!$moduleName\", ob_get_clean())?>\r\n";
	return "<? module(\"page:display:$moduleName\")?>";
}
function parsePageValFn($matches)
{
	$val = $matches[1];
	//	[value] => ['value']
	$val = preg_replace('#\[([^\]]*)\]#', "['\\1']", $val);
	return "<?= @htmlspecialchars($val) ?>";
}

function parsePageValDirectFn($matches)
{
	$val = $matches[1];
	//	[value] => ['value']
	$val = preg_replace('#\[([^\]]*)\]#', "['\\1']", $val);
	return "<?= @$val ?>";
}
function parsePageCSS($matches)
{
	$val = $matches[1];
	return "<? module(\"page:style\", '$val') ?>";
}

?>