<?
//	Компиляция шаблонов загружаемых модулей
//	Компиляция програмного кода, сюда можно вставить компиляцию шаблонов
addEvent('page.compile:after',	'page_compile');
function module_page_compile($val, &$ev)
{
	$thisPage	= &$ev['content'];
	
	global $_CONFIG;
	$_CONFIG['page']['compile']		= array();
	$_CONFIG['page']['compileLoaded']= array();

	//	<img src="" ... />
	//	Related path, like href="../../_template/style.css"
	$thisPage	= preg_replace('#((href|src)\s*=\s*["\'])([^"\']+_[^\'"/]+/)#i',	'\\1', 	$thisPage);
	//	<link rel="stylesheet" ... /> => use CSS module
	$thisPage	= preg_replace_callback('#<link[^>]+href\s*=\s*[\'"]([^>\'"]+)[\'"][^>]*>#i','parsePageCSS', $thisPage);
	//	<script src=...
	$thisPage	= preg_replace_callback('#<script[^>]+src\s*=\s*[\'"]([^>\'"]+)[\'"][^>]*>\s*</script>#i','parsePageScript', $thisPage);

	//	{push} {pop:layout}
	$thisPage	= str_replace('{push}',				'<? ob_start() ?>',		$thisPage);
	$thisPage	= preg_replace('#{pop:([^}]+)}#',	'<? module("page:display:\\1", ob_get_clean()) ?>',$thisPage);

	//	{!$variable} direct out variable
	$thisPage	= preg_replace_callback('#{!(\$[^}]+)}#','parsePageValDirectFn', $thisPage);
	
	//	{$variable} htmlspecialchars out variable
	$thisPage	= preg_replace_callback('#{(\$[^}]+)}#', 'parsePageValFn', $thisPage);
	
	//	{{moduleName=values}}
	$thisPage	= preg_replace_callback('#{{([^}]+)}}#', 'parsePageFn', 	$thisPage);
	
	//	{checked:$varName}	=> checked="checked" class="current"
	//	{selected:$varName}=> selected="selected" class="current"
	$thisPage	= preg_replace_callback('#{(checked|selected|(class)\.([^:]+)):(\$[^}]+)}#', 'parseCheckedValFn', $thisPage);
	
	//	{hidden:name:$valueVarName}	=> <input type=hidden name=name value=valueVarName />

	$root		=	globalRootURL;
	//	Ссылка не должна начинаться с этих символов
	$notAllow	= preg_quote('/#\'"<{', '#');
	$thisPage	= preg_replace("#((href|src)\s*=\s*[\"\'])(?!\w+://|//)([^$notAllow])#i", "\\1$root/\\3", 	$thisPage);

	$thisPage	= $thisPage.implode('', array_reverse($GLOBALS['_CONFIG']['page']['compileLoaded']));

/******************************/
//	OPTIMIZE GENERATED CODE
/******************************/

	$ini		= getIniValue(':');
	$bOptimize	= $ini['optimizePHP'];
	if ($bOptimize != 'yes') return;

	//	Remove HTML comments
	$thisPage	= preg_replace('#<\!--(.*?)-->#', 	'', 		$thisPage);
	$thisPage	= preg_replace('#(\?\>)\s*(\<\?)#', '\\1\\2',	$thisPage);

	//	Remove PHP white space
	$thisPage	= preg_replace('#^\s*(\<\?)#',	'\\1',		$thisPage);
	$thisPage	= preg_replace('#(\?\>)\s*$#',	'\\1',		$thisPage);
	$thisPage	= preg_replace('#^(//\s+(?!\+function).*)$#m',	'',			$thisPage);

	$thisPage	= preg_replace('#^(\s*)#m',		'',			$thisPage);
}
function quoteArgs($val){
	$val	= str_replace('"', '\\"', $val);
	$val	= str_replace('(', '\\(', $val);
	$val	= str_replace(')', '\\)', $val);
	return $val;
}
function makeParseVar(&$values)
{
	$v	= array();
	foreach($values as $name=>&$val)
	{
		if (is_array($val)){
			$v[]	= "'$name'=>array(" . implode(',', makeParseVar($val)) . ')';
		}else{
			$val	= makeParseValue($val);
			$v[]	= "'$name'=>$val";
		}
	}
	return $v;
}
function makeParseValue($val)
{
	if (preg_match('#^(\$[\w\d+_]+)$#', $val))
		return $val;

	return "\"$val\"";
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
	$values	= array();
	$d		= explode(';', $moduleData);
	foreach($d as $row)
	{
		//	val					=> [] = val
		//	name:val			=> [name] = val
		//	name.name.name:val	=> [name][name][name] = val;
		$name = NULL; $val = NULL;
		list($name, $val) = explode(':', $row, 2);
		if (!$name) continue;
		
		if (isset($val)){
			$name	= explode('.', $name);
			$d		= &$values;
			while(list(,$n) = each($name)) $d = &$d[$n];
			$d		= $val;
		}else{
			$data[]	= makeParseValue($name);
		}
	}
	if ($values){
		$data[]	=	'array(' . implode(',', makeParseVar($values)) . ')';
	}
	
	if ($data){
		//	new code
		if (count($data) > 1 ) $code	= 'array(' . implode(',', $data) . ')';
		else $code = $data[0];
		
		$code	= "module(\"$moduleName\", $code)";
	}else{
		$code	= "module(\"$moduleName\")";
	}

	if (!$bPriorityModule) return "<? $code ?>";

	$GLOBALS['_CONFIG']['page']['compileLoaded'][] = "<? \$p = ob_get_clean(); $code; echo \$p; ?>";
	return "<? ob_start() ?>";
}
function parsePageValFn(&$matches)
{
	$val	= $matches[1];
	//	[value:charLimit OR in future function]
	$val	= explode('=', $val, 2);
	//	[value] => ['value']
	$bCheck	= is_int(strpos($val[0], ']['));
	$v		= preg_replace('#\[([^\]]*)\]#', "[\"\\1\"]", $val[0]);
	//	$valName //	$valName[xx][xx]
	//	isset($valName[xx][xx])?$valName[xx][xx]:''
	if (count($val) == 1)
		return $bCheck?"<? if(isset($v)) echo htmlspecialchars($v) ?>":"<?= htmlspecialchars($v)?>";

	$v1	= $val[1];
	if (!$v1) $v1 = 50;
	return $bCheck?"<? if(isset($v)) echo htmlspecialchars(makeNote($v, \"$v1\")) ?>":"<?= htmlspecialchars(makeNote($v, \"$v1\"))?>";
}

function parsePageValDirectFn(&$matches)
{
	$val = $matches[1];
	//	[value:charLimit OR in future function]
	$val= explode('=', $val, 2);
	//	[value] => ['value']
	$bCheck	= is_int(strpos($val[0], ']['));
	$v		= preg_replace('#\[([^\]]*)\]#', "[\"\\1\"]", $val[0]);
	if (count($val) == 1)
		return $bCheck?"<? if(isset($v)) echo $v ?>":"<?= $v ?>";

	$v1	= $val[1];
	if (!$v1) $v1 = 100;
	return $bCheck?"<? if(isset($v)) echo makeNote($v, \"$v1\") ?>":"<?= makeNote($v, \"$v1\") ?>";
}
function parseCheckedValFn(&$matches)
{
	$val	= $matches[4];
	//	[value] => ['value']
	$bCheck	= is_int(strpos($val[0], ']['));
	$v		= preg_replace('#\[([^\]]*)\]#', "[\"\\1\"]", $val);
	
	if ($matches[2] == 'class')
	{
		$class	= $matches[3];
		return "<?= ($v)?' class=\"$class\"':''?>";
	}

	$type	= $matches[1];
	return "<?= ($v)?' $type=\"$type\" class=\"current\"':''?>";
}
function parsePageCSS(&$matches)
{
	$val	= $matches[0];
	if (!is_int(strpos($val, 'stylesheet'))) return $val;
	
	$val	= $matches[1];
	$val	= str_replace('../', '', $val);
	return "<? module('fileLoad', '$val') ?>";
}
function parsePageScript(&$matches)
{
	$val	= $matches[1];
	$val	= str_replace('../', '', $val);
	return "<? module('fileLoad', '$val') ?>";
}
?>