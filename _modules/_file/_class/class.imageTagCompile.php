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
			$clip	= $props['@clip'];
			if (!$clip) $clip = "{$width}x{$height}";
			$upload	= $props['@folder'];

			$cfg	= array(
				'clip'		=> $clip,
				'default'	=> $src,
				'uploadFolder'	=> $upload
			);
		}
		if (isset($props['@size']))
		{
			$width	= $p['width'];
			$height	= $p['height'];
			$src	= $p['src'];
			$size	= $props['@size'];
			if (!$size) $size = "{$width}x{$height}";
			$upload	= $props['@folder'];

			$cfg	= array(
				'size'		=> $size,
				'default'	=> $src,
				'uploadFolder'	=> $upload
			);
		}
		if (isset($props['@mask']))
		{
			$width	= $p['width'];
			$height	= $p['height'];
			$src	= $p['src'];
			$mask	= $p['@mask'];
			$upload	= $props['@folder'];

			$cfg	= array(
				'mask'		=> $mask,
				'default'	=> $src,
				'uploadFolder'	=> $upload
			);
		}
	
		if (!$cfg) return;
		
		$exclude= explode(',', '@folder,@clip,@size,@mask,src,width,height,default');
		foreach($exclude as $name){
			$p[$name] 	= ''; unset($p[$name]);
		}
		$cfg['property']= $p;
		$cfg['hasAdmin'] = "true";
		
		$code	= makeParseVar($cfg);
		$code	= 'array(' . implode(',', $code) . ')';

		$code	= "<? module('file:image', $code) ?>";
		return $code;
	}
};
?>
