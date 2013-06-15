<? function admin_toolbar(){
	if (!access('use', 'adminPanel')) return;
?>
{{script:jq_ui}}{{script:ajaxLink}}
<link rel="stylesheet" type="text/css" href="admin.css"/>
<link rel="stylesheet" type="text/css" href="../../_templates/baseStyle.css"/>
<div class="adminToolbar"></div>
<div class="adminHover">
<div class="adminPanel">
Панель управления сайтом
</div>
<div class="adminTools adminForm">
	<div style="padding:0 0 30px 50px; margin-left:-50px;">
	{{admin:tab:admin_panel}}
    </div>
</div>
</div>
<? } ?>

