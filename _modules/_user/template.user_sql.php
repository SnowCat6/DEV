<?
// +function user_sql
function user_sql(&$sql, &$search)
{
	$db		= module('user');
	$path	= array();

	if (isset($search['id']))
	{
		$val	= $search['id'];
		$val	= makeIDS($val);
		
		if ($val) $sql[]	= "`user_id` IN ($val)";
		else $sql[] = 'false';
	}
	
	if (isset($search['login']))
	{
		$val	= $search['login'];
		$val	= $db->escape_string($val);
		$sql[]	= "`login` LIKE ('$val')";
	}

	if (isset($search['password']))
	{
		list($login, $passw) = explode(':', $search['password'], 2);
		$md5	= getMD5($login, $passw);
		$val	= $db->escape_string($val);
		$sql[]	= "`md5` = '$md5'";
	}
	
	if (isset($search['md5']))
	{
		$val	= $search['md5'];
		$val	= $db->escape_string($val);
		$sql[]	= "`md5` = '$val'";
	}

	$ev = array(&$sql, &$search);
	event('user.sql', $ev);
	
	if (@$sql[':from'] || @$sql[':join']){
		$sql[':from'][] = 'u';
	}

	return $path;
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
?>
