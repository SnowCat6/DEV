<? function text_table($class, &$data)
{
	if (!is_array($data)) return;
	
	m('fileLoad', 'css/tableProperty.css');

	$val	= '';
	$row	= 0;
	foreach($data as $line)
	{
		if (!$line || !is_array($line))
			continue;
			
		if (count($line) == 1){
			$val	.= "<div class=\"cellTitle\">$line[0]</div>";
			$row	= 0;
			continue;
		}
		++$row;
		$prev	= 0;
		$val	.= $row%2?'<table class="rowAlt"><tr>':'<table><tr>';
		foreach($line as $ix => $col)
		{
			if ($ix == 0 ){
				if ($line[0]) $val	.= "<td class=\"cellName\">$col</td>";
				else $val	.= "<td></td>";
				continue;
			}
/*			
			if ($ix == 1 && $line[0])
				$val	.= "<th></th>";
*/
			if ($line[0])	$val	.= "<td class=\"cellValue c$ix\">$col</td>";
			else{
				if ($col[0] == '[') $val	.= "<td class=\"cellAccent\">$col</td>";
				else $val	.= "<td class=\"cellNote\">$col</td>";
			}
		}
		$val	.= '</tr></table>';
	}
	$data	= "<div class=\"tableProperty $class\">$val</div>";
}?>