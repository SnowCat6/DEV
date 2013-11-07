﻿<?
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
	
	$d				= array();
	$d['user_id']	= userID();
	$d['userIP']	= userIP();
	$d['date']		= makeSQLDate(time());
	$d['renderTime']= getmicrotime() - sessionTimeStart;
	$d['url']		= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$d['referer']	= $_SERVER['HTTP_REFERER'];
	$d['browser']	= $_SERVER['HTTP_USER_AGENT'];
	
	foreach($d as $name => &$val) makeSQLValue($val);
	$db->insertRow($db->table, $d, true);
}
function stat_tools($db, &$data){
	if (!hasAccessRole('admin,developer,SEO,writer')) return;
	$data['Статистика#ajax']	= getURL('site_stat');
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
	if ($val = $search['date']){
		list($n1, $n2) = explode('-', $val);
		$n1	= (int)$n1; if ($n1 <= 0)	$n1 = 0;
		$n2	= (int)$n2; if ($n2 < $n1)	$n2 = $n1;
		
		$n1	= mktime(0, 0, 0, date('n', $n1), date('j', $n1), date('Y', $n1));
		$n2	= mktime(0, 0, 0, date('n', $n2), date('j', $n2), date('Y', $n2))+60*60*24;
		
		$n1		= makeSQLDate($n1);
		$n2		= makeSQLDate($n2);
		$sql[]	= "`date` BETWEEN $n1 AND $n2";
	}
}
?>