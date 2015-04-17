<? function text_table($class, &$data)
{
	if (!is_array($data)) return;
	
	m('fileLoad', 'css/tableProperty.css');

	$val	= '';
	foreach($data as $line)
	{
		if (!$line || !is_array($line))
			continue;
			
		if (count($line) == 1){
			$val .= "<div class=\"cellTitle\">$line[0]</div>";
			continue;
		}

		$prev	= 0;
		$val	.= '<table><tr>';
		foreach($line as $ix => $col)
		{
			if ($ix == 0 ){
				if ($line[0]) $val	.= "<td class=\"cellName\">$col</td>";
				else $val	.= "<td></td>";
				continue;
			}
			
			if ($ix == 1 && $line[0])
				$val	.= "<th></th>";

			if ($line[0])	$val	.= "<td class=\"cellValue\">$col</td>";
			else $val	.= "<td class=\"cellNote\">$col</td>";
		}
		$val	.= '</tr></table>';
	}
	$data	= "<div class=\"tableProperty $class\">$val</div>";
}?>