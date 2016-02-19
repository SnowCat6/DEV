<?
addURL('property_all', 			'prop:all');
addURL('property_edit_(\d+)',	'prop:edit');
addURL('property_add',			'prop:edit:add');
addURL('property_getAjax',		'prop:getAjax');

addEvent('doc.sql',				'prop_sql');
addEvent('admin.tools.settings','prop:tools');

addEvent('prop.querySQLfn',		'prop:fnSQLbetween');
addEvent('prop.querySQLfn',		'prop:fnSQLperiod');

$viewType			= array();
$viewType['Нет']		= '';
setCacheValue(':properyViewType', $viewType);

$propGroups	= array();
$propGroups['globalSearch']		= 'Глобальный поиск';
$propGroups['globalSearch2']	= 'Глобальный поиск уточняющий';
$propGroups['productSearch']	= 'Поиск товаров';
$propGroups['productSearch2']	= 'Отображение товаров в каталоге';
$propGroups['productSEO']		= 'Использовать в SEO настройках';

setCacheValue(':properyGroupType', $propGroups);

addEvent('config.end',	'prop_config');
function module_prop_config($val, $data)
{
	$propGroups	= getCacheValue(':properyGroupType');
	$propGroups	= array_keys($propGroups);
	$propGroups	= makeIDS($propGroups);
	
	$documents_tbl['property']= array('Type'=>'array', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	dbAlter::alterTable('documents_tbl', $documents_tbl);

	$prop_name_tbl = array();
	$prop_name_tbl['prop_id']= array('Type'=>'smallint(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$prop_name_tbl['name']= array('Type'=>'varchar(255)', 'Null'=>'NO', 'Key'=>'UNI', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['valueType']= array('Type'=>'enum(\'valueText\',\'valueDigit\',\'valueDate\')', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['viewType']= array('Type'=>'varchar(128)', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['group']= array('Type'=>"set($propGroups)", 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['format']= array('Type'=>'varchar(128)', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['note']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['alias']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['values']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['queryName']= array('Type'=>'varchar(128)', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['query']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['sort']= array('Type'=>'int(10) unsigned', 'Null'=>'YES', 'Key'=>'', 'Default'=>'9999', 'Extra'=>'');
	$prop_name_tbl['visible']= array('Type'=>'tinyint(8) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'1', 'Extra'=>'');
	dbAlter::alterTable('prop_name_tbl', $prop_name_tbl);

	$prop_value_tbl = array();
	$prop_value_tbl['value_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$prop_value_tbl['prop_id']= array('Type'=>'smallint(10) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$prop_value_tbl['doc_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$prop_value_tbl['values_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'0', 'Extra'=>'');
	$fields = dbAlter::alterTable('prop_value_tbl', $prop_value_tbl);
	if ($fields['valueDigit']) dbAlter::deleteField('prop_value_tbl', 'valueDigit');

	$prop_values_tbl = array();
	$prop_values_tbl['values_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$prop_values_tbl['valueText']= array('Type'=>'varchar(255)', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$prop_values_tbl['valueDigit']= array('Type'=>'int(10)', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'0', 'Extra'=>'');
	$prop_values_tbl['valueDate']= array('Type'=>'datetime', 'Null'=>'YES', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$fields = dbAlter::alterTable('prop_values_tbl', $prop_values_tbl);
	if ($fields['valueFloat']) dbAlter::deleteField('prop_values_tbl', 'valueFloat');
}
?>