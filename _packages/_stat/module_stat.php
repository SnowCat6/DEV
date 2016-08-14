<?
function module_stat(&$val, &$data)
{
	$db	= new dbRow('stat_tbl', 'stat_id');
	if (!$val) return $db;
	
	$fn	= getFn("stat_$val");
	if ($fn) $fn($db, $data);
	define('statPages', true);
}
function stat_add(&$db, &$config)
{
	if (defined('statPages')) return;
	if (hasAccessRole('admin,developer,editor,manager')) return;
//	if (testValue('ajax')) return;
	
	$d				= array();
	$d['user_id']	= userID();
	$d['userIP']	= userIP();
	$d['date']		= time();
	$d['renderTime']= getmicrotime() - sessionTimeStart;
	$d['url']		= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$d['referer']	= $_SERVER['HTTP_REFERER'];
	$d['browser']	= $_SERVER['HTTP_USER_AGENT'];
	
//	foreach($d as $name => &$val) makeSQLValue($val);
	dbEncode($db, $db->dbFields, $d);
	$db->insertRow($db->table, $d, true);
}
function stat_tools($db, &$menu)
{
	if (!hasAccessRole('admin,developer,SEO,writer')) return;

	$menu[':stat']	= array(
		'Статистика#ajax'	=> getURL('site_stat'),
		'[Очистить]#ajax'	=> getURL('site_stat_clean')
	);
}
function stat_clean($db, $data)
{
	$date	= time()-60*60*24*31;
	$date	= dbEncDate($db, $date);

	$table	= $db->table();
	$sql	= "DELETE FROM $table WHERE `date` < $date";
	$db->exec($sql);

	echo "Статистика за предыдущие месяцы удалена";
}

function stat2sql($search){
	$sql	= array();
	stat_sql($sql, $search);
	return $sql;
}
function stat_sql(&$sql, &$search)
{
	//	Выборка за день unixtime-unixtime
	//	Округление по дням
	if ($val = $search['date'])
	{
		list($n1, $n2) = explode('-', $val);
		$n1	= (int)$n1; if ($n1 <= 0)	$n1 = 0;
		$n2	= (int)$n2; if ($n2 < $n1)	$n2 = $n1;
		
		$n1	= mktime(0, 0, 0, date('n', $n1), date('j', $n1), date('Y', $n1));
		$n2	= mktime(0, 0, 0, date('n', $n2), date('j', $n2), date('Y', $n2))+60*60*24;
		
		$db		= new dbRow();
		$n1		= dbEncDate($db, $n1);
		$n2		= dbEncDate($db, $n2);
		$sql[]	= "`date` BETWEEN $n1 AND $n2";
	}
}
?>