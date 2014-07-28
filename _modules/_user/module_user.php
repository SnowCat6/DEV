<?
module('user:enter');
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
function hasAccessRole($checkRole)
{
	if (!userID()) return false;
	
	$userRoles	= $GLOBALS['_CONFIG']['user']['userRoles'];
	if (!is_array($checkRole))
		$checkRole = explode(',', $checkRole);
	
	return count(array_intersect($userRoles, $checkRole)) > 0;
}
function user_find($db, $val, &$search){
	$db->open(user2sql($search));
	return $db;
}
?>