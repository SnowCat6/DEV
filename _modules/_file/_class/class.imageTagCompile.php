<?
class imageTagCompile extends tagCompile
{
	function onTagCompile($name, $props, $ctx, $options)
	{
		$cfg	= array();
		$p		= self::makeLower($props);

		if (isset($props['@clip']))
		{
			$width	= $p['width'];
			$height	= $p['height'];
			$src	= $p['src'];

			$cfg	= array(
				'clip'		=> "{$width}x{$height}",
				'default'	=> $src
			);
		}
		if (isset($props['@size']))
		{
			$width	= $p['width'];
			$height	= $p['height'];
			$src	= $p['src'];

			$cfg	= array(
				'size'		=> "{$width}x{$height}",
				'default'	=> $src
			);
		}
		if (isset($props['@mask']))
		{
			$width	= $p['width'];
			$height	= $p['height'];
			$src	= $p['src'];
			$mask	= $p['@mask'];

			$cfg	= array(
				'mask'		=> $mask,
				'default'	=> $src
			);
		}
	
		if (!$cfg) return;
		
		$exclude= explode(',', '@clip,@size,@mask,src,width,height,default');
		foreach($exclude as $name){
			$p[$name] 	= ''; unset($p[$name]);
		}
		$cfg['property']= $p;
		
		$code	= makeParseVar($cfg);
		$code	= 'array(' . implode(',', $code) . ')';

		$code	= "<? module('file:image', $code) ?>";
		return $code;
	}
};
?>
