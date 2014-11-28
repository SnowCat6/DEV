<?
function module_doc_compile($v, &$thisPage)
{
	$thisPage = preg_replace_callback('%(<img\s+[^>]+/>)%i',				'parseImageFn',		$thisPage);
	$thisPage = preg_replace_callback('%(<a[^>]+youtube[^>]+>[^<]+</a>)%i', 'parseYoutubeFn',	$thisPage);

	$root		=	globalRootURL;
	//	Ссылка не должна начинаться с этих символов
	$notAllow	= preg_quote('/#\'"<{', '#');
	$thisPage	= preg_replace("#((href|src)\s*=\s*[\"\'])(?!\w+://|\w+:)([^$notAllow])#i", "\\1$root/\\3", 	$thisPage);
}
function getYoutubeID(&$val){
	return preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $val, $m)
		?$m[1]:'';
}
function parseYoutubeFn($matches){
	@$link	=  getYoutubeID($matches[1]);
	if (!$link) return $matches[0];

	$link	= "<iframe width=\"480\" height=\"360\" src=\"http://youtube.com/embed/$link?rel=0&wmode=transparent\" frameborder=\"0\" allowfullscreen></iframe>";
	return $link;
}
function getYoutubeThumb(&$doc){
	if (!preg_match('%(<a[^>]+youtube[^>]+>[^<]+</a>)%i', $doc, $v))
		return'';
	return getYoutubeID($v[1]);
}
function parseImageFn($matches)
{
	@$val	=  $matches[0];
	if (is_int(strpos($val, '://'))) 	return $val;
	if (is_int(strpos($val, '/thumb'))) return $val;

	$m	= array();
	preg_match_all('%(\w+)\s*=\s*[\'\"]{0,1}([^\'\"]+)[\'\"]{0,1}%', $val, $m);

	$attr	= array();
	$src	= ''; $w=0; $h=0; $zoom=false;
	$style	= array();
	
	if ($m)
	foreach($m[1] as $ndx => &$name)
	{
		$v	= $m[2][$ndx];
		switch(strtolower($name))
		{
		case 'src':		$src= localRootPath.'/'.$v;	break;
		case 'width':	$w	= $v;	break;
		case 'height':	$h	= $v;	break;
		case 'rel':		$zoom= $v == 'lightbox';		break;
		case 'style':	$style	= parseImageStyle($v);	break;
		default:
			$attr[$name]	= $v;
		}
	}
	
	$s2	= array();
	foreach($style as $name => &$v2)
	{
		switch(strtolower($name))
		{
		case 'width':	$w	= (int)$v2;	break;
		case 'height':	$h	= (int)$v2;	break;
		default:
			$s2[]	= "$name:$v2";
		continue;
		}
		unset($style[$name]);
	}
	$attr['style']	= implode(';', $s2);
	$attr['src']	= $src;
	$attr['width']	= $w;
	$attr['height']	= $h;
	
	if (!$w || !$h) return $val;
	list($iw, $ih) = getimagesize($src);
	if ($iw == $w) return $val;

	$v	= mEx("image:displayThumbImage", $attr);
	return $v?$v:$val;
}
function parseImageStyle($v)
{
	$prop	= array();
	$v		= explode(';', $v);
	foreach($v as $val){
		$val	= explode(':', $val);
		$prop[trim($val[0])]	= trim($val[1]);
	}
	return $prop;
}
?>