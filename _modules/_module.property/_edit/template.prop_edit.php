<?
function prop_edit($db, $val, $data){
	$id = $data[1];
	noCache();
?>
<?
if (!hasAccessRole('admin,developer,writer'))
	return module('message:error', 'Недостаточно прав');
	
	$data = $db->openID($id);
	if (!$data) return module('message:error', 'Нет свойства');
	
	$prop = getValue('property');
	if (is_array($prop))
	{
		$ddb	= module('prop');
		$table = $ddb->dbValue->table();
		//	Объеденить свойства
		$aliases= array();
		@$a		= explode("\r\n", $prop['alias']);
		foreach($a as $key => &$val){
			$val = trim($val);
			if ($val) $aliases[$val] = $val;
		}

		if ($aliases){
			$a = array();
			foreach($aliases as $name){
				makeSQLValue($name);
				$a[] = $name;
			};
			
			$bHasUpdate = false;
			$a = implode(', ', $a);
			$db->open("`name` IN ($a)");
			while($d = $db->next())
			{
				$iid = $db->id();
				if ($iid == $id) continue;
				
				@$a = explode("\r\n", $data['alias']);
				foreach($a as $val){
					$val = trim($val);
					if ($val) $aliases[$val] = $val;
				}
				
				$bHasUpdate = true;
				$aliases[$d['name']] = $d['name'];
				
				if ($d['valueType'] != $data['valueType'])
				{
					$table = $db->dbValue->table();
					if ($data['valueType'] == 'valueDigit'){
						$db->dbValue->exec("UPDATE $table SET `valueDigit` = CONV(`valueText`, 10, 10) WHERE prop_id = $iid");
					}else{
						$db->dbValue->exec("UPDATE $table SET `valueText` = `$valueDigit` WHERE prop_id = $iid");
					}
				}
				
				$ddb->dbValue->exec("UPDATE $table SET prop_id = $id WHERE prop_id = $iid");
				$ddb->delete($iid);
			}
			$prop['alias'] = implode("\r\n", $aliases);
			if ($bHasUpdate) module('doc:recompile');
		}

		$db->setValues($id, $prop, false);
//		module('display:log'); die;
		if (isset($prop['valueType']) && $prop['valueType'] != $data['valueType'])
		{
			$table = $db->dbValue->table();
			if ($prop['valueType'] == 'valueDigit'){
				$db->dbValue->exec("UPDATE $table SET `valueDigit` = CONV(`valueText`, 10, 10) WHERE prop_id = $id");
			}else{
				$db->dbValue->exec("UPDATE $table SET `valueText` = `valueDigit` WHERE prop_id = $id");
			}
		}
		module('message', 'Данные сохранены');
		return module('prop:all');
	}

	module('script:ajaxForm');
	module('script:ajaxLink');
	
	$data = $db->openID($id);
?>
{{page:title=Изменение свойства}}
<form action="{{getURL:property_edit_$id}}" method="post" class="admin ajaxForm ajaxReload">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td nowrap="nowrap">Название</td>
    <td width="100%"><input type="text"name="property[name]" class="input w100" value="{$data[name]}" /></td>
</tr>
<tr>
    <td nowrap="nowrap">Группа</td>
    <td><input type="text" name="property[group]" class="input w100" value="{$data[group]}" /></td>
</tr>
<tr>
    <td nowrap="nowrap">Формат*</td>
    <td><input type="text" name="property[format]" class="input w100" value="{$data[format]}" /></td>
</tr>
<tr>
  <td nowrap="nowrap">Тип</td>
  <td><select name="property[valueType]" class="input w100">
<?
foreach(explode(',', 'valueText,valueDigit') as $name){
	$class = $name==$data['valueType']?' selected="selected"':'';
?>
  <option value="{$name}"{!$class}>{$name}</option>
<? } ?>
  </select></td>
</tr>
</table>
<div>Описание</div>
<div><textarea name="property[note]" rows="5" class="input w100">{$data[note]}</textarea></div>
<div>Псевдонимы, в каждой строчке по одному названию, при обноружении свойств с таким названием они будут объеденены</div>
<div><textarea name="property[alias]" rows="5" class="input w100">{$data[alias]}</textarea></div>
<p>
<input type="submit" class="button" value="Сохранить" />
<a href="{{getURL:property_all}}" id="ajax">Посмотреть все свойства</a>
</p>
<p>Для обозначения места подстановки значения используйте знак <strong>%</strong> в поле <strong>формат</strong></p>
</form>
<? } ?>