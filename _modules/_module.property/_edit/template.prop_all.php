<?
function prop_all($db, $val, $data)
{
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
	
	module('script:jq_ui');
	$db->sortByKey('sort', getValue('propertyOrder'));

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
    <th>Формат</th>
</tr>
<tbody id="sortable">
<?
	$db->order = 'sort, name';
	$db->open();
	while($data = $db->next()){
		$id = $db->id();
?>
<tr>
    <td>
    <input type="hidden" name="propertyOrder[]" value= "{$id}" />
	<? if ($data['name'][0] != ':'){ ?><input name="propertyDelete[]" type="checkbox" value="{$id}" /><? } ?>
    </td>
    <td><a href="{{getURL:property_edit_$id}}" id="ajax" title="{$data[note]}">{$data[name]}</a></td>
    <td nowrap="nowrap">{$data[group]}</td>
    <td nowrap="nowrap">{$data[valueType]}</td>
    <td nowrap="nowrap">{$data[format]}</td>
</tr>
<? } ?>
</tbody>
</table>
<p><input type="submit" class="button" value="Сохранить"> Все отмеченные свойства будут удалены</p>
</form>
<script language="javascript" type="text/javascript">
$(function(){
	$( "#sortable" ).sortable();
	$( "#sortable" ).disableSelection();
});
</script>
<? } ?>