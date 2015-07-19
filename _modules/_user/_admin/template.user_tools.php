<?
function user_tools($db, $val, &$menu)
{
	if (!hasAccessRole('admin,developer,accountManager')) return;
	
	$menu[':user']	= array(
		'Пользователи#ajax'	=> getURL("user_all"),
		'[выход]'			=> getURL("#", 'logout')
	);
}
?>