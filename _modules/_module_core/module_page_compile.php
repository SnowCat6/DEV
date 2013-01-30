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
function parsePageFn($matches)
{
	$data = array();
	@list($moduleName, $d) = explode('=', $matches[1], 2);

	$bPriorityModule = $moduleName[0] == '!';
	if ($bPriorityModule) $moduleName = substr($moduleName, 1);
	
	$d = explode(';', $d);
	foreach($d as $row){
		$name = $v = '';
		list($name, $v) = explode(':', $row);
		$name	= trim($name);
		if (!$v){
			$v		= str_replace('"', '\\"', $name);
			if ($v) $data[] = "\"$v\"";
		}else{
			$v		= str_replace('"', '\\"', $v);
			$data[] = "\"$name\"=>\"$v\"";
		}
	}

	$data		= implode(',', $data);
	$moduleCode	= $data?"module(\"$moduleName\", array($data))":"module(\"$moduleName\")";
	
	if (!$bPriorityModule) return "<? $moduleCode ?>";
	if (isset($GLOBALS['_CONFIG']['page']['compileLoaded'][$moduleName])) return '';
	$GLOBALS['_CONFIG']['page']['compileLoaded'][$moduleName] = true;
	
	$GLOBALS['_CONFIG']['page']['compile'][] = "<? ob_start(); $moduleCode; module(\"page:display:!$moduleName\", ob_get_clean())?>\r\n";
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