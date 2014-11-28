<?
//	module user
function module_user($fn, &$data)
{
	//	База данных пользователей
	$db 		= new dbRow('users_tbl', 'user_id');
	$db->sql	= '`deleted` = 0 AND `visible` = 1';
	$db->images = images.'/users/user';
	$db->url 	= 'user';
	
	$db2		= new dbRow('login_tbl', 'login_id');
	$db->dbLogin= $db2;

	if (!$fn){
		$db->data = $data;
		return $db;
	}
	
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("user_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}
//	Проверка прав доступа
function module_user_adminAccess(&$val, &$data)
{
	$id = (int)$data[1];
	return $id == userID() || hasAccessRole('admin,developer,accountManager');
}
//	Проверить путь к файлу на предмет возможной записи
function module_user_file_access(&$mode, &$data)
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
function module_user_access(&$val, &$data)
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
	}
	if ($data[0]) return false;
	return hasAccessRole('admin,developer,writer,manager,accountManager');
}
//	Проверить наличие роди пользователя в профиле
//	$checkRole - или строка со списком ролей через запятую или массив с ролями
function hasAccessRole($checkRole)
{
	if (!userID()) return false;
	if (!is_array($checkRole)) $checkRole = explode(',', $checkRole);

	global $_CONFIG;
	$userRoles	= &$_CONFIG['user']['userRoles'];
	foreach($checkRole as $accessRole){
		if ($userRoles[$accessRole]) return true;
	}
	return false;
}
//	+function user_storage
function user_storage($db, $mode, &$ev)
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
		$d		= $db->openID($userID);
		if (!$d) return;
		
		$ev['content']	= $d['fields'][':storage'][$name];
		return true;
	}
}
//	Вернуть объект базы данных с выполненным запросом SQL
function user_find($db, $val, &$search){
	$db->open(user2sql($search));
	return $db;
}
function user2sql($search){
	$sql	= array();
	$fn		= getFn("user_sql");
	if ($fn) $fn($sql, $search);
	return $sql;
}
function getMD5($login, $passw){
	$l = strtolower($login);
	return md5("$l:$passw");
}

?>