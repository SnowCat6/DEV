<?
function text_split($v, &$data)
{
	$val	= $data;
	$data	= array();
	
	$val	= preg_split("#<br>|<br />|</p>|\r\n#i", $val);
	foreach($val as $ix => $v)
	{
		$v = strip_tags($v);
		$v	= trim($v);
		if ($v) $val[$ix]	= $v;
		else unset($val[$ix]);
	}
	
	foreach($val as $ix => $v)
	{
		//	Строка текста
		if (preg_match('#(.*)\s+([\d\s\./]+(гр|гр.|кус|кус.))\s+([\d\s\.,]+)#i', $v, $v1))
		{
			unset($v1[0]);
			unset($v1[3]);
			$data[]	= array_values($v1);
			continue;
		}
		//	Если начинается со скобки, зачит комментарий
		if ($v[0] == '(')
		{
			$data[]	= array('', $v);
			continue;
		}
		//	Пробуем вариант с разделителем двоеточия
		$v1			= explode(':', $v, 2);
		if (count($v1) > 1)
		{
			$data[]	= array_merge(array($v1[0]), explode("\t", $v1[1]));
			continue;
		}
		//	Пробуем вариант с табуляцией
		$v1			= explode("\t", $v);
		if (count($v1) > 1)
		{
			$data[]	= $v1;
			continue;
		}
		//	Просто строка, так и запишем
		$data[]	= array($v);
	}
}
?>