<?
//	+function admin_updateMakerTools
function admin_updateMakerTools($val, &$menu)
{
	if (!hasAccessRole('developer')) return;

	$menu['Создать обновление#ajax']	= getURL('admin_updateMaker');
}
?>