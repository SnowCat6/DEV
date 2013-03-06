<?
function admin_panel_log(&$data){
	if (!hasAccessRole('developer')) return;
	$eceuteTime = round(getmicrotime() - sessionTimeStart, 2);
?>
	{$eceuteTime} сек.
    <pre>{{page:display:log}}</pre>
<? return 'Лог исполнения'; } ?>