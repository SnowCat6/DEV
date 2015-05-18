<?
function user_tools($db, $val, &$menu)
{
	if (!hasAccessRole('admin,developer,accountManager')) return;
	
	$menu['Пользователи#ajax']	= getURL("user_all");
}
?>