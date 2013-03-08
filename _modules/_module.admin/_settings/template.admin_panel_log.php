<?
function admin_panel_log(&$data){
	if (!hasAccessRole('developer')) return;
	$eceuteTime = round(getmicrotime() - sessionTimeStart, 2);
?>
	{$eceuteTime} сек.
<div id="logTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#logMessages">Лог исполнения</a></li>
    <li class="ui-corner-top"><a href="#logTrace">Трассировка</a></li>
    <li class="ui-corner-top"><a href="#logSQL">SQL</a></li>
<!--    <li class="ui-corner-top"><a href="#logTime">Time trace</a></li>-->
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

<!--<div id="logTime" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<pre><?= implode("\r\n", $GLOBALS['_CONFIG']['checkTime']) ?></pre>
</div>-->
</div>

<script>
$(function() {
	$("#logTabs").tabs();
});
</script>

<? return 'Лог исполнения'; } ?>