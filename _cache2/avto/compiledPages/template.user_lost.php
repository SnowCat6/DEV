<?
function user_lost(&$db, $val, &$data)
{
	if (!is_array(&$data)) return module('message:error', 'Не верный формат данных');

	@$login = $data['login'];
	if (!$login) return module('message:error', 'Введите имя пользователя в строку');
	makeSQLValue($login);

	$db->open("login LIKE $login");
	if (!$db->next()) return module('message:error', 'Пользователь с таким именем не найден');
	
	return true;
}
?>