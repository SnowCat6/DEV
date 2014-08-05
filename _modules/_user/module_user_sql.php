<?
function user2sql($search){
	$sql = array();
	user_sql($sql, $search);
	return $sql;
}
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
?>
