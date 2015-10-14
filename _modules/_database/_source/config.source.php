<?
addEvent('page.compile:before',	'htmlSourceCompile');
function module_htmlSourceCompile($val, &$ev)
{
	$thisPage	= &$ev['content'];
	
	$compiller	= new sourceTagCompile('source:');
	$thisPage	= $compiller->compile($thisPage);
	
	$compiller	= new eachTagCompile('each');
	$thisPage	= $compiller->compile($thisPage);
}
?>


<?
class sourceTagCompile extends tagCompile
{
	function onTagCompile($name, $props, $ctx, $options)
	{
		list($tagName, $name, $moduleName) = explode(':', $name, 3);
		if (!$name) return "";

		$code	= '';
		if ($moduleName){
			$param	= makeParseVar($props);
			$param	= 'array(' . implode(',', $param) . ')';
			$code	= "module(\"$moduleName\", $param)";
			$code	= "<? config::set(\"source_$name\", $code) ?>";
		}else{
			$code	=  "<? function source_$name(){ ?>$ctx<? } ?>";
			$code	.= "<? config::set(\"source_$name\", source_$name()) ?>";
		}

		return $code;
	}
};
?>

<?
class eachTagCompile extends tagCompile
{
	function onTagCompile($name, $props, $ctx, $options)
	{
		$source	= $props['source'];
		if (!$source) return "";

		list($tagName, $name) = explode(':', $name, 2);
		if (!$name) $name = 'data';

		$cfg		= $props;
		$cfg['name']= $name;
		$rowCode	= array();

		$compiller	= new eachRowTagCompile('eachrow');
		$ctx		= $compiller->compile($ctx, array('cfg' => &$cfg, 'rowCode' => &$rowCode));
		
		if (is_int(strpos($source, '$'))){
			$sourceName	= $source;
			$code		= '';
		}else{
			$sourceName	= "\$source_$source";
			$code		= "<?  $sourceName = config::get(\"source_$source\") ?>";
		}
		
		if ($rowCode)
		{
			$rows	= $cfg['rows'];
			$code	.=	"<? if ($sourceName){ " .
						"while(\$$name = {$sourceName}->next()){ " .
						"\${$name}_seek = array(\$$name); " .
						"for (\${$name}_ix = 1; \${$name}_ix < $rows; ++\${$name}_ix)" .
						"{ \${$name}_seek[] =  {$sourceName}->next(); }" .
						"?>";
			$code	.=$ctx;
			$code	.="<? }} ?>";
		}else{
			$code	.=	"<? if ($sourceName){ " .
						"while(\$$name = {$sourceName}->next()){ ?>";
			$code	.=$ctx;
			$code	.="<? }} ?>";
		}
		return $code;
	}
};
?>

<?
class eachRowTagCompile extends tagCompile
{
	function onTagCompile($name, $props, $ctx, $options)
	{
		$cfg	= $options['cfg'];
		$rows	= $cfg['rows'];
		$name	= $cfg['name'];
		
		if ($rows)
		{
			$code	= "<? foreach(\${$name}_seek as \${$name}_ix => \$$name){ ?>" .
					$ctx .
					"<? } ?>";
		}else{
			return $ctx;
		}

		$options['rowCode'][]	= $code;
		return $code;
	}
};
?>