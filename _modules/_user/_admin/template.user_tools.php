<?
function user_tools($val, &$menu)
{
	if (hasAccessRole('admin,developer,accountManager'))
	{
		$menu[':user']	= array(
			'Пользователи#ajax'	=> getURL("user_all"),
			'[выход]'			=> getURL("#", 'logout')
		);
	}else
	if ($id = userID())
	{
		$menu[':user']	= array(
			'Персональные настройки#ajax'	=> getURL("user_edit_$id"),
			'[выход]'						=> getURL("#", 'logout')
		);
	}
}
?>