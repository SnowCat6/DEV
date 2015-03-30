<? function text_table($val, &$data)
{
	if (!is_array($data)) return;

	$val	= '';
	foreach($data as $line)
	{
		if (!$line || !is_array($line))
			continue;

		$val	.= '<table><tr>';
		$prev	= 0;
		foreach($line as $ix => $col)
		{
			if ($ix == 0 ){
				if ($line[0]){
					if (count($line) > 1){
						$val	.= "<td class=\"cellName\">$col</td>";
					}else{
						$val	.= "<td class=\"cellTitle\">$col</td>";
					}
				}
				continue;
			}
			
			if ($ix == 1 && $line[0])
				$val	.= "<th></th>";

			if ($line[0])	$val	.= "<td class=\"cellValue\">$col</td>";
			else $val	.= "<td class=\"cellNote\">$col</td>";
		}
		$val	.= '</tr></table>';
	}
	$data	= "<div class=\"tableProperty\">$val</div>";
}?>