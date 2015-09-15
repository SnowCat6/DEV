<?
class moduleTagCompile extends tagCompile
{
	function onTagCompile($name, $props, $ctx, $options)
	{
		$name		= explode(':', $name, 2);
		$moduleName	= $name[1] . $props['+'];
		$props['+']	= '';

		$choose		= $props['?'];
		$props['?']	= '';
		if ($choose){
			$choose = "if (\"$choose\")";
		}
		
		$data	= $props['@'] or array();
		$props['@']	= '';
		
		foreach($props as $name => $val)
		{
			if (!$val) continue;
			//	Контент если есть между тегами
			
			$d	= &$data;
			foreach(explode('.', $name) as $n) $d = &$d[$n];
			$d	= $val=='@'?str_replace('"', '\\"', $ctx):$val;
		}
		$data	= makeParseVar($data);
	
		if ($data){
			if (is_array($data)){
				$code	= 'array(' . implode(',', $data) . ')';
				$code	= "module(\"$moduleName\", $code)";
			}else{
				$code	= "module(\"$moduleName\", $data)";
			}
		}else{
			$code	= "module(\"$moduleName\")";
		}
		
		if ($choose) return "<? $choose $code ?>";
		return "<? $code ?>";
	}
};
?>