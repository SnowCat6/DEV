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
	<div style="padding:0 50px 30px 50px; margin:0 auto; max-width:1000px;">
    	<div style=" box-shadow:0 0 30px #000">
	        {{admin:tab:admin_panel}}
        </div>
    </div>
</div>
</div>
<div class="adminSpace"></div>
<? } ?>

