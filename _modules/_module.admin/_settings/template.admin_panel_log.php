<?
function admin_panel_log(&$data){
	if (!hasAccessRole('developer')) return;
	$eceuteTime	= round(getmicrotime() - sessionTimeStart, 2);
	$memUse		= function_exists('memory_get_peak_usage')?memory_get_peak_usage():'';
	$memUse		= round($memUse / (1024*1024), 2);
	if ($memUse) $memUse = "Выделеная память: <b>$memUse</b> Mb.";
?>
Время выполнения: <b>{$eceuteTime}</b> сек. {!$memUse}
<div id="logTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#logMessages">Лог исполнения</a></li>
    <li class="ui-corner-top"><a href="#logTrace">Трассировка</a></li>
    <li class="ui-corner-top"><a href="#logSQL">SQL</a></li>
</ul>

<div id="logMessages" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<pre>{{page:display:log}}</pre>
</div>

<div id="logTrace" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<pre>{{page:display:logTrace}}</pre>
</div>

<div id="logSQL" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<pre>{{page:display:logSQL}}</pre>
</div>

</div>

<script>
$(function() {
	$("#logTabs").tabs();
});
</script>

<? return 'Лог исполнения'; } ?>