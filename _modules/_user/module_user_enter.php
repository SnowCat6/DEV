<?
function user_enter($db, $val, &$data)
{
	if (testValue('logout')) user_logout();

	$login = $data?$data:getValue('login');
	//	Проверить регистрацию, если введен логин пользователя
	if (is_array($login))
	{	//	Если пользователь регистрируется
		$db->open(user2sql(array('password' => "$login[login]:$login[passw]")));
		//	Проверка что такой пользователь есть
		//	Если пользователь найден, то регистрация
		if ($data = $db->next()){
			logData("user: \"$data[login]\" entered", 'user');
			define('firstEnter', true);
			return setUserData($db, $login['remember']);
		}
		if ($val) return false;

		user_logout();
		module('message:error', 'Неверный логин или пароль');

		return false;
	}
	
	$md5 = $_COOKIE['userSession5'];
	if ($md5){	//	Если пользователь в сессии, то ищем его в базе
		$db->open(user2sql(array('md5' => $md5)));
		//	Проверка что такой пользователь есть
		if($db->next()){
			//	Если хешь совпадает, то регистрируем пользователя
			return setUserData($db);
		}
	}
	
	//	Если происходит авторегистрация
	$md5 = @$_COOKIE['autologin5'];
	if ($md5){	//	Если пользователь с запоминанием, то ищем его в базе
		$db->open(user2sql(array('md5' => $md5)));
		//	Проверка что такой пользователь есть
		if ($data = $db->next()){
			logData("user: \"$data[login]\" entered", 'user');
			//	Если хешь совпадает, то регистрируем пользователя
			return setUserData($db);
		}
	}
	//	Сбрасываем авторегистрацию
	if (!$val) user_logout();

	return false;
}
function user_checkLogin($db, $val, $login)
{
	$md5	= getMD5($login['login'], $login['passw']);
	$db->open(user2sql(array('md5' => $md5)));
	return $db->next() != NULL;
}

function user_logout()
{
	if (userID()) logData("user: \"$data[login]\" logout", 'user');
//	module('message:user:trace', "User logout from site");
	cookieSet('userSession5',	'');
	cookieSet('autologin5',		'');
}

//	Регистрация пользователя, установка ACL и прочего
function setUserData(&$db, $remember = false)
{
	$data 	= $db->rowCompact();	//	Получить данные
	$userID = $db->id();			//	Запомнить код
	@define('user', $data['access']);//	Определить уровень доступа
	if ($remember){
		cookieSet('autologin5', $data['md5']);
	}else cookieSet('userSession5', $data['md5'], false);
	
	//	Сохранить данные текущего пользователя
	define('userID', $userID);
	
	$roles			= array();
	$accessRoles	= explode(',', $data['access']);
	foreach($accessRoles as $accessRole){
		if ($accessRole) $roles[$accessRole] = $accessRole;
	}
	
	global $_CONFIG;
	$_CONFIG['user']['data']		= $data;
	$_CONFIG['user']['userRoles']	= $roles;

//	module('message:user:trace', "User '$data[login]' entered in site");
	return $userID;
}

function getMD5($login, $passw){
	$l = strtolower($login);
	return md5("$l:$passw");
}

?>