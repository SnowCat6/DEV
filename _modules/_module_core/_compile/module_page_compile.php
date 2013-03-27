<?
function module_page_compile($val, &$thisPage)
{
	$GLOBALS['_CONFIG']['page']['compile']		= array();
	$GLOBALS['_CONFIG']['page']['compileLoaded']= array();

	//	<img src="" ... />
	//	Related path, like .href="../../_template/style.css"
	$thisPage	= preg_replace('#((href|src)\s*=\s*["\'])([^"\']+_[^\'"/]+/)#i', '\\1', 	$thisPage);

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

	//	{push} {pop:layout}
	$thisPage	= str_replace('{push}',				'<? ob_start() ?>',		$thisPage);
	$thisPage	= preg_replace('#{pop:([^}]+)}#',	'<? module("page:display:\\1", ob_get_clean()) ?>',$thisPage);

	//	<link rel="stylesheet" ... /> => use CSS module
	$thisPage	= preg_replace_callback('#<link[^>]+href\s*=\s*[\'"]([^>\'"]+)[\'"][^>]*>#i',parsePageCSS, $thisPage);

	//	{beginCompile:compileName}  {endCompile:compileName}
	$thisPage	= preg_replace('#{beginCompile:([^}]+)}#', '<?  if (beginCompile(\$data, "\\1")){ ?>', $thisPage);
	$thisPage	= preg_replace('#{endCompile:([^}]+)}#', '<?  endCompile(\$data, "\\1"); } ?>', $thisPage);
	$thisPage	= str_replace('{document}',	'<? document($data) ?>',$thisPage);

	$thisPage	= $thisPage.implode('', array_reverse($GLOBALS['_CONFIG']['page']['compileLoaded']));
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
	$data		= array();
	$baseCode	= $matches[1];
	@list($moduleName, $moduleData) = explode('=', $baseCode, 2);

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

	$GLOBALS['_CONFIG']['page']['compileLoaded'][] = "<? \$p = ob_get_clean(); $code echo \$p; ?>";
	return "<? ob_start(); ?>";
}
function parsePageValFn($matches)
{
	$val = $matches[1];
	//	[value] => ['value']
	$val = preg_replace('#\[([^\]]*)\]#', "[\"\\1\"]", $val);
	//	$valName //	$valName[xx][xx]
	//	isset($valName[xx][xx])?$valName[xx][xx]:''
	return "<?= isset($val)?htmlspecialchars($val):'' ?>";
}

function parsePageValDirectFn($matches)
{
	$val = $matches[1];
	//	[value] => ['value']
	$val = preg_replace('#\[([^\]]*)\]#', "[\"\\1\"]", $val);
	return "<?= isset($val)?$val:'' ?>";
}
function parsePageCSS($matches)
{
	$val = $matches[1];
	return "<? module(\"page:style\", '$val') ?>";
}

?>