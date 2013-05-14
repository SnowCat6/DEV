<?
function module_doc_compile($v, &$val)
{
	$val = preg_replace_callback('%(<img\s+[^>]+/>)%i',	parseImageFn,	$val);
	$val = preg_replace_callback('%(<a[^>]+youtube[^>]+>[^<]+</a>)%i', parseYoutubeFn, $val);
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
	if (is_int(strpos($val, '://'))) return $val;
	if (is_int(strpos($val, '/thumb'))) return $val;
//	if (!is_int(strpos($val, 'Image')) &&
//		!is_int(strpos($val, 'Title'))) return $val;
	if (!preg_match_all('%(\w+)\s*=\s*[\'\"]{0,1}([^\'\"]+)[\'\"]{0,1}%', $val, $m)) return $val;
	
	$attr	= '';
	$src	= ''; $w=0; $h=0; $alt=''; $border=0; $zoom=false;
	foreach($m[1] as $ndx=>$name){
		$name 	= strtolower($name);
		$v	= $m[2][$ndx];
		switch($name){
		case 'src':		$src= localHostPath.'/'.$v;	break;
		case 'width':	$w	= $v;	break;
		case 'height':	$h	= $v;	break;
		case 'border':	$h	= $v;	break;
		case 'alt':		$alt= $v;	break;
		case 'rel':		$zoom= $v == 'lightbox';		break;
		default:
			$attr .= $m[1][$ndx].'="'.$v.'"';
		}
	}
	if (!$w || !$h) return $val;

	@list($iw, $ih) = getimagesize($src);
	if ($iw == $w) return $val;
	
	ob_start();
	$attr .= " border=\"$border\"";
	displayThumbImage($src, $w, $attr, $alt, $zoom?$src:'');
	if ($zoom) echo '{'.'{script:lightbox}'.'}';
	return ob_get_clean();
}

?>