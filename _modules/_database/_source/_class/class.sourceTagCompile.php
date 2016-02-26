<?
class sourceTagCompile extends tagCompile
{
	function onTagCompile($name, $props, $ctx, $options)
	{
		list($tagName, $name, $moduleName) = explode(':', $name, 3);
		if (!$name) $name = 'default';

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
		if (!$source) $source = 'default';

		list($tagName, $name) = explode(':', $name, 2);
		if (!$name) $name = 'data';

		$cfg		= $props;
		$cfg['name']= $name;
		$rowCode	= array();

		if (is_int(strpos($source, '$')))
		{
			$sourceName	= $source;
			$code		= '';
		}else
		if (is_int(strpos($source, ':')))
		{
			$sourceName	= "\$source_$name";
			$query		= $props; 
			$query['rows']	= ''; unset($query['rows']);
			$query['source']= ''; unset($query['source']);
			
			$p	= $props['@'];
			if ($p){
				$query	= $p;
			}else{
				$query	= makeParseVar($query);
				$query	= 'array(' . implode(',', $query) . ')';
			}
			
			$code		= "<? $sourceName = module(\"$source\", $query) ?>";
		}else
		{
			$sourceName	= "\$source_$source";
			$code		= "<?  $sourceName = config::get(\"source_$source\") ?>";
		}
		
		$compiller	= new eachRowTagCompile('eachrow');
		$ctx		= $compiller->compile($ctx, array('cfg' => &$cfg, 'rowCode' => &$rowCode));
		
		if ($rowCode)
		{
			$rows	= $cfg['rows'];
			$code	.=
"<? if ($sourceName){
	while(\${$name} = {$sourceName}->nextItem()){
		\${$name}_seek = array(\${$name});
		for (\${$name}_ix = 1; \${$name}_ix < $rows; ++\${$name}_ix)
		{ \${$name}_seek[] =  {$sourceName}->nextItem(); }
		?>$ctx<? }} ?>";
		}else{
			$code	.=
"<? if ($sourceName){
	while(\${$name} = {$sourceName}->nextItem()){
		\${$name}_id = \${$name}->itemId();
?>$ctx<? }} ?>";
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
			$code	= "<? foreach(\${$name}_seek as \${$name}_ix => \${$name}){
				\${$name}_id = \${$name}?\${$name}->itemId():NULL; ?>$ctx<? } ?>";
		}else{
			return $ctx;
		}

		$options['rowCode'][]	= $code;
		return $code;
	}
};
?>

<?
class cacheTagCompile extends tagCompile
{
	function onTagCompile($name, $props, $ctx, $options)
	{
		list($tagName, $name) = explode(':', $name, 2);
		if (!$name) $name = 'default';
		
		$param	= makeParseVar($props);
		$param	= 'array(' . implode(',', $param) . ')';
		
		$code = "<? \$cache_param = $param;
if (beginCache(hashData(\$cache_param))){ ?>
$ctx
<? endCache(); } ?>";
		return $code;
	}
};
?>