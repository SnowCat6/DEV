<? function text_summ($options, &$data)
{
	list($col, $template) = explode(':', $options, 2);
	$col	= (int)$col;
	if (!is_array($data) || !$col) return;
	
	$totalSumm	= 0;
	foreach($data as $val)
	{
		if (is_array($val))
		{
			$v	= $val[$col];
			$v	= preg_replace('#[^\d]#', '', $v);
			$totalSumm += (int)$v;
		}
	}
	$totalSumm	= number_format($totalSumm, 0, '', ' ');
	if ($template) $totalSumm = str_replace('%', $totalSumm, $template);
	
	$val		= array();
	for($ix=0; $ix<$col; ++$ix)	$val[$ix] = '';
	$val[$col]	= "<div class=\"totalSumm\">$totalSumm</div>";
	$data[]		= $val;
}
?>
