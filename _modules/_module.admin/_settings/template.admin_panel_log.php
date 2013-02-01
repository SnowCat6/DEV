<?
function admin_panel_log(&$data){
	if (!access('read', 'log')) return;
?>
	{{debug:executeTime}}
    <pre>{{page:display:log}}</pre>
<? return 'Лог исполнения'; } ?>