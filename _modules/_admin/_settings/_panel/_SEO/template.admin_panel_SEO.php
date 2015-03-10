<?
//	+function admin_SEO
function admin_SEO(){
	return admin_panel_SEO();
}
//	+function admin_panel_SEO
function admin_panel_SEO()
{
	if (!access('write', 'admin:SEO')) return;
?>

<form action="{{getURL:admin_SEO}}" method="post" class="admin">
<? module('admin:tab:site_SEO', array())?>
</form>

<? return 'SEO'; } ?>