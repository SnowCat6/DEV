<?
function text_note($nLen, &$val)
{
	$nLen	= (int)$nLen;
	$val	= strip_tags($val);
	$val	= preg_replace('#(\s+)#', ' ', $val);
	$val	= trim($val);
	if (!function_exists('mb_strrpos')){
		if (strlen($val) <= $nLen) return $val;
		while(ord($val[$nLen]) >= 0x80) ++$nLen;
		$val	= substr($val, 0, $nLen);
	}else{
		$minLen	= $nLen - $nLen / 3;
		if (strlen($val) <= $nLen) return $val;
		
		$val	= mb_substr($val, 0, $nLen, 'UTF-8');
		if (is_int($nPos = mb_strrpos($val, '.')) && $nPos > $minLen)		$val = mb_substr($val, 0, $nPos+1, 'UTF-8');
		else if (is_int($nPos = mb_strrpos($val, '!')) && $nPos > $minLen)	$val = mb_substr($val, 0, $nPos+1, 'UTF-8');
		else if (is_int($nPos = mb_strrpos($val, '?')) && $nPos > $minLen)	$val = mb_substr($val, 0, $nPos+1, 'UTF-8');
	}

	return $val .= ' ..';
}

?>