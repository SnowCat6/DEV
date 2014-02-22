<? function doc_all_article(&$db, &$val, &$data)
{
	$search		= getValue('search');
	if (!is_array($search)) $search = array();
	$template	= getValue('template');
	
	$type		= $data[1];
	$thisURL	= $type?"page_all_$type":'page_all';
	$s			= array();
	$s['type']	= $type?$type:'page,catalog';
	$s['template']	= $template;
	dataMerge($s, $search);
	
	$typeName	= $type?docTypeEx($type, $template, 1):'разделов и каталогов';
	m('page:title', "Редактирование $typeName");
	$items		= m("doc:read:docAll", $s);
	$props		= module("prop:name:globalSearch,globalSearch2,productSearch,productSearch2");
?>
<form method="post" action="{{url:#=template:$template}}" enctype="application/x-www-form-urlencoded" class="ajaxForm ajaxReload">
<?= makeFormInput($search, 'search')?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td valign="top" style="width: 200px; min-width:200px" class="search search2 property seekLink">
<div class="title">
<big>Фильтры отбора</big>
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
<?
$n		= implode(',', array_keys($props));
$prop	= $n?module("prop:count:$n", $s):array();
foreach($prop as $name => $counts){
	if (isset($search['prop'][$name])) continue;
	$s2['search']	= $search;
	$s2['search']['prop'][$name]	= '';
	$s2['template']	= $template;
	removeEmpty($s2);
	$url	= getURL($thisURL, makeQueryString($s2));
?>
<div class="panel">
<h3>{$name}</h3>
<? foreach($counts as $n => $c){
	$s2['search']	= $search;
	$s2['search']['prop'][$name]	= $n;
	$s2['template']	= $template;
	removeEmpty($s2);
	$url	= getURL($thisURL, makeQueryString($s2));
	$n		= propFormat($n, $props[$name]);
?>
<span><a href="{!$url}">{!$n}</a> <sup>{$c}</sup></span>
<? } ?>
</div>
<? } ?>
</td>
    <td valign="top" style="padding-left:20px">{!$items}</td>
  </tr>
</table>
</form>
<? } ?>
<? function doc_read_docAll_before(&$db, $val, &$search)
{
	$search[':sort']	= 'sort';
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
	}
}?>
<? function doc_read_docAll(&$db, $val, &$search)
{
	$type	= $search['type'];
	$db2	= module('doc');
	$s		= array();
	$s['search']	= getValue('search');
	$s['template']	= getValue('template');
	removeEmpty($s);

	m('script:ajaxForm');
?>
<div id="manageTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#manageSearch">Поиск документов</a></li>
    <li class="ui-corner-top"><a href="#manageAction">Операции с отмеченными</a></li>
	<li style="float:right"><input name="docSave" type="submit" value="Выполнить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="manageSearch" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<input type="text" class="input w100" name="search[name]" value="{$search[name]}">
</div>

<div id="manageAction" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td valign="top">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td>Свойство</td>
    <td>Значение</td>
  </tr>
  <tr class="adminReplicate" id="addProp">
    <td><input name="managePropertyName[]" type="text" class="input w100 autocomplete" size="20" options="propAutocomplete" /></td>
    <td><input name="managePropertyProperty[]" type="text" class="input w100 autocomplete" size="20" options="propAutocomplete2" /></td>
  </tr>
</table>
<div style="white-space:nowrap">
<input type="button" class="button adminReplicateButton" id="addProp" value="Добавть свойство">
<label><input name="managePropAdd" type="checkbox" checked="checked" /> Добавить к имеющимся</label>
</div>
{{script:property}}
{{script:clone}}
<p style="white-space:nowrap">
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
<div><label><input name="manageParentAdd" type="checkbox" checked="checked" /> Добавить к имеющимся</label></div>
</p>
    </td>
    <td align="right" valign="top" nowrap="nowrap">
<div><label><input type="checkbox" name="manageDeleteAll" /> Удалить документы</label></div>
    </td>
  </tr>
</table>
</div>
</div>

{{script:jq_ui}}
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
	$("#manageTabs").tabs();;
});
</script>


<? if ($db->rows() == 0) return ?>
<?= $p = dbSeek($db, 15, $s); ?>
<table class="table all" cellpadding="0" cellspacing="0" width="100%">
<tr>
  <th>&nbsp;</th>
  <th><input type="checkbox" name="documentSelectAll" value="all" title="Применить ко всем документам" /></th>
  <th>&nbsp;</th>
  <th>Заголовок</th>
</tr>
<tbody id="sortable">
<?	
	while($data = $db->next()){
		$id		= $db->id();
		$url	= getURL($db->url());
		$drag	= docDraggableID($id, $data);
?>
<tr>
  <td><div  class="ui-icon ui-icon-arrowthick-2-n-s"></div></td>
    <td>
<input type="hidden" name="documentOrder[]" value= "{$id}" />
<input type="checkbox" name="documentDelete[]" value="{$id}" />
    </td>
    <td><a href="{{getURL:page_edit_$id}}" id="ajax_edit"><b>{$id}</b></a></td>
    <td width="100%">
    <a href="{!$url}"{!$drag}>{$data[title]}</a>
    <div><small><?
$split	= '';
$parents = getPageParents($id);
foreach($parents as $iid){
	$d		= $db2->openID($iid);
	$s2		= $s;
	$s2['search']['parent*']	= $iid;
	$url	= getURL('#', makeQueryString($s2));
?>
{!$split}<a href="{!$url}" class="seekLink">{$d[title]}</a>
<? $split = ' &gt; '; } ?></small></div>
    </td>
</tr>
<?	} ?>
</tbody>
</table>
{!$p}
<? } ?>