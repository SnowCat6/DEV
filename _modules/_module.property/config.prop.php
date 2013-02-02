<?
addURL('property_all', 			'prop:all');
addURL('property_edit_(\d+)',	'prop:edit');

addEvent('config.end',	'prop_config');

function module_prop_config($val, $data)
{
	$prop_name_tbl = array();
	$prop_name_tbl['prop_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$prop_name_tbl['name']= array('Type'=>'varchar(255)', 'Null'=>'NO', 'Key'=>'UNI', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['valueType']= array('Type'=>'enum(\'valueText\',\'valueDigit\')', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['group']= array('Type'=>'varchar(128)', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['format']= array('Type'=>'varchar(128)', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['note']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	dbAlterTable('prop_name_tbl', $prop_name_tbl);

	$prop_value_tbl = array();
	$prop_value_tbl['value_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$prop_value_tbl['prop_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$prop_value_tbl['doc_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$prop_value_tbl['valueDigit']= array('Type'=>'int(10)', 'Null'=>'YES', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$prop_value_tbl['valueText']= array('Type'=>'varchar(255)', 'Null'=>'YES', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	dbAlterTable('prop_value_tbl', $prop_value_tbl);
}
?>