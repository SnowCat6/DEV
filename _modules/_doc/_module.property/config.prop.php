<?
addURL('property_all', 			'prop:all');
addURL('property_edit_(\d+)',	'prop:edit');
addURL('property_add',			'prop:edit:add');
addURL('property_getAjax',		'prop:getAjax');

addEvent('doc.sql',				'prop_sql');
addEvent('admin.tools.edit',	'prop:tools');

addEvent('prop.querySQLfn',		'prop:fnSQLbetween');
addEvent('prop.querySQLfn',		'prop:fnSQLperiod');

$viewType			= array();
$viewType['Нет']		= '';
setCacheValue(':properyViewType', $viewType);

addEvent('config.end',	'prop_config');
function module_prop_config($val, $data)
{
	$documents_tbl['property']= array('Type'=>'array', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	dbAlterTable('documents_tbl', $documents_tbl);

	$prop_name_tbl = array();
	$prop_name_tbl['prop_id']= array('Type'=>'smallint(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$prop_name_tbl['name']= array('Type'=>'varchar(255)', 'Null'=>'NO', 'Key'=>'UNI', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['valueType']= array('Type'=>'enum(\'valueText\',\'valueDigit\',\'valueDate\')', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['viewType']= array('Type'=>'varchar(128)', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['group']= array('Type'=>'set(\'globalSearch\',\'globalSearch2\',\'productSearch\',\'productSearch2\')', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['format']= array('Type'=>'varchar(128)', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['note']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['alias']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['values']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['queryName']= array('Type'=>'varchar(128)', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['query']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['sort']= array('Type'=>'int(10) unsigned', 'Null'=>'YES', 'Key'=>'', 'Default'=>'9999', 'Extra'=>'');
	$prop_name_tbl['visible']= array('Type'=>'tinyint(8) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'1', 'Extra'=>'');
	dbAlterTable('prop_name_tbl', $prop_name_tbl);

	$prop_value_tbl = array();
	$prop_value_tbl['value_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$prop_value_tbl['prop_id']= array('Type'=>'smallint(10) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$prop_value_tbl['doc_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$prop_value_tbl['values_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'0', 'Extra'=>'');
//	$prop_value_tbl['valueInt']= array('Type'=>'int(10)', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'0', 'Extra'=>'');
	$fields = dbAlterTable('prop_value_tbl', $prop_value_tbl);
	if ($fields['valueDigit']) dbDeleteField('prop_value_tbl', 'valueDigit');

	$prop_values_tbl = array();
	$prop_values_tbl['values_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$prop_values_tbl['valueText']= array('Type'=>'varchar(255)', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$prop_values_tbl['valueDigit']= array('Type'=>'int(10)', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'0', 'Extra'=>'');
	$prop_values_tbl['valueDate']= array('Type'=>'datetime', 'Null'=>'YES', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
//	$prop_values_tbl['valueFloat']= array('Type'=>'float(10,3)', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'0', 'Extra'=>'');
	$fields = dbAlterTable('prop_values_tbl', $prop_values_tbl);
	if ($fields['valueFloat']) dbDeleteField('prop_values_tbl', 'valueFloat');
	
	//	Migrate from old property
	$dbValue	= new dbRow('prop_value_tbl', 'value_id');
	$dbValues	= new dbRow('prop_values_tbl','values_id');
/*	
	if ($fields['valueDigit']){
		$table1	= $dbValue->table();
		$table2	= $dbValues->table();
		$dbValue->exec("UPDATE $table1 v INNER JOIN $table2 vs ON v.values_id = vs.values_id  SET v.valueInt = vs.valueDigit");
	}
*/	
	//	Make Date values
	$dbValues->open('`valueDate` IS NULL');
	while($data = $dbValues->next()){
		$dbValues->setValue($dbValues->id(), 'valueDate', makeDateStamp($data['valueText']));
	}
	
	$dbValue->open("`values_id` = 0");
	if (mysql_error()) return;
	if ($dbValue->rows() == 0) return;


/*	
	$dbValues->open();
	if (mysql_error()) return;
	
	$valueTextCache	= array();
	$valueDigitCache= array();
	
	while($data = $dbValues->next()){
		$id	= $dbValues->id();
		$valueTextCache[$data['valueText']]		= $id;
		$valueDigitCache[$data['valueDigit']]	= $id;
//		$valueDigitCache[$data['valueDate']]	= $id;
//		$valueDigitCache[$data['valueFloat']]	= $id;
	}
	
	while($data = $dbValue->next())
	{
		if ($data['valueText'] == NULL){
			$v		= $data['valueDigit'];
			@$iid	= $valueDigitCache[$v];
			if (!$iid){
				$d	= array();
				$d['valueDigit']= $v;
//				$d['valueFloat']= $v;
				$d['valueText']	= "$v";
//				$d['valueDate']	= $v;
				$iid = $dbValues->update($d, false);
				if (mysql_error()) return;
				$valueDigitCache[$v] = $iid;
			}
		}else{
			$v		= $data['valueText'];
			@$iid	= $valueTextCache[$v];
			if (!$iid){
				$d['valueDigit']	= (int)$v;
//				$d['valueFloat']	= (float)$v;
				$d['valueText']		= $v;
//				$d['valueDate']		= $v;
				$iid = $dbValues->update($d, false);
				if (mysql_error()) return;
				$valueTextCache[$v] = $iid;
			}
		}
		$dbValue->setValue($dbValue->id(), $dbValues->key, $iid, false);
		if (mysql_error()) return;
	}
	$table = $dbValue->table;
	$dbValue->exec("ALTER TABLE `$table` DROP COLUMN `valueDigit`, DROP COLUMN `valueText`");
*/
}
?>