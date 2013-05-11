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
				
				$ddb->dbValue->exec("UPDATE $table SET prop_id = $id WHERE prop_id = $iid");
				$ddb->delete($iid);
			}
			$prop['alias'] = implode("\r\n", $aliases);
			if ($bHasUpdate) module('doc:recompile');
		}

		$db->setValues($id, $prop, false);
//		module('display:log'); die;
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
<div>
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <th width="50%" valign="top"><div>Псевдонимы</div>
        <div></div></th>
      <th width="10" valign="top">&nbsp;</th>
      <th width="50%" valign="top">Стандартные значения</th>
    </tr>
    <tr>
      <td valign="top"><textarea name="property[alias]" rows="5" class="input w100">{$data[alias]}</textarea>
        <br />
        В каждой строчке по одному названию, при обноружении свойств с таким названием они будут объеденены</td>
      <td valign="top">&nbsp;</td>
      <td valign="top"><textarea name="property[values]" rows="5" class="input w100">{$data[values]}</textarea>
        <br />
В каждой строчке по одному названию, используется со свойствами фиксированного выбора</td>
    </tr>
  </table>
</div>
<p>
  <input type="submit" class="button" value="Сохранить" />
<a href="{{getURL:property_all}}" id="ajax">Посмотреть все свойства</a>
</p>
<p>Для обозначения места подстановки значения используйте знак <strong>%</strong> в поле <strong>формат</strong></p>
</form>
<? } ?>