<?
function prop_all($db, $val, $data){
	module('script:ajaxForm');
	module('script:ajaxLink');
	noCache();
?>
<h1>Список свойств</h1>
<?
	if (!hasAccessRole('admin,developer,writer'))
		return module('message:error', 'Недостаточно прав');
	
	$deleteProp = getValue('propertyDelete');
	if (is_array($deleteProp)){
		$ids = makeIDS($deleteProp);
		$db->delete($ids);
		$db->dbValue->deleteByKey('prop_id', $ids);
		module('message', 'Свойства удалены');
	}
	module('script:ajaxLink');
?>
{{display:message}}
<form action="{{getURL:property_all}}" method="post" class="admin ajaxForm ajaxReload">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
    <th>&nbsp;</th>
    <th width="100%">Свойство</th>
    <th>Группа</th>
    <th>Тип</th>
</tr>
<?
	$db->order = 'name';
	$db->open();
	while($data = $db->next()){
		$id = $db->id();
?>
<tr>
    <td><input name="propertyDelete[]" type="checkbox" value="{$id}" /></td>
    <td><a href="{{getURL:property_edit_$id}}" id="ajax" title="{$data[note]}">{$data[name]}</a></td>
    <td nowrap="nowrap">{$data[group]}</td>
    <td nowrap="nowrap">{$data[valueType]}</td>
</tr>
<? } ?>
</table>
<p><input type="submit" class="button" value="Удалить отмеченные"></p>
</form>
<? } ?>