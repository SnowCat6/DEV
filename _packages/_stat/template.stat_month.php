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
	$db->exec("SELECT COUNT(*) as `c`, `DayOfYear` FROM (SELECT CONCAT(YEAR(`date`), DAYOFYEAR(`date`)) as `DayOfYear` FROM $table$sql GROUP BY `DayOfYear`, `userIP`) AS `q` GROUP BY `DayOfYear`");
	
	for($d = $date1; $d <= $date2; $d += 60*60*24)
	{
		$year	= substr($d, 0, 4);
		$day	= substr($d, 4);

		$date	= $d;
		$date	= date('Y-m-d', $date);
		
		$d2		= date('Yz', $d);
		$days[$d2]	= array($date, 0);
		$views[$d2]= array($date, 0);
	}

	while($data = $db->next())
	{
		$d		= $data['DayOfYear'];
		$year	= substr($d, 0, 4);
		$day	= substr($d, 4);
		$date	= mktime(0, 0, 0, 1, 0, $year) + $day*60*60*24;
		$date	= date('Y-m-d', $date);
		$days[$d]	= array($date, (int)$data['c']);;
	}
	
	$db->exec("SELECT count(*) AS `c`, CONCAT(YEAR(`date`), DAYOFYEAR(`date`)) as `DayOfYear` FROM $table$sql GROUP BY `DayOfYear`");
	while($data = $db->next())
	{
		$d		= $data['DayOfYear'];
		$year	= substr($d, 0, 4);
		$day	= substr($d, 4);
		$date	= mktime(0, 0, 0, 1, 0, $year) + $day*60*60*24;
		$date	= date('Y-m-d', $date);
		$views[$d]	= array($date, (int)$data['c']);
	}
//	print_r($views); die;

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
