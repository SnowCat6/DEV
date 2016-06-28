<?
function module_SEO($val, &$data)
{
	list($fn, $val) = explode(':', $val);
	$fn	= getFn("SEO_$fn");
	return $fn?$fn($val, $data):NULL;
}
function SEO_set($val, $SEO)
{
	if (!is_array($SEO)) return;

	foreach($SEO as $name => $val)
	{
		//	replace snippet code
		$val	= snippets::compile($val);
		//	replace SEO template
		$val	= makeSEOvalue($SEO[':replace'], $val);
		$val	= preg_replace('#\s+#', ' ', $val);
		$val	= preg_replace('#([\,\.\-\:])(\s*[\,\.\-\:])+#', '\\1', $val);
		$val	= trim($val);
		if (!$val) continue;
		
		switch($name){
		case ':replace':
			break;
		case 'title':
			moduleEx('page:title:siteTitle', $val);
			break;
		default:
			moduleEx("page:meta:$name", $val);
		}
	};
}

function makeSEOvalue($SEO, $val)
{
	return preg_replace_callback('#{([^}]*)}#', 
	function($val) use($SEO)
	{
		$val	= $val[1];
		list($prefix, $value, $postfix)	= explode('?', $val);
		if (!$value) return $SEO[$val];
		$value	= $SEO[$value];
		return $value?"$prefix $value $postfix":'';
	}, $val);
}
?>