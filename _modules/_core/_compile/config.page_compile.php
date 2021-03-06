<?
//	Компиляция шаблонов загружаемых модулей
//	Компиляция програмного кода, сюда можно вставить компиляцию шаблонов
addEvent('page.compile:after',	'page_compile');
function module_page_compile($val, &$ev)
{
	$thisPage	= &$ev['content'];
	
	config::set('pageCompile',	array());
	config::set('compileLoaded',array());

	//	<img src="" ... />
	//	Related path, like href="../../_template/style.css"
	$compile	= new pathCompile("img");
	$thisPage	= $compile->compile($thisPage);
	
	//	<link rel="stylesheet" ... /> => use CSS module
	$compile	= new cssCompile("link");
	$thisPage	= $compile->compile($thisPage);
	
	//	<script src=... use script module
	$compile	= new scriptCompile("script");
	$thisPage	= $compile->compile($thisPage);

	//	{push} {pop:layout}
	$thisPage	= str_replace('{push}',				'<? ob_start() ?>',		$thisPage);
	$thisPage	= preg_replace('#{pop:([^}]+)}#',	'<? module("page:display:\\1", ob_get_clean()) ?>', $thisPage);

	//	{!$variable} direct out variable
	$thisPage	= preg_replace_callback('#{!(\$[^}]+)}#', 
	function($matches){
		return parseParseVarFn($matches[1], '');
	}, $thisPage);
	
	/***************************************/
	//	Angular escape
	$thisPage	= preg_replace_callback('#<(([^>\s]+)[^>]+ng-app[^>]*)>(.*?)</\2>#s', 
	function($val)
	{
		$val	= $val[0];
		$val	= preg_replace('#{{([^}]+)}}#', '{!$1!}', $val);
		return $val;
	}, $thisPage);
	
	//	{$variable} htmlspecialchars out variable
	$thisPage	= preg_replace_callback('#{(\$[^}]+)}#',
	function($matches){
		return parseParseVarFn($matches[1], 'htmlspecialchars');
	}, $thisPage);
	
	//	{{moduleName=values}}
	$thisPage	= preg_replace_callback('#{{([^}]+)}}#', 'parsePageFn', 	$thisPage);
	
	//	Angular unescape
	$thisPage	= preg_replace_callback('#{!([^}]+)!}#', 
	function($val)
	{
		return "{{" . $val[1] . "}}";
	}, 	$thisPage);
	/***************************************/
	
	//	{checked:$varName}	=> checked="checked" class="current"
	//	{selected:$varName}=> selected="selected" class="current"
	$thisPage	= preg_replace_callback('#{(checked|selected|(class)\.([^:]+)):(\$[^}]+)}#',
	function($matches)
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
	}, $thisPage);
	
	//	{hidden:name:$valueVarName}	=> <input type=hidden name=name value=valueVarName />

	$root		=	globalRootURL;
	//	Ссылка не должна начинаться с этих символов
	$notAllow	= preg_quote('/#\'"<{', '#');
	$thisPage	= preg_replace("#((href|src)\s*=\s*[\"\'])(?!\w+://|//|mailto:)([^$notAllow])#i", "\\1$root/\\3", 	$thisPage);

	$thisPage	= $thisPage.implode('', array_reverse(config::get('compileLoaded')));

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

function makeParseVar($values)
{
	if (!is_array($values))
		return $values?makeParseValue($values):'';
	
	$v	= array();
	foreach($values as $name => $val)
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
	if (!preg_match('#^(\$[\w\d+_\[\]]+)$#', $val))
		return "\"$val\"";

	return preg_replace('#\[([^\]]*)\]#', "[\"\\1\"]", $val);
//	return $val;
}
function parsePageFn($matches)
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

	$compileLoaded	= config::get('compileLoaded');
    $compileLoaded[]= "<? \$p = ob_get_clean(); $code; echo \$p; ?>";
	config::set('compileLoaded', $compileLoaded);

	return "<? ob_start() ?>";
}
function parseParseVarFn($val, $fn)
{
	//	[value:charLimit OR in future function]
	$thisVal= explode('|', $val, 2);
	if (count($thisVal) == 1) $thisVal = explode('=', $val, 2);
	
	//	[value] => ['value']
	$bCheck	= is_int(strpos($thisVal[0], ']['));
	$v		= preg_replace('#\[([^\]]*)\]#', "[\"\\1\"]", $thisVal[0]);

	$fx		= array();
	$val	= $v;
	
	if (count($thisVal) > 1)
	{
		foreach(explode('|', $thisVal[1]) as $v2)
		{
			if (!$v2) $v2 = 100;
			if (preg_match('#^\d+$#', $v2)) $fx[]	= "note:$v2";
			else $fx[]	= $v2;
		}
	}

	if ($fx){
		$fx[]	= 'show';
		$fx		= implode('|', $fx);
		$val	= "m('text:$fx', $val)";
	}
	if ($fn) $val = "$fn($val)";
	
	if ($bCheck) return "<? if(isset($v)) echo $val; ?>";
	return "<?= $val ?>";
}

?>