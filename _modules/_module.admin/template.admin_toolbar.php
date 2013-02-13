<? function admin_toolbar(){?>
{{script:jq_ui}}
<link rel="stylesheet" type="text/css" href="admin.css"/>
<div class="adminToolbar"></div>
<div class="adminHover">
<div class="adminPanel">
Панель управления сайтом
<b>док: 0</b>
</div>
<div class="adminTools adminForm">
	<div style="padding:0 0 30px 50px; margin-left:-50px;">
	{{admin:tab:admin_panel}}
    </div>
</div>
</div>
<? } ?>

