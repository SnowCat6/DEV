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
		$days[$day]	= array($date, $data['c']);;
	}
	
	$db->exec("SELECT count(*) AS `c`, DAYOFYEAR(`date`) as `DayOfYear` FROM $table$sql GROUP BY `DayOfYear`");
	while($data = $db->next())
	{
		$day	= $data['DayOfYear'];
		$date	= mktime(0, 0, 0, 1, 0) + $day*60*60*24;
		$date	= date('Y-m-d', $date);
		$views[$day]	= array($date, $data['c']);
	}

	m('script:plot');
?>
<div id="visitorsByDays"></div>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.highlighter.min.js"></script>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.dateAxisRenderer.min.js"></script>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.pointLabels.min.js"></script>
<script>
var days = <?= json_encode(array_values($days))?>;
var views = <?= json_encode(array_values($views))?>;
$(function(){
	$.jqplot('visitorsByDays', [days, views], {
		title:	'Посещаемость за последние {$max} дней',
		axes:{
			xaxis:{
				renderer: $.jqplot.DateAxisRenderer,
				numberTicks: <?= count($days)?>,
				tickOptions:{
					formatString:'%b %d'
          		} 
		  	},
			yaxis:{
				min:0,
				tickOptions:{
					formatString:'%d чел.'
          		} 
		  	}
		},
		series:[
			{	label:'Посетителей',
				highlighter:{formatString: "%2$d чел./день"}
			},
			{	label:'Просмотров',
				highlighter:{formatString: "%2$d стр./день"}
			},
		],
        legend: {
            show: true
        },
		highlighter:{
			show: true,
			sizeAdjust: 7.5,
			useAxesFormatters: false
		}
	});
});
</script>
<? } ?>
