<?
addUrl('admin_mail', 		'mail:all');
addUrl('admin_mail(\d+)', 	'mail:edit');

addEvent('config.end',	'mail_config');
function module_mail_config($val, $data)
{
	$mail_tbl = array();
	$mail_tbl['mail_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$mail_tbl['user_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$mail_tbl['from']= array('Type'=>'varchar(256)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$mail_tbl['to']= array('Type'=>'varchar(256)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$mail_tbl['subject']= array('Type'=>'varchar(256)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$mail_tbl['document']= array('Type'=>'text', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$mail_tbl['dateSend']= array('Type'=>'datetime', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$mail_tbl['mailStatus']= array('Type'=>'enum(\'sendOK\',\'sendFalse\',\'sendWait\')', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$mail_tbl['mailError']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	dbAlterTable('mail_tbl', $mail_tbl);
}
?>