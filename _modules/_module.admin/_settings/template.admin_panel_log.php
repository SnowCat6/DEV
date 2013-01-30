<? function admin_panel_log(&$data){ ?>
	{{debug:executeTime}}
    <pre>{{page:display:log}}</pre>
<? return 'Лог исполнения'; } ?>