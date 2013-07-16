<?
function prop_all($db, $val, &$data)
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
	
	if (testValue('doSorting')){
		$sort = getValue('propertyOrder');
		$db->sortByKey('sort', $sort);
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
	$p = dbSeek($db, 15);
?>
<? $module_data = array(); $module_data[] = "Список свойств"; moduleEx("page:title", $module_data); ?>
<? module("display:message"); ?>
<? if(isset($p)) echo $p ?>
<form action="<? module("getURL:property_all"); ?>" method="post" class="admin ajaxForm ajaxReload">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%"><input type="text" name="propertySearch" value="<? if(isset($propertySearch)) echo htmlspecialchars($propertySearch) ?>" class="input w100"  /></td>
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
	while($data = $db->next()){
		$id		= $db->id();
		$group	= explode(',', $data['group']);
?>
<tr>
  <td><div  class="ui-icon ui-icon-arrowthick-2-n-s"></div></td>
    <td>
    <input type="hidden" name="propertyOrder[]" value= "<? if(isset($id)) echo htmlspecialchars($id) ?>" />
	<? if ($data['name'][0] != ':'){ ?><input name="propertyDelete[]" type="checkbox" value="<? if(isset($id)) echo htmlspecialchars($id) ?>" /><? } ?>
    </td>
    <td><a href="<? module("getURL:property_edit_$id"); ?>" id="ajax" title="<? if(isset($data["note"])) echo htmlspecialchars($data["note"]) ?>"><? if(isset($data["name"])) echo htmlspecialchars($data["name"]) ?></a></td>
    <td nowrap="nowrap"><? if(isset($data["valueType"])) echo htmlspecialchars($data["valueType"]) ?></td>
    <td nowrap="nowrap"><? if(isset($data["format"])) echo htmlspecialchars($data["format"]) ?></td>
</tr>
<? } ?>
</tbody>
</table>
<? if(isset($p)) echo $p ?>
<p><input type="submit" class="button" value="Сохранить"> Все отмеченные свойства будут удалены</p>
</form>
<script language="javascript" type="text/javascript">
$(function(){
	$( "#sortable" ).sortable({
		axis: 'y',
		update: function(e, ui){
			var form = $(this).parents("form");
			if (form.find("input[name=doSorting]").length) return;
			$('<input name="doSorting" />').appendTo(form);
		}
		}).disableSelection();
});
</script>
<? } ?>