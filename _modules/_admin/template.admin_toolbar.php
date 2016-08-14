<? function admin_toolbar()
{
	if (defined('admin_toolbar')) return;
	define('admin_toolbar', true);
	
	if (!access('use', 'adminPanel')) return;
	module('admin:tabUpdate:admin_panel');
?>
<link rel="stylesheet" type="text/css" href="css/admin.css"/>
<link rel="stylesheet" type="text/css" href="../../_templates/baseStyle.css"/>

<div class="adminToolbar"></div>
{push}
<div class="adminHover">
    <div class="adminPanel">Панель управления сайтом</div>
    <div class="adminTools adminForm" style="margin:0 auto; max-width:1100px;">
        <div style="padding:0 10px 5px 10px">
            <div style=" box-shadow:0 0 30px #000">
                <module:script:jq_ui />
                <module:script:ajaxLink />
                <module:admin:tab:admin_panel />
            </div>
        </div>
    </div>
</div>
{pop:adminPanel}
<div class="adminSpace"></div>

<? } ?>

