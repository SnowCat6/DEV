<?
addEvent('storage.get',	'file:storage:get');
addEvent('storage.set',	'file:storage:set');

addAccess('file:(.*)',	'file_file_access');
?>

<?
addEvent('page.compile:before',	'htmlImageCompile');
function module_htmlImageCompile($val, &$ev)
{
	$thisPage	= &$ev['content'];
	
	$compiller	= new imageTagCompile('img');
	$thisPage	= $compiller->compile($thisPage);
}
?>

<?
class imageTagCompile extends tagCompile
{
	function onTagCompile($name, $props, $ctx, $options)
	{
		if (isset($props['@clip']))
		{
			$p		= self::makeLower($props);
			$width	= $p['width'];
			$height	= $p['height'];
			$src	= $p['src'];

			$cfg	= array('clip' => "{$width}x{$height}", 'src' => trim($src, "{}"));
			$code	= makeParseVar($cfg);
			$code	= 'array(' . implode(',', $code) . ')';

			$code	= "<? module('file:image', $code) ?>";
			return $code;
		}
	}
};
?>
