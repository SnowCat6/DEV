<? function admin_toolbar()
{
	if (defined('admin_toolbar')) return;
	define('admin_toolbar', true);
	
	if (!access('use', 'adminPanel')) return;
	module('admin:tabUpdate:admin_panel');
?>
{{script:jq_ui}}
{{script:ajaxLink}}

<link rel="stylesheet" type="text/css" href="css/admin.css"/>
<link rel="stylesheet" type="text/css" href="../../_templates/baseStyle.css"/>
<div class="adminToolbar"></div>
<div class="adminHover">
<div class="adminPanel">Панель управления сайтом</div>
<div class="adminTools adminForm">
	<div style="padding:0 0 30px 50px; margin-left:-50px;">
        {{admin:tab:admin_panel}}
    </div>
</div>
</div>
<div class="adminSpace"></div>
<? } ?>

