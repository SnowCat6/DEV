<?
//	+function module_adminSEO
function module_adminSEO(){
	return admin_panel_SEO();
}
//	+function admin_panel_SEO
function admin_panel_SEO()
{
	if (!access('write', 'admin:SEO')) return;
?>

<form action="{{getURL:admin_SEO}}" method="post" class="admin ajaxFormNow ajaxReload">
<? module('admin:tab:site_SEO', array())?>
</form>

<? return 'SEO'; } ?>