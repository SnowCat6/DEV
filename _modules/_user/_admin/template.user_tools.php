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
<?
//	+function user_tools2
function user_tools2($val, &$menu)
{
	if (!access('use', 'adminPanel')) return;

	if (!$val)
	{
		if (hasAccessRole('edit')){
			$menu['Отключить редактирование#ajax']	= getURL("admin_edit_mode", 'mode=no');
		}else{
			$menu['Включить редактирование#ajax']	= getURL("admin_edit_mode", 'mode=yes');
		}
		return;
	}
	m("page:title", "Режим редактирования");

	$id		= userID();
	$mode	= getValue('mode') == 'yes'?'':'userDdenyEdit';
	setStorage('editMode', $mode, "user$id");
	
	if (hasAccessRole('edit')){
?>
	Режим онлайн редактирования включен
<? }else{ ?>
	Режим онлайн редактирования отключен
<? } ?>
<? } ?>