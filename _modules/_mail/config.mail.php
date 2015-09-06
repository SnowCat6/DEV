<?
addUrl('admin_mail', 		'mail:all');
addUrl('admin_mail(\d+)', 	'mail:edit');
addUrl('admin_mailTemplates',		'mail:templates');
addUrl('admin_mailTemplates_(\w+)',	'mail:templatesEdit');

addAccess('mail:(\d*)',		'mail_access');
addEvent('admin.tools.edit','mail:tools');

addEvent('config.end',	'mail_config');
function module_mail_config($val, $data)
{
	$mail_tbl = array();
	$mail_tbl['mail_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$mail_tbl['user_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$mail_tbl['from']= array('Type'=>'varchar(256)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$mail_tbl['to']= array('Type'=>'varchar(256)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$mail_tbl['subject']= array('Type'=>'varchar(256)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$mail_tbl['document']= array('Type'=>'array', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$mail_tbl['dateSend']= array('Type'=>'datetime', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$mail_tbl['mailStatus']= array('Type'=>'enum(\'sendOK\',\'sendFalse\',\'sendWait\')', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$mail_tbl['mailError']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	dbAlter::alterTable('mail_tbl', $mail_tbl);
}
?>