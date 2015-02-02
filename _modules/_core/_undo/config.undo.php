<?
addEvent('admin.tools.service', 'undo:tools');
addUrl('admin_undo',  			'undo:admin');
addAccess('undo',				'undoAccess');
addAccess('undo:(\d+)',			'undoAccess');

addEvent('config.end',	'undo_config');
function module_undo_config($val, $data)
{
	$log_tbl = array();
	$log_tbl['log_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$log_tbl['user_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$log_tbl['userIP']= array('Type'=>'int(4) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$log_tbl['date']= array('Type'=>'datetime', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$log_tbl['action']= array('Type'=>'varchar(32)', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$log_tbl['message']= array('Type'=>'text', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$log_tbl['data']= array('Type'=>'array', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$log_tbl['source']= array('Type'=>'varchar(255)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$log_tbl['session']= array('Type'=>'char(32)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	dbAlterTable('log_tbl', $log_tbl, 'MyISAM');
}
?>
