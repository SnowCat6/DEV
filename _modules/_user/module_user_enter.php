<?
function user_enterSite($val, $config)
{
	if (testValue('logout')) user_logout();
	
	$login	= getValue('login');
	$db		= user_checkLogin($val, $login);
	if ($db)
	{
		undo::addLog("User \"$login[login]\" entered", 'user');
		define('firstEnter', true);
		return setUserData($db, $login['remember']);
	}
	
	$md5	= $_COOKIE['userSession5'];
	if ($md5){
		//	Если пользователь в сессии, то ищем его в базе
		$db	= user::find(array('md5' => $md5));
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
		$db	= user::find(array('md5' => $md5));
		//	Проверка что такой пользователь есть
		if ($data = $db->next()){
			undo::addLog("User \"$data[login]\" entered", 'user');
			//	Если хешь совпадает, то регистрируем пользователя
			return setUserData($db);
		}
	}
	//	Сбрасываем авторегистрацию
	user_logout();
	return false;
}
function user_enter($val, $login)
{
	$db	= user_checkLogin($val, $login);
	if ($db)
	{
		undo::addLog("User \"$login[login]\" entered", 'user');
		define('firstEnter', true);
		return setUserData($db, $login['remember']);
	};

	user_logout();
	module('message:error', 'Неверный логин или пароль');

	return false;
}
function user_checkLogin($val, $login)
{
	if (!is_array($login)) return;

	$db		= user::find(array('login' => $login['login'], 'password' => $login['passw']));
	if ($db->next() != NULL) return $db;
}

function user_logout()
{
	if (userID()) undo::addLog("User \"$data[login]\" logout", 'user');
	config::set(':USER', NULL);
//	module('message:user:trace', "User logout from site");
	cookieSet('userSession5',	'');
	cookieSet('autologin5',		'');
}


?>