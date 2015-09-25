<?
//	module user
function module_user($fn, &$data)
{
	if (!$fn)	return user::db();
	
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("user_$fn");
	return $fn?$fn($val, $data):NULL;
}
function userID($data = NULL)
{
	if ($data) return (int)$data['user_id'];
	$user	= config::get(':USER');
	return (int)$user['id'];
}
//	Проверка прав доступа
function module_user_adminAccess($val, $data)
{
	$id = (int)$data[1];
	return $id == userID() || hasAccessRole('admin,developer,accountManager');
}
//	Проверить путь к файлу на предмет возможной записи
function module_user_file_access($mode, $data)
{
	//	Если это новый документ и идентификатор пользователя совпадает, то дать доступ
	if (preg_match('#new(\d+)#', $data[1], $var)){
		if (userID() == $var[1]) return true;
	}
	//	Проверить стандартные права
	$id	= (int)$data[1];
	return access($mode, "user:$id");
}

//	Проверка прав доступа
function module_user_access($val, $data)
{
	list($mode,) = explode(':', $val);
	switch($mode){
	case 'register':
		$ini			= getCacheValue('ini');
		$denyRegister	= $ini[':user']['denyRegisterNew'];
		return $denyRegister != 1;
	case 'use':
		if ($data[0] != 'adminPanel') return;
		return hasAccessRole('admin,developer,writer,manager,accountManager,SEO');
	case 'clearCache':
		return hasAccessRole('admin,developer');
	}
	if ($data[0]) return false;
	return hasAccessRole('admin,developer,writer,manager,accountManager');
}
//	Проверить наличие роди пользователя в профиле
//	$checkRole - или строка со списком ролей через запятую или массив с ролями
function hasAccessRole($checkRole)
{
	if (!is_array($checkRole))
		$checkRole = explode(',', $checkRole);

	$user		= config::get(':USER');
	$userRoles	= $user['userRoles'];
	foreach($checkRole as $accessRole){
		if ($userRoles[$accessRole]) return true;
	}
	return false;
}
//	+function user_storage
function user_storage($mode, &$ev)
{
	$id		= $ev['id'];
	$name	= $ev['name'];
	
	if (strncmp($id, 'user', 4)) return;
	$userID	= (int)substr($id, 4);
	
	switch($mode){
	case 'set':
		$d	= array();
		$d['fields'][':storage'][$name]	= $ev['content'];
		$bOK=  m("user:update:$userID:edit", $d) != 0;
		return $bOK;
	case 'get':
		$db	= user::find(array('id' => $userID));
		$d	= $db->next();
		if (!$d) return;
		
		$ev['content']	= $d['fields'][':storage'][$name];
		return true;
	}
}
//	Вернуть объект базы данных с выполненным запросом SQL
function user_find($val, &$search){
	return user::find($search);
}
function user2sql($search)
{
	$sql= array();
	user::sql($sql, $search);
	return $sql;
}
function getMD5($login, $passw){
	return user::loginKey($login. $passw);
}
//	Регистрация пользователя, установка ACL и прочего
function setUserData($db, $remember = false)
{
	$userID = $db->id();			//	Запомнить код
	$data 	= $db->rowCompact();	//	Получить данные
	
	if ($remember){
		cookieSet('autologin5', $data['md5']);
	}else cookieSet('userSession5', $data['md5'], false);
	
	$roles			= array();
	$accessRoles	= explode(',', $data['access']);
	foreach($accessRoles as $accessRole){
		if ($accessRole) $roles[$accessRole] = $accessRole;
	}
	
	$user				= array();
	$user['id']			= $userID;
	$user['access']		= $data['access'];
	$user['data']		= $data;
	$user['userRoles']	= $roles;
	config::set(':USER', $user);
	
//	module('message:user:trace', "User '$data[login]' entered in site");
	return $userID;
}
?>