<?
function prop_all($db, $val, &$data)
{
	$seek	= 50;
	$page	= (int)getValue('page');
	$search	= getValue('search');
	if ($search) $seek - 1000;
	
	module('script:ajaxForm');
	module('script:ajaxLink');
	m('script:prop_all');
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
		$db->sortByKey('sort', $sort, $page*$seek);
		$ids	= makeIDS($sort);
		 m("prop:clear:$ids");
	}

	module('script:ajaxLink');
	
	$sql	= array();
	if ($val = $search['name']){
		$s = $db->escape_string($val);
		$sql[] = "`name` LIKE '%$s%'";
	}
	
	$val = $search['group'];
	if (is_array($val)){
		foreach($val as &$v){
			$v	= dbEncString($db, $v);
			$v	= "find_in_set($v, `group`)";
		}
		$val	= implode(' OR ', $val);
		$sql[]	= "($val)";
	}
	
	$db->order = 'sort, name';
	$db->open($sql);
?>

{{ajax:template=ajax}}
{{script:jq_ui}}
{{script:seekKey}}
{{page:title=Список свойств}}
{{display:message}}
<form action="{{getURL:property_all}}" method="post" class="admin ajaxForm ajaxReload">

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px">
  <tr>
    <td width="100%">
<? 
$group	= array();
$group['Глобальные']			= 'globalSearch';
$group['Глобальные уточняющий']	= 'globalSearch2';
$group['Товары']			= 'productSearch';
//	$group['Товары уточняющий']	= 'productSearch2';
$thisValues	= $search['group'];
foreach($group as $name=>$val){ ?>
<label><input type="checkbox" name="search[group][]" value="{$val}" <?= is_int(array_search($val, $thisValues))?'checked':''?>>{$name}</label>
<? } ?>
    </td>
    <td nowrap="nowrap"><a href="{{url:property_add}}" class="seekLink">Новое свойство</a></td>
  </tr>
</table>

<input type="hidden" name="page" value= "{$page}" />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%"><input type="text" name="search[name]" value="{$search[name]}" class="input w100"  /></td>
    <td><input type="submit" value="Искать" class="button" /></td>
  </tr>
</table>

<? propReadAll($db, $seek) ?>

<p><input type="submit" class="button" value="Сохранить"> Все отмеченные свойства будут удалены</p>
</form>
<? } ?>
<? function script_prop_all($val){ ?>
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
<? function style_prop_all($val){ ?>
<style>
.admin .table td{
	padding:1px 10px;
}
.admin .table a{
	text-decoration:none;
}
</style>
<? } ?>
<? function propReadAll(&$db, $seek)
{
	$s	= array();
	$s[':url']	= getURL('property_all');
	$p	= dbSeek($db, $seek, $s);
?>
{!$p}
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="admin table">
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
<? } ?>
