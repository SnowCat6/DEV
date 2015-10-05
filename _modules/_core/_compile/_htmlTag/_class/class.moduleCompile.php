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
		
//		$data	= $props['@'] or array();
//		$props['@']	= '';
		$code2	= '';
		$data	= array();
		
		foreach($props as $name => $val)
		{
			if (!$val) continue;
			
			//	Контент если есть между тегами
			if ($val == '@'){
//				$val	= str_replace('"', '\\"', $ctx);
				$code2	= 'ob_start() ?>' . $ctx . '<? $module_content = ob_get_clean(); ';
				$val	= '$module_content';
			}
			
			$d	= &$data;
			foreach(explode('.', $name) as $n) $d = &$d[$n];
			$d	= $val;
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
		
		if ($choose){
			if ($code2)	return "<? $choose{ $code2 $code; }?>";
			return "<? $choose $code ?>";
		}
		return "<? $code2 $code ?>";
	}
};
?>