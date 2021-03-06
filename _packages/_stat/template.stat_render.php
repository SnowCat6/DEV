﻿<? function stat_render(&$db, &$data)
{
	if (!hasAccessRole('admin,developer,SEO,writer')) return;

	$max	= 30;
	$rMax	= array();
	$rMin	= array();
	$rAvg	= array();
	
	$search		= array();
	
	$date1		= time()-60*60*24*$max;
	$date2		= time();
	$search['date']	= "$date1-$date2";
	
	$sql	= implode(' AND ', stat2sql($search));
	if ($sql) $sql = " WHERE $sql ";
	
	$table	= $db->table();
	$db->exec("SELECT * FROM (SELECT max(renderTime) rMax, min(renderTime) rMin, avg(renderTime) as rAvg, CONCAT(YEAR(`date`), DAYOFYEAR(`date`)) as `DayOfYear` FROM $table$sql GROUP BY `DayOfYear`) AS `q` GROUP BY `DayOfYear`");
	
	for($d = $date1; $d <= $date2; $d += 60*60*24)
	{
		$date	= date('Y-m-d', $d);
		$d2		= date('Yz', $d);

		$rMax[$d2]	= array($date, 0);
		$rMin[$d2]	= array($date, 0);
		$rAgv[$d2]	= array($date, 0);
	}
	while($data = $db->next())
	{
		$r1	= round($data['rMax'],	4);
		$r2	= round($data['rMin'],	4);
		$r3	= round($data['rAvg'],	4);

		$d		= $data['DayOfYear'];
		$year	= substr($d, 0, 4);
		$day	= substr($d, 4);
		
		$date	= mktime(0, 0, 0, 1, 0, $year) + $day*60*60*24;
		
		$date	= date('Y-m-d', $date);
		$rMax[$d]	= array($date, $r1);
		$rMin[$d]	= array($date, $r2);
		$rAvg[$d]	= array($date, $r3);
	}

	m('script:plot');
	$json	= array(
		'days'	=> count($days),
		'max'	=> array_values($rMax),
		'min'	=> array_values($rMin),
		'avg'	=> array_values($rAvg)
	);
?>
<div id="renderByDays" style="height:500px" rel="{$json|json}"></div>

<script type="text/javascript" src="script/jqPlot/plugins/jqplot.highlighter.min.js"></script>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.dateAxisRenderer.min.js"></script>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.pointLabels.min.js"></script>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.cursor.min.js"></script>
<script src="script/jq.stst.js"></script>
<link rel="stylesheet" type="text/css" href="css/jq.stst.css">

<? } ?>
