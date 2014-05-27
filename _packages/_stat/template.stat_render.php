<? function stat_render(&$db, &$data)
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
	$db->exec("SELECT * FROM (SELECT max(renderTime) rMax, min(renderTime) rMin, avg(renderTime) as rAvg, DAYOFYEAR(`date`) as `DayOfYear` FROM $table$sql GROUP BY `DayOfYear`) AS `q` GROUP BY `DayOfYear`");
	
	for($day = date('z') - $max; $day <= date('z'); ++$day)
	{
		$date	= mktime(0, 0, 0, 1, 0) + $day*60*60*24;
		$date	= date('Y-m-d', $date);
		$rMax[$day]	= "['$date', 0]";
		$rMin[$day]	= "['$date', 0]";
		$rAgv[$day]	= "['$date', 0]";
	}
	while($data = $db->next())
	{
		$r1	= round($data['rMax'],	4);
		$r2	= round($data['rMin'],	4);
		$r3	= round($data['rAvg'],	4);

		$day	= $data['DayOfYear'];
		$date	= mktime(0, 0, 0, 1, 0) + $day*60*60*24;
		$date	= date('Y-m-d', $date);
		$rMax[$day]	= "['$date', $r1]";
		$rMin[$day]	= "['$date', $r2]";
		$rAvg[$day]	= "['$date', $r3]";
	}

	m('script:plot');
?>
<div id="renderByDays" style="height:500px"></div>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.highlighter.min.js"></script>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.dateAxisRenderer.min.js"></script>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.pointLabels.min.js"></script>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.cursor.min.js"></script>
<script>
var rMax = [<?= implode(',',	$rMax)?>];
var rMin = [<?= implode(',',	$rMin)?>];
var rAvg = [<?= implode(',',	$rAvg)?>];

$(function(){
	$.jqplot('renderByDays', [rMax, rMin, rAvg], {
		title:	'Время выполнения {$max} дней',
		cursor: {
			show: true,
			tooltipLocation:'sw', 
			zoom:true
		},
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
					formatString:'%f сек.'
          		}
		  	}
		},
		series:[
			{	label:'Максимум',
				highlighter:{formatString: "%2$.4f сек./стр."}
			},
			{	label:'Минимум',
				highlighter:{formatString: "%2$.4f сек./стр."}
			},
			{	label:'Среднее',
				highlighter:{formatString: "%2$.4f сек./стр."}
			}
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
