<?
function user_lost($val, &$data)
{
	if (!is_array(&$data)) return module('message:error', 'Не верный формат данных');

	@$login = $data['login'];
	if (!$login) return module('message:error', 'Введите имя пользователя в строку');
	
	$db		= user::db();
	$login	= dbEncString($db, $login);

	$db->open("login LIKE $login");
	if (!$db->next()) return module('message:error', 'Пользователь с таким именем не найден');
	
	return true;
}
?>