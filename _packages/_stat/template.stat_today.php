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
?>
<div id="visitorsByHours"></div>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.highlighter.min.js"></script>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.barRenderer.min.js"></script>
<script type="text/javascript" src="script/jqPlot/plugins/jqplot.pointLabels.min.js"></script>
<script>
var hours = <?= json_encode($hours)?>;
var hoursNow = <?= json_encode($hoursNow)?>;
$(function(){
	$.jqplot('visitorsByHours', [hours, hoursNow], {
		title:	'Посещаемость за день по часам',
		axes:{
			xaxis:{
				min:0, max:23,
				numberTicks: 24,
				tickOptions:{formatString:'%d час'}
		  	},
			yaxis:{
				min:0,
				tickOptions:{formatString:'%d чел.'}
		  	}
		},
		series:[
			{label:'Вчера <?= date('d.m.y', time() - 60*60*24)?>'},
			{label:'Сегодня <?= date('H:i')?>'}
		],
        legend: {
            show: true
        },
		highlighter:{
			show: true,
			sizeAdjust: 7.5,
			formatString: "%2$d чел./час",
			useAxesFormatters: false
		}
	});
});
</script>
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