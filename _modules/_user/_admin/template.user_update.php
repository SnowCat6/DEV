<?
//	user:update::add
//	user:update:20:delete
//	user:update:20:edit
function user_update(&$db, $id, &$data)
{
	$db->sql = '';
	list($id, $action) = explode(':', $id, 2);

	$id = (int)$id;
	if ($id){
		$userData = $db->openID($id);
		if (!$userData) return module('message:error', 'Нет пользователя');
	}else $userData = NULL;
	
	if ($action == 'delete')
	{
		if (!hasAccessRole('admin,developer,accountManager')) return module('message:error', 'Нет прав доступа на удаление');
		if (userID() == $id) return module('message:error', 'Вы не можете удалить свой аккаунт');
		
		$db->delete($id);
		module('message', 'Пользователь удален');
		return $id;
	}

	$d = array();
	if (isset($data['login'])){
		$d['login']	= $data['login'];
		if (isset($data['passw'])) $d['passw']	= $data['passw'];
	}
	
	if (hasAccessRole('admin,developer,accountManager')){
		if (isset($data['access']))	$d['access']	= $data['access'];
	}
	
	if ($userData && isset($data['fields']) && is_array($data['fields']))
	{
		if (!is_array($userData['fields'])) $userData['fields'] = array();

		$fields	= $data['fields'];
		dataMerge($fields, $userData['fields']);
		$d['fields'] = $fields;
	}else{
		if (isset($data['fields']) && is_array($data['fields'])){
			$d['fields'] = $data['fields'];
		}
	}

	switch($action){
		case 'register':
			//	Пользовательская обработка данных
			$base = array(&$d, &$data, &$error);
			event("user.update:$action", $base);
			if ($error) return module('message:error', $error);

			if (!access('register', ''))
				return module('message:error', 'Регистрация запрещена');;

			if (!@$d['login'])
				return module('message:error', 'Введите логин пользователя');
			if (!@$d['passw'])
				return module('message:error', 'Введите пароль пользователя');

			if (getUserByLogin($d['login']))
				return module('message:error', 'Пользователь существует, введите другой логин');

			@$d['md5'] 		= getMD5($d['login'], $data['passw']);
			$d['passw']		= ''; unset($d['passw']);
			
			$d['access']	= 'user';
			$d['dateCreate']= time();
			$iid			= $db->update($d);
			if (!$iid) 	return module('message:error', 'Ошибка добавления в базу данных');
			//	Получить пути к файлам, сарый и новый
			$oldPath	= $ddb->folder();
			$newPath	= $ddb->folder($iid);
			//	Переместить все файлы в новую папку
			@rename($oldPath, $newPath);
		break;
		case 'add':
			//	Пользовательская обработка данных
			$base = array(&$d, &$data, &$error);
			event("user.update:$action", $base);
			if ($error) return module('message:error', $error);

			if (!hasAccessRole('admin,developer,accountManager'))
				return module('message:error', 'Недостаточно прав');
			if (!@$d['login'])
				return module('message:error', 'Введите логин пользователя');
			if (getUserByLogin($d['login']))
				return module('message:error', 'Пользователь существует, введите другой логин');
			
			@$d['md5'] 		= getMD5($d['login'], $d['passw']);
			$d['passw']		= ''; unset($d['passw']);
			
			$d['dateCreate']= time();
			$iid			= $db->update($d);
			if (!$iid){
				$error = $db->error();
				return module('message:error', "Ошибка добавления в базу данных, $error");
			}
			//	Получить пути к файлам, сарый и новый
			$ddb		= module('user');
			$oldPath	= $ddb->folder();
			$newPath	= $ddb->folder($iid);
			//	Переместить все файлы в новую папку
			@rename($oldPath, $newPath);
		break;
		case 'edit':
			//	Пользовательская обработка данных
			$base = array(&$d, &$data, &$error);
			event("user.update:$action", $base);
			if ($error) return module('message:error', $error);

			if (!hasAccessRole('admin,developer,accountManager') && userID() != $id)
				return module('message:error', 'Недостаточно прав');
				
			if (isset($d['login']))
			{
				if (!$d['login']) 			return module('message:error', 'Введите логин пользователя');
				if (!isset($d['passw']))	return module('message:error', 'Введите пароль');
				
				$existUserID	= getUserByLogin($d['login']);
				if ($existUserID && $existUserID != $id)
					return module('message:error', 'Пользователь существует, введите другой логин');

				$d['md5'] = getMD5($d['login'], $d['passw']);
				unset($d['passw']);
			}
				
			$d['id']	= $id;
			$iid		= $db->update($d);
			if (!$iid){
				$error = $db->error();
				return module('message:error', "Ошибка добавления в базу данных: $error");
			}
			
			if ($iid == userID()){
				$db->openID($iid);
				setUserData($db);
			}
		break;
		default:
			return module('message:error', 'Неизвестная команда');
	}
	return $iid;
}
function getUserByLogin($login)
{
	$db		= module('user');
	$login	= dbEncString($db, $login);
	$db->open("login LIKE $login");
	return $db->next()?$db->id():0;
}
?>