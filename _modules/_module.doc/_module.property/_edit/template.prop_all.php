<?
function prop_all($db, $val, $data)
{
	module('script:ajaxForm');
	module('script:ajaxLink');
	noCache();
?>
<?
	if (!hasAccessRole('admin,developer,writer'))
		return module('message:error', 'Недостаточно прав');

	$deleteProp = getValue('propertyDelete');
	if (is_array($deleteProp)){
		$ids = makeIDS($deleteProp);
		$db->delete($ids);
		$db->dbValue->deleteByKey('prop_id', $ids);
		 m("prop:clear:$ids");
		module('message', 'Свойства удалены');
	}
	
	module('script:jq_ui');
	$db->sortByKey('sort', getValue('propertyOrder'));

	module('script:ajaxLink');
	
	$sql	= array();
	$propertySearch = getValue('propertySearch');
	if ($propertySearch){
		$s = mysql_real_escape_string($propertySearch);
		$sql[] = "`name` LIKE '%$s%'";
	}
	
	$db->order = 'sort, name';
	$db->open($sql);
	$p = dbSeek($db, 15);
?>
{{page:title=Список свойств}}
{{display:message}}
{!$p}
<form action="{{getURL:property_all}}" method="post" class="admin ajaxForm ajaxReload">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%"><input type="text" name="propertySearch" value="{$propertySearch}" class="input w100"  /></td>
    <td><input type="submit" value="Искать" class="button" /></td>
  </tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
    <th>&nbsp;</th>
    <th width="100%">Свойство</th>
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