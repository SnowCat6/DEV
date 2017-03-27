<?
addEvent('site.noUrlFound',	'links:url');
addEvent('site.prepareURL',	'links:prepareURL');

addEvent('config.end',	'links_config');

function module_links_config($val, $data)
{
	$links_tbl = array();
	$links_tbl['link']= array('Type'=>'varchar(128)', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'');
	$links_tbl['nativeURL']= array('Type'=>'varchar(128)', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$links_tbl['user_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$links_tbl['sort']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0', 'Extra'=>'');
	$links_tbl['lastUpdate']= array('Type'=>'datetime', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	dbAlter::alterTable('links_tbl', $links_tbl);
}
?>