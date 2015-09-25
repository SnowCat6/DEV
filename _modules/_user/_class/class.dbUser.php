<?
class user
{
	static function db()
	{
		$db 		= new dbRow('users_tbl', 'user_id');
		$db->sql	= '`deleted` = 0 AND `visible` = 1';
		$db->images = images.'/users/user';
		$db->url 	= 'user';

		$db2		= new dbRow('login_tbl', 'login_id');
		$db->dbLogin= $db2;
		
		return $db;
	}
	
	static function get($userID)
	{
		$db	= self::db();
		$db->sql = '';
		return $db->openID($userID);
	}

	static function find($search)
	{
		$db	= self::db();
		$sql= array();
		self::sql($sql, $search);
		$db->open($sql);
		return $db;
	}
	
	static function sql(&$sql, $search)
	{
		$path	= array();
		$db		= self::db();
	
		if (isset($search['id']))
		{
			$val	= $search['id'];
			$val	= makeIDS($val);
			
			if ($val) $sql[]	= "`user_id` IN ($val)";
			else $sql[] = 'false';
		}
		
		if (isset($search['password']))
		{
			$md5	= self::loginKey($search['login'], $search['password']);
			$search['md5']	= $md5;
		}else
		if (isset($search['login']))
		{
			$val	= $search['login'];
			$val	= $db->escape_string($val);
			$sql[]	= "`login` LIKE ('$val')";
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
	static function loginKey($login, $passw){
		$l = strtolower($login);
		return md5("$l:$passw");
	}
};

?>