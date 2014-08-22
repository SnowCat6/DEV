<?
function module_layout(&$val, &$data)
{
	$fn	= getFn("layout_$val");
	return $fn?$fn($val, $data):NULL;
}
function layout_tools($fn, &$data)
{
	if (!hasAccessRole('developer')) return;
	$data['Изменить стиль страницы#ajax_layout']	= getURL('layout_admin');
}
function layout_render(&$val, &$content)
{
	if (getSiteFile('userStyle.css'))
		m('page:style', 'userStyle.css');
}
?>