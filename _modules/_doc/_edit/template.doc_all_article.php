<? function doc_all_article(&$db, &$val, &$data)
{
	m('script:ajaxForm');
	m('script:calendar');

	$search		= getValue('search');
	if (!is_array($search)) $search = array();
	$template	= getValue('template');
	
	$type		= $data[1];
	$thisURL	= $type?"page_all_$type":'page_all';
	$s			= array();
	$s['type']	= $type?$type:'page,catalog';
	$s['template']	= $template;
	dataMerge($s, $search);

	if ($s['dateUpdate']){
		$s['dateUpdate']	= makeDateStamp($s['dateUpdate']);
	}
	if ($s['dateUpdateTo']){
		$s['dateUpdateTo']	= makeDateStamp($s['dateUpdateTo']);
	}

/***********************************/
	$ids	= getValue('documentDelete');
	if (!is_array($ids)) $ids = array();
	//	Все документы в выборке и страницах
	if (testValue('documentSelectAll')){
		$db2	= module('doc');
		$ids	= $db2->selectKeys($db2->key, doc2sql($search), false);
	}
/***********************************/
	$prop	= array();
	if (testValue('doSorting')){
		$db->sortByKey('sort', getValue('documentOrder'), getValue('page')*15);
	}
	if (testValue('manageDeleteAll')){
		foreach($ids as $id){
			module("doc:update:$id:delete");
			unset($ids[$id]);
		}
	}
	$property	= getValue('managePropertyName');
	if (is_array($property) && $ids){
		$prop		= array();
		$propertyVal = getValue('managePropertyProperty');
		foreach($property as $ix => $name){
			$val	= $propertyVal[$ix];
			$name	= trim($name);
			$val	= trim($val);
			if (!$name || !$val) continue;
			
			if (testValue('managePropAdd')){
				$prop['+property'][$name]	= $val;
			}else{
				$prop[':property'][$name]	= $val;
			}
		}
	}
	$manageParents	= getValue('manageParents');
	if ($manageParents){
		if (testValue('manageParentAdd')){
			$prop['+property'][':parent']	= $manageParents;
		}else{
			$prop[':property'][':parent']	= $manageParents;
		}
	}

	if ($prop && $ids){
		foreach($ids as $id){
			m("doc:update:$id:edit", $prop);
		}
		m('doc:clear');
	}
/*****************************************/
	$tabID		= rand(0, 10000);
	$db2		= module('doc');
	$typeName	= $type?docTypeEx($type, $template, 1):'разделов и каталогов';
	$props		= module("prop:name:globalSearch,globalSearch2,productSearch,productSearch2");
	m('page:title', "Редактирование $typeName");
	
	m('ajax:template', 'ajax_edit');
?>
<style>
.ajaxBody .propFilter{
	background:white;
	padding:2px 5px;
}
.ajaxBody form{
	margin-top:10px;
}
</style>
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">
<form method="post" action="{{url:#=template:$template}}" enctype="application/x-www-form-urlencoded" class="ajaxForm ajaxReload">
<?= makeFormInput($search, 'search')?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td valign="top" style="width: 200px; min-width:200px">
<div class="search search2 property seekLink">
<div class="title">
<big>Фильтры отбора</big>
</div>
<div class="propFilter">
<?
$parentID	= $search['parent*'];
$d			= $parentID?$db->openID($parentID):NULL;
if ($d){
	$s2['search']			= $search;
	$s2['search']['parent*']= '';
	$s2['template']			= $template;
	removeEmpty($s2);
	$url	= getURL($thisURL, makeQueryString($s2));
?>
<div>Каталог: <a href="{!$url}">{$d[title]}</a></div>
<? } ?>
<?
$sProp	= $search['prop'];
if (!is_array($sProp)) $sProp = array();
foreach($sProp as $name => $val){
	$s2['search']		= $search;
	$s2['search']['prop'][$name]	= '';
	$s2['template']		= $template;
	removeEmpty($s2);
	$url	= getURL($thisURL, makeQueryString($s2));
	$val	= propFormat($val, $props[$name]);
?>
<div>{$name}: <a href="{!$url}">{!$val}</a></div>
<? } ?>
</div>
</div>

{{script:jq_ui}}
{{script:doc_select}}
{{script:ajaxLink}}

<ul class="propSelector seekLink">
<li><a href="#">Родитель</a>
	<ul>
<?
$db2	= module('doc:find', array('type'=>'page,catalog'));
while($data = $db2->next()){
	$s2['search']	= $search;
	$s2['search']['parent*']	= $db2->id();
	$s2['template']	= $template;
	removeEmpty($s2);
	$url	= getURL($thisURL, makeQueryString($s2));
?>
		<li><a href="{!$url}">{$data[title]}</a></li>
<? } ?>
	</ul>
</li>
<?
$n		= implode(',', array_keys($props));
$prop	= $n?module("prop:count:$n", $s):array();
foreach($prop as $name=>&$val){
?>
<li><a href="#">{$name}</a>
    <ul>
<? foreach($val as $name2=>$count){
	$s2['search']	= $search;
	$s2['search']['prop'][$name]	= $name2;
	$s2['template']	= $template;
	removeEmpty($s2);
	$url	= getURL($thisURL, makeQueryString($s2));
?>
        <li><a href="{!$url}"><span>{$name2}</span> <sup>{$count}</sup></a></li>
<? } ?>
    </ul>
</li>
<? } ?>
</ul>
<script>
$(function() {
	$(".propSelector").menu().css({"max-height": 500, "overflow-y": "auto"});
	$(".propSelector .ui-menu").css({"z-index": 1000, "max-height": 300, "overflow-y": "auto", "min-width": 150, "max-width": 500});
});
</script>
</td>
    <td valign="top" style="padding-left:20px">
<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#manageSearch">Поиск документов</a></li>
    <li class="ui-corner-top"><a href="#manageAction">Действия</a></li>
    <li class="ui-corner-top"><a href="#manageProperty">Характеристики</a></li>
	<li style="float:right"><input name="docSave" type="submit" value="Выполнить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="manageSearch" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<input type="text" class="input w100" name="search[name]" value="{$search[name]}">
{{script:calendar}}
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>Дата изменения от</td>
    <td>Дата изменения до</td>
  </tr>
  <tr>
    <td><input type="text" value="{$search[dateUpdate]}" class="input w100" id="calendarFrom" name="search[dateUpdate]" /></td>
    <td><input type="text" value="{$search[dateUpdateTo]}" class="input w100" id="calendarTo" name="search[dateUpdateTo]" /></td>
  </tr>
</table>
</div>

<div id="manageAction" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td valign="top">
<div>Выбрать родителей</div>
<select name = "manageParents" class="input w100" id="parentToAdd">
<option value="">- родитель -</option>
<?
$parentToAdd	= array();
$parentTypes	= getCacheValue('docTypes');
$thisType		= explode(',', $type);
foreach($parentTypes as $parentType => $val){
	list($parentType,) = explode(':', $parentType);
	foreach($thisType as $t){
		if (access('add', "doc:$parentType:$t"))
			$parentToAdd[] = $parentType;
	}
};

$s2			= array();
$s2['type'] = implode(', ', $parentToAdd);

$db2->open(doc2sql($s2));
while($d = $db2->next()){
	$iid = $db2->id();
?><option value="{$iid}">{$d[title]}</option><? } ?>
</select>
<p>
<label><input name="manageParentAdd" type="checkbox" checked="checked" /> Добавить к имеющимся</label>
</p>
    </td>
    <td align="right" valign="top" nowrap="nowrap">
<div><label><input type="checkbox" name="manageDeleteAll" /> Удалить документы</label></div>
    </td>
  </tr>
</table>
</div>

<div id="manageProperty" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td>Добавить характеристику</td>
    <td>Значение</td>
  </tr>
  <tr class="adminReplicate" id="addProp">
    <td><input name="managePropertyName[]" type="text" class="input w100 autocomplete" size="20" options="propAutocomplete" /></td>
    <td><input name="managePropertyProperty[]" type="text" class="input w100 autocomplete" size="20" options="propAutocomplete2" /></td>
  </tr>
</table>
<p>
<input type="button" class="button adminReplicateButton" id="addProp" value="Добавть характеристику">
<label><input name="managePropAdd" type="checkbox" checked="checked" /> Добавить к имеющимся</label>
</p>
{{script:property}}
{{script:clone}}
</div>

</div>

{{script:jq_ui}}
{{script:adminTabs}}
<script language="javascript" type="text/javascript">
var doChangeCheckValue = false;
$(function(){
	$( "#sortable" ).sortable({
		axis: 'y',
		update: function(e, ui){
			var form = $(this).parents("form");
			if (form.find("input[name=doSorting]").length) return;
			$('<input name="doSorting" type="hidden" />').appendTo(form);
		}
	}).disableSelection();
	$("input[name*=documentSelectAll]").change(function(){
		doChangeCheckValue = true;
		var bCheck = $(this).prop('checked')?true:false;
		$("input[name*=documentDelete]").prop("checked", bCheck);
		doChangeCheckValue = false;
	});
	$("input[name*=documentDelete]").change(function(){
		if (doChangeCheckValue) return;
		$("input[name*=documentSelectAll]").prop("checked", false);
	});
});
</script>
<div class="ajaxDocument">
<?
if ($type == 'product') module("doc:read:docAllProduct", $s);
else module("doc:read:docAll", $s);
?>
</div>
    </td>
  </tr>
</table>
</form>
<? } ?>

