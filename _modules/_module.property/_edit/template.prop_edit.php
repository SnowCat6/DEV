<?
function prop_edit($db, $val, $data){
	$id = $data[1];
?>
<h1>Изменение свойства</h1>
<?
if (!hasAccessRole('admin,developer,writer'))
	return module('message:error', 'Недостаточно прав');
	
	$data = $db->openID($id);
	if (!$data) return module('message:error', 'Нет свойства');
	
	$prop = getValue('property');
	if (is_array($prop)){
		$db->setValues($id, $prop, false);
		module('message', 'Данные сохранены');
	}

	module('script:ajaxForm');
	module('script:ajaxLink');
?>
<form action="{{getURL:property_edit_$id}}" class="admin ajaxForm">
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
<p>
<input type="submit" class="button" value="Сохранить" />
<a href="{{getURL:property_all}}" id="ajax">Посмотреть все свойства</a>
</p>
</form>
<? } ?>