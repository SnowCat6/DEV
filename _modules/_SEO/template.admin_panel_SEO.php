<?
//	+function admin_panel_SEO
function admin_panel_SEO()
{
	if (!access('write', 'admin:SEO')) return;
	return array(
		'name'	=> 'SEO',
		'URL'	=> getURL('admin_SEO', array(
			'SEO_DOC' => currentPage()
			)
		)
	);
};

//	+function admin_SEO
function admin_SEO()
{
	if (!access('write', 'admin:SEO')) return;
	if (testValue('SEO_UPDATE')) module('admin:tabUpdate:site_SEO', array());
?>

<form action="{{getURL:admin_SEO}}" method="post" class="admin ajaxForm ajaxReload">
<input type="hidden" name="SEO_UPDATE" />
<? module('admin:tab:site_SEO', array())?>
</form>

<? return ; } ?>
