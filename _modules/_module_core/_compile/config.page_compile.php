<?
//	Компиляция шаблонов загружаемых модулей
//	Компиляция програмного кода, сюда можно вставить компиляцию шаблонов
addEvent('page.compile',	'page_compile');

function module_page_compile($val, &$thisPage)
{
	$GLOBALS['_CONFIG']['page']['compile']		= array();
	$GLOBALS['_CONFIG']['page']['compileLoaded']= array();

	//	<img src="" ... />
	//	Related path, like href="../../_template/style.css"
	$thisPage	= preg_replace('#((href|src)\s*=\s*["\'])([^"\']+_[^\'"/]+/)#i',	'\\1', 	$thisPage);

	//	{!$variable} direct out variable
	$thisPage	= preg_replace_callback('#{!(\$[^}]+)}#','parsePageValDirectFn', $thisPage);

	//	{beginAdmin}  {endAdmin}
	$thisPage	= str_replace('{beginAdmin}',	'<? beginAdmin() ?>',		$thisPage);
	$thisPage	= str_replace('{endAdmin}',		'<? endAdmin($menu) ?>',	$thisPage);
	$thisPage	= str_replace('{endAdminTop}',	'<? endAdmin($menu, true) ?>',	$thisPage);
	$thisPage	= str_replace('{endAdminBottom}','<? endAdmin($menu, false) ?>',$thisPage);

	//	Admin tools
	$thisPage	= str_replace('{header}',	'{{!page:header}}',		$thisPage);
	$thisPage	= str_replace('{admin}',	'{{!admin:toolbar}}',	$thisPage);

	//	{push} {pop:layout}
	$thisPage	= str_replace('{push}',				'<? ob_start() ?>',		$thisPage);
	$thisPage	= preg_replace('#{pop:([^}]+)}#',	'<? module("page:display:\\1", ob_get_clean()) ?>',$thisPage);

	//	<link rel="stylesheet" ... /> => use CSS module
	$thisPage	= preg_replace_callback('#<link[^>]+href\s*=\s*[\'"]([^>\'"]+)[\'"][^>]*>#i','parsePageCSS', $thisPage);

	//	{beginCompile:compileName}  {endCompile:compileName}
	$thisPage	= preg_replace('#{beginCompile:([^}]+)}#', '<?  if (beginCompile(\$data, "\\1")){ ?>', $thisPage);
	$thisPage	= preg_replace('#{endCompile:([^}]+)}#', '<?  endCompile(\$data, "\\1"); } ?>', $thisPage);
	$thisPage	= str_replace('{document}',	'<? document($data) ?>',$thisPage);

	//	{{moduleName=values}}
	$thisPage	= preg_replace_callback('#{{([^}]+)}}#', 'parsePageFn', 	$thisPage);

	//	{$variable} htmlspecialchars out variable
	$thisPage	= preg_replace_callback('#{(\$[^}]+)}#', 'parsePageValFn', $thisPage);

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

?>