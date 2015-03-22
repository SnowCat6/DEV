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
		$val	= makeSEOvalue($SEO[':replace'], $val);
		$val	= preg_replace('#\s+#', ' ', $val);
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
	global $_CONFIG;
	$_CONFIG[':SEO_val']	= $SEO;
	
	return preg_replace_callback('#{([^}]*)}#', 'makeSEOvalueFn', $val);
}
function makeSEOvalueFn($val)
{
	global $_CONFIG;
	$SEO	= $_CONFIG[':SEO_val'];
	$val	= $SEO[$val[1]];
	return $val;
}
?>