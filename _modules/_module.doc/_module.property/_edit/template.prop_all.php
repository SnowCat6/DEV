<?
function prop_all($db, $val, &$data)
{
	module('script:ajaxForm');
	module('script:ajaxLink');
	noCache();

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
	
	if (testValue('doSorting')){
		$sort	= getValue('propertyOrder');
		$db->sortByKey('sort', $sort);
		$ids	= makeIDS($sort);
		 m("prop:clear:$ids");
	}

	module('script:ajaxLink');
	
	$sql	= array();
	$propertySearch = getValue('propertySearch');
	if ($propertySearch){
		$s = $db->escape_string($propertySearch);
		$sql[] = "`name` LIKE '%$s%'";
	}
	
	module('script:jq_ui');
	$db->order = 'sort, name';
	$db->open($sql);
	
	$s	= array();
	$s[':url']	= getURL('property_all');
	$p	= dbSeek($db, 15, $s);
	if (testValue('ajax')) setTemplate('ajax');
?>
{{script:seekKey}}
{{page:title=Список свойств}}
{{display:message}}
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px">
  <tr>
    <td width="100%">{!$p}</td>
    <td nowrap="nowrap"><a href="{{url:property_add}}" class="seekLink">Новое свойство</a></td>
  </tr>
</table>

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
    <th>&nbsp;</th>
    <th width="100%">Свойство</th>
    <th>Тип</th>
    <th>Формат</th>
</tr>
<tbody id="sortable">
<?
	$q	= getCacheValue('propertyQuery');
	while($data = $db->next()){
		$id		= $db->id();
		$group	= explode(',', $data['group']);
		$valueType	= $data['valueType'];
		if ($data['queryName']){
			$valueType = $data['queryName'];
			if ($q[$valueType]) $valueType = $q[$valueType];
		}
?>
<tr>
  <td><div  class="ui-icon ui-icon-arrowthick-2-n-s"></div></td>
    <td>
    <input type="hidden" name="propertyOrder[]" value= "{$id}" />
	<? if ($data['name'][0] != ':'){ ?><input name="propertyDelete[]" type="checkbox" value="{$id}" /><? } ?>
    </td>
    <td><a href="{{getURL:property_edit_$id}}" class="seekLink" title="{$data[note]}">{$data[name]}</a></td>
    <td nowrap="nowrap">{$valueType}</td>
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
	$( "#sortable" ).sortable({
		axis: 'y',
		update: function(e, ui){
			var form = $(this).parents("form");
			if (form.find("input[name=doSorting]").length) return;
			$('<input name="doSorting" type="hidden" />').appendTo(form);
		}
		}).disableSelection();
});
</script>
<? } ?>