<?
function stat_today($db, &$data)
{
	if (!hasAccessRole('admin,developer,SEO,writer')) return;

	$search	= array();
	$search['date']	= time()-60*60*24;
	$hours			= getHoursByDate($db, stat2sql($search));

	$search['date']	= time();
	$hoursNow		= getHoursByDate($db, stat2sql($search), true);

	m('script:plot');
	$json	= array(
		'title1'	=>  date('d.m.y', time() - 60*60*24),
		'title2'	=>  date('H:i'),
		'hours'		=> array_values($hours),
		'hoursNow'	=> array_values($hoursNow)
	);
?>
<div id="visitorsByHours" rel="{$json|json}"></div>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.highlighter.min.js"></script>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.barRenderer.min.js"></script>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.pointLabels.min.js"></script>

<script src="script/jq.stst.js"></script>
<link rel="stylesheet" type="text/css" href="css/jq.stst.css">

<? } ?>
<? function getHoursByDate($db, $sql, $bApprox = false)
{
	$sql	= implode(' AND ', $sql);
	if ($sql) $sql = " WHERE $sql ";
	
	$table	= $db->table();
	$db->exec("SELECT count(*) AS `c`, `h` FROM(SELECT HOUR(date) AS `h` FROM $table$sql GROUP BY `h`, `userIP`) AS `h` GROUP BY `h`");

	$hours	= array();
	while($data = $db->next())
	{
		$hours[(int)$data['h']]= $data['c'];
	}
	
	$prev	= 0;
	$now	= (int)date('H');
	for($h = 0; $h < 24; ++$h)
	{
		$thisHour	= (int)$h;
		$thisValue	= (int)$hours[$h];
		if ($now && $h == $now && $bApprox){
			$percent	= 1-date('i')/60;
			$thisValue	= floor($thisValue+$prev*$percent);
			$thisHour	= round($thisHour-$percent, 2);
		}
		$prev		= $thisValue;
		if ($bApprox && !$thisValue && $h > $now) $thisValue = 'null';
		$hours[$h]	= array($thisHour, $thisValue);
	}
	return array_values($hours);
}?>