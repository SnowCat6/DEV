<? function admin_toolbar(){?>
{{script:jq_ui}}
<link rel="stylesheet" type="text/css" href="admin.css"/>
<div class="adminToolbar adminForm">
	<div class="adminPanel"><a href="#">Панель управления сайтом</a></div>
	<div class="adminWindow">
        {{admin:tab:admin_panel}}
    </div>
</div>
<? } ?>

