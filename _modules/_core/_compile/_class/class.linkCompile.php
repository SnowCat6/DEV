<?
class cssCompile extends tagCompile
{
	function onTagCompile($tagName, $property, $content, $options)
	{
		$p		= self::makeLower($property);
		$rel	= strtolower($p['rel']);
		$src	= strtolower($p['href']);
		$type	= strtolower($p['type']);
		
		if ($src == '')				return;
		if ($rel != 'stylesheet')	return;
		if ($type != 'text/css')	return;
		if (strncmp($src, 'http://', 7) == 0)return;
		if (strncmp($src, '//', 2) == 0) 	return;

		$src	= preg_replace('#(^.*_[^/]*/|\.\./)#', '', $p['href']);
		return "<? module('fileLoad', '$src') ?>";
	}
};

class scriptCompile extends tagCompile
{
	function onTagCompile($tagName, $property, $content, $options)
	{
		$p		= self::makeLower($property);
		$src	= strtolower($p['src']);
		$type	= strtolower($p['type']);
		
		if ($src == '') 						return;
		if ($type && $type != 'text/javascript')return;
		if (strncmp($src, 'http://', 7) == 0)	return;
		if (strncmp($src, '//', 2) == 0) 		return;

		$src	= preg_replace('#(^.*_[^/]*/|\.\./)#', '', $p['src']);
		return "<? module('fileLoad', '$src') ?>";
	}
};

class pathCompile extends tagCompile
{
	function onTagCompile($tagName, $property, $content, $options)
	{
		$p		= self::makeLower($property);
		if (!$p['src']) return;
		$src		= preg_replace('#(^.*_[^/]*/|\.\./)#', '', $p['src']);
		$p['src']	= $src;
		
		return self::makeTag($tagName, $p, $content);
	}
};

?>