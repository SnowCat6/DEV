<?
function user_enterSite(&$db, $val, $config)
{
	if (testValue('logout')) user_logout();
	
	$login	= getValue('login');
	if (user_checkLogin($db, $val, $login))
	{
		logData("user: \"$login[login]\" entered", 'user');
		define('firstEnter', true);
		return setUserData($db, $login['remember']);
	}
	
	$md5	= $_COOKIE['userSession5'];
	if ($md5){
		//	Если пользователь в сессии, то ищем его в базе
		$db->open(user2sql(array('md5' => $md5)));
		//	Проверка, что такой пользователь есть
		if($db->next()){
			//	Если хеш совпадает, то регистрируем пользователя
			return setUserData($db);
		}
	}
	
	//	Если происходит авторегистрация
	$md5	= $_COOKIE['autologin5'];
	if ($md5){
		//	Если пользователь с запоминанием, то ищем его в базе
		$db->open(user2sql(array('md5' => $md5)));
		//	Проверка что такой пользователь есть
		if ($data = $db->next()){
			logData("user: \"$data[login]\" entered", 'user');
			//	Если хешь совпадает, то регистрируем пользователя
			return setUserData($db);
		}
	}
	//	Сбрасываем авторегистрацию
	user_logout();
	return false;
}
function user_enter(&$db, $val, &$login)
{
	if (user_checkLogin($db, $val, $login))
	{
		logData("user: \"$login[login]\" entered", 'user');
		define('firstEnter', true);
		return setUserData($db, $login['remember']);
	};

	user_logout();
	module('message:error', 'Неверный логин или пароль');

	return false;
}
function user_checkLogin(&$db, $val, $login)
{
	if (!is_array($login)) return;

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


?>