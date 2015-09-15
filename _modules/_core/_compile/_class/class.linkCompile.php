<?
class cssCompile extends tagCompile
{
	function onTagCompile($tagName, $property, $content, $options)
	{
		$rel	= strtolower($property['rel']);
		$src	= strtolower($property['href']);
		$type	= strtolower($property['type']);
		
		if ($src == '')				return;
		if ($rel != 'stylesheet')	return;
		if ($type != 'text/css')	return;
		if (strncmp($src, 'http://', 7) == 0)return;
		if (strncmp($src, '//', 2) == 0) 	return;

		$src	= preg_replace('#(^.*_[^/]*/|\.\./)#', '', $property['href']);
		return "<? module('fileLoad', '$src') ?>";
	}
};

class scriptCompile extends tagCompile
{
	function onTagCompile($tagName, $property, $content, $options)
	{
		$src	= strtolower($property['src']);
		$type	= strtolower($property['type']);
		
		if ($src == '') 						return;
		if ($type && $type != 'text/javascript')return;
		if (strncmp($src, 'http://', 7) == 0)	return;
		if (strncmp($src, '//', 2) == 0) 		return;

		$src	= preg_replace('#(^.*_[^/]*/|\.\./)#', '', $property['src']);
		return "<? module('fileLoad', '$src') ?>";
	}
};

class pathCompile extends tagCompile
{
	function onTagCompile($tagName, $property, $content, $options)
	{
		if (!$property['src']) return;
		$src	= preg_replace('#(^.*_[^/]*/|\.\./)#', '', $property['src']);
		$property['src']	= $src;
		
		return self::makeTag($tagName, $property, $content);
	}
};

?>