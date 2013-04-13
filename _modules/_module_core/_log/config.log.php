<?
addEvent('config.end',	'log_config');
function module_log_config($val, $data)
{
	$log_tbl = array();
	$log_tbl['log_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$log_tbl['user_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$log_tbl['userIP']= array('Type'=>'int(4) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$log_tbl['date']= array('Type'=>'datetime', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$log_tbl['message']= array('Type'=>'text', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$log_tbl['data']= array('Type'=>'longtext', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$log_tbl['source']= array('Type'=>'varchar(255)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$log_tbl['session']= array('Type'=>'char(32)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	dbAlterTable('log_tbl', $log_tbl, true, 'MyISAM');
}
?>
