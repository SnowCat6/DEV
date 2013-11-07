﻿<?
addUrl('site_stat',		'stat:report');

addEvent('site.exit',			'stat:add');
addEvent('admin.tools.service',	'stat:tools');

addEvent('config.end',	'stat_config');
function module_stat_config($val, $data)
{
	$stat_tbl = array();
	$stat_tbl['stat_id']= array('Type'=>'int(11) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$stat_tbl['user_id']= array('Type'=>'int(10) unsigned', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$stat_tbl['userIP']= array('Type'=>'int(4) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$stat_tbl['date']= array('Type'=>'datetime', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$stat_tbl['url']= array('Type'=>'varchar(255)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$stat_tbl['browser']= array('Type'=>'varchar(255)', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$stat_tbl['referer']= array('Type'=>'varchar(255)', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$stat_tbl['renderTime']= array('Type'=>'float unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	dbAlterTable('stat_tbl', $stat_tbl, true, 'MyISAM');
}
?>