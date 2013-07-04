<?
addURL('property_all', 			'prop:all');
addURL('property_edit_(\d+)',	'prop:edit');
addEvent('doc.sql',				'prop_sql');

$propertyGroup = array();
$propertyGroup['']		 		= '';
$propertyGroup['System'] 		= 'Системные';
$propertyGroup['Product'] 		= 'Свойства товара';
$propertyGroup['ProductFull'] 	= 'Характеристики';
setCacheValue('propertyGroup', $propertyGroup);

addEvent('config.end',	'prop_config');
function module_prop_config($val, $data)
{
	$documents_tbl['property']= array('Type'=>'longtext', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	dbAlterTable('documents_tbl', $documents_tbl);

	$prop_name_tbl = array();
	$prop_name_tbl['prop_id']= array('Type'=>'smallint(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$prop_name_tbl['name']= array('Type'=>'varchar(255)', 'Null'=>'NO', 'Key'=>'UNI', 'Default'=>'', 'Extra'=>'');
	$prop_name_tbl['valueType']= array('Type'=>'enum(\'valueText\',\'valueDigit\')', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
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
	dbAlterTable('prop_value_tbl', $prop_value_tbl);

	$prop_values_tbl = array();
	$prop_values_tbl['values_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$prop_values_tbl['valueDigit']= array('Type'=>'int(10)', 'Null'=>'YES', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$prop_values_tbl['valueText']= array('Type'=>'varchar(255)', 'Null'=>'YES', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	dbAlterTable('prop_values_tbl', $prop_values_tbl);
	
	//	Migrate from old property
	$dbValue	= new dbRow('prop_value_tbl', 'value_id');
	$dbValues	= new dbRow('prop_values_tbl','values_id');
	
	$dbValue->open("`values_id` = 0");
	if (mysql_error()) return;
	if ($dbValue->rows() == 0) return;
	
	$dbValues->open();
	if (mysql_error()) return;
	
	$valueTextCache	= array();
	$valueDigitCache= array();
	
	while($data = $dbValues->next()){
		$id	= $dbValues->id();
		$valueTextCache[$data['valueText']]		= $id;
		$valueDigitCache[$data['valueDigit']]	= $id;
	}
	
	while($data = $dbValue->next())
	{
		if ($data['valueText'] == NULL){
			$v		= $data['valueDigit'];
			@$iid	= $valueDigitCache[$v];
			if (!$iid){
				$d	= array();
				$d['valueDigit']= $v;
				$d['valueText']	= "$v";
				$iid = $dbValues->update($d, false);
				if (mysql_error()) return;
				$valueDigitCache[$v] = $iid;
			}
		}else{
			$v		= $data['valueText'];
			@$iid	= $valueTextCache[$v];
			if (!$iid){
				$d['valueDigit']	= (int)$v;
				$d['valueText']		= $v;
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
}
?>