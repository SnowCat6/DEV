<? function stat_month(&$db, &$data)
{
	if (!hasAccessRole('admin,developer,SEO,writer')) return;

	$max	= 30;
	$days	= array();
	$views	= array();
	
	$search		= array();
	
	$date1		= time()-60*60*24*$max;
	$date2		= time();
	$search['date']	= "$date1-$date2";
	
	$sql	= implode(' AND ', stat2sql($search));
	if ($sql) $sql = " WHERE $sql ";
	
	$table	= $db->table();
	$db->exec("SELECT COUNT(*) as `c`, `DayOfYear` FROM (SELECT DAYOFYEAR(`date`) as `DayOfYear` FROM $table$sql GROUP BY `DayOfYear`, `userIP`) AS `q` GROUP BY `DayOfYear`");
	
	for($day = date('z') - $max; $day <= date('z'); ++$day)
	{
		$date	= mktime(0, 0, 0, 1, 0) + $day*60*60*24;
		$date	= date('Y-m-d', $date);
		$days[$day]	= array($date, 0);
		$views[$day]= array($date, 0);
	}
	while($data = $db->next())
	{
		$day	= $data['DayOfYear'];
		$date	= mktime(0, 0, 0, 1, 0) + $day*60*60*24;
		$date	= date('Y-m-d', $date);
		$days[$day]	= array($date, (int)$data['c']);;
	}
	
	$db->exec("SELECT count(*) AS `c`, DAYOFYEAR(`date`) as `DayOfYear` FROM $table$sql GROUP BY `DayOfYear`");
	while($data = $db->next())
	{
		$day	= $data['DayOfYear'];
		$date	= mktime(0, 0, 0, 1, 0) + $day*60*60*24;
		$date	= date('Y-m-d', $date);
		$views[$day]	= array($date, (int)$data['c']);
	}

	m('script:plot');
	$json	= array(
		'days'	=> count($days),
		'users'	=> array_values($days),
		'views'	=> array_values($views)
	);
?>
<div id="visitorsByDays" rel="{$json|json}"></div>

<script type="text/javascript" src="script/jqPlot/plugins/jqplot.highlighter.min.js"></script>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.dateAxisRenderer.min.js"></script>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.pointLabels.min.js"></script>
<script src="script/jq.stst.js"></script>
<link rel="stylesheet" type="text/css" href="css/jq.stst.css">
<? } ?>
