<?
function prop_all($db, $val, $data)
{
	module('script:ajaxForm');
	module('script:ajaxLink');
	noCache();
?>
{{page:title=Список свойств}}
<?
	if (!hasAccessRole('admin,developer,writer'))
		return module('message:error', 'Недостаточно прав');

	$propertySet= getValue('propertySet');
	if (is_array($propertySet)){
		foreach($propertySet as $id => $groups){
			$groups = array_filter($groups, 'strlen');
			$db->setValue($id, 'group', implode(',', $groups), false);
		}
	}
	
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
	
	$db->order = 'sort, name';
	$db->open();
	$p = dbSeek($db, 15);
?>
{{display:message}}
{!$p}
<form action="{{getURL:property_all}}" method="post" class="admin ajaxForm ajaxReload">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
    <th>&nbsp;</th>
    <th width="100%">Свойство</th>
    <th nowrap="nowrap">Г</th>
    <th nowrap="nowrap">Г2</th>
    <th nowrap="nowrap">Т</th>
    <th>Тип</th>
    <th>Формат</th>
</tr>
<tbody id="sortable">
<?
	while($data = $db->next()){
		$id		= $db->id();
		$group	= explode(',', $data['group']);
?>
<tr>
    <td>
    <input type="hidden" name="propertyOrder[]" value= "{$id}" />
	<? if ($data['name'][0] != ':'){ ?><input name="propertyDelete[]" type="checkbox" value="{$id}" /><? } ?>
    </td>
    <td><a href="{{getURL:property_edit_$id}}" id="ajax" title="{$data[note]}">{$data[name]}</a></td>
    <td nowrap="nowrap">
<input name="propertySet[{$id}][globalSearch]" type="hidden" value="" />
<input name="propertySet[{$id}][globalSearch]" type="checkbox" value="globalSearch" <?= is_int(array_search('globalSearch', $group))?' checked="checked"':'' ?> />
    </td>
    <td nowrap="nowrap">
<input name="propertySet[{$id}][globalSearch2]" type="hidden" value="" />
<input name="propertySet[{$id}][globalSearch2]" type="checkbox" value="globalSearch2" <?= is_int(array_search('globalSearch2', $group))?' checked="checked"':'' ?> />
    </td>
    <td nowrap="nowrap">
<input name="propertySet[{$id}][productSearch]" type="hidden" value="" />
<input name="propertySet[{$id}][productSearch]" type="checkbox" value="productSearch" <?= is_int(array_search('productSearch', $group))?' checked="checked"':'' ?> />
    </td>
    <td nowrap="nowrap">{$data[valueType]}</td>
    <td nowrap="nowrap">{$data[format]}</td>
</tr>
<? } ?>
</tbody>
</table>
{!$p}
<p><input type="submit" class="button" value="Сохранить"> Все отмеченные свойства будут удалены</p>
</form>
<script language="javascript" type="text/javascript">
$(function(){
	$( "#sortable" ).sortable();
	$( "#sortable" ).disableSelection();
});
</script>
<? } ?>