<? function doc_all_article(&$db, &$val, &$data)
{
	m('script:jq');
	m('script:ajaxForm');
	m('script:calendar');
	m('script:docAll');

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
		$ids	= $db2->selectKeys($db2->key, doc2sql($s), false);
	}
/***********************************/
	$prop	= array();
	if (testValue('manageDeleteAll')){
		foreach($ids as $id){
			module("doc:update:$id:delete");
			unset($ids[$id]);
		}
	}

	//	Delete property
	$property	= getValue('managePropertyDeleteName');
	if (is_array($property) && $ids)
	{
		$prop		= array();
		$propertyVal = getValue('managePropertyDeleteProperty');
		foreach($property as $ix => $name)
		{
			$val	= $propertyVal[$ix];
			$name	= trim($name);
			$val	= trim($val);
			if (!$name || !$val) continue;
			
			$prop['-property'][$name]	= $val;
		}
	}
	//	ADD property
	$property	= getValue('managePropertyName');
	if (is_array($property) && $ids)
	{
		$propertyVal = getValue('managePropertyProperty');
		foreach($property as $ix => $name)
		{
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
	//	Add parents
	$manageParents	= getValue('manageParents');
	if ($manageParents){
		if (testValue('manageParentAdd')){
			$prop['+property'][':parent']	= $manageParents;
		}else{
			$prop[':property'][':parent']	= $manageParents;
		}
	}

	if ($prop && $ids){
undo::lock();
set_time_limit(1*60);
		foreach($ids as $id){
			m("doc:update:$id:edit", $prop);
		}
undo::unlock();
		m('doc:clear');
	}
/*****************************************/
	$tabID		= rand(0, 10000);
	$db2		= module('doc');
	$typeName	= $type?docTypeEx($type, $template, 1):'разделов и каталогов';
	$props		= module("prop:name:globalSearch,globalSearch2,productSearch");
	m('page:title', "Редактирование $typeName");
	
	m('ajax:template', 'ajax_edit');
?>
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css" />
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
{{script:docAll}}

<ul class="propSelector seekLink">
<li><a href="#">Родитель</a>
	<ul>
<?
$db2	= module('doc:find', array('type'=>'page,catalog'));
while($data = $db2->next()){
	$s2['search']	= $search;
	$s2['search']['parent*']	= $db2->id();
	$s2['template']				= $template;
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

</td>
    <td valign="top" style="padding-left:20px">
<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#manageSearch">Поиск документов</a></li>
    <li class="ui-corner-top"><a href="#manageAction">Действия</a></li>
    <li class="ui-corner-top"><a href="#manageProperty">Характеристики</a></li>
    <li class="ui-corner-top"><a href="#managePropertyDelete">Удалить</a></li>
	<li style="float:right"><input name="docSave" type="submit" value="Выполнить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="manageSearch" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<input type="text" class="input w100" name="search[name]" value="{$search[name]}">
{{script:calendar}}
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td nowrap="nowrap">Дата изменения от</td>
    <td nowrap="nowrap">Дата изменения до</td>
    <td width="100%">&nbsp;</td>
  </tr>
  <tr>
    <td valign="top"><input type="text" value="{$search[dateUpdate]}" class="input w100" id="calendarFrom" name="search[dateUpdate]" /></td>
    <td valign="top"><input type="text" value="{$search[dateUpdateTo]}" class="input w100" id="calendarTo" name="search[dateUpdateTo]" /></td>
    <td valign="top">
	<label>
	<input type="hidden" name="search[showHidden]" value="" /> 
	<input type="checkbox" name="search[showHidden]" {checked:$search[showHidden]} value="showHidden" /> 
	показать скрытые
	</label>
	</td>
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
$parentTypes	= docConfig::getTypes();
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
	<label>
		<input name="manageParentAdd" type="checkbox" checked="checked" /> Добавить к имеющимся
	</label>
</p>
    </td>
    <td align="right" valign="top" nowrap="nowrap">
<div>
	<label>
		<input type="checkbox" name="manageDeleteAll" /> Удалить документы
	</label>
</div>
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

<div id="managePropertyDelete" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td>Удалить характеристику</td>
    <td>Значение</td>
</tr>
<tr class="adminReplicate" id="deleteProp">
    <td><input name="managePropertyDeleteName[]" type="text" class="input w100 autocomplete" size="20" options="propAutocomplete" /></td>
    <td><input name="managePropertyDeleteProperty[]" type="text" class="input w100 autocomplete" size="20" options="propAutocomplete2" /></td>
</tr>
</table>
<p>
<input type="button" class="button adminReplicateButton" id="deleteProp" value="Добавть характеристику">
</p>
{{script:property}}
{{script:clone}}
</div>


</div>

{{script:jq_ui}}
{{script:adminTabs}}
<script language="javascript" type="text/javascript">
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

<? function script_docAll(){
	m('script:overlay');
?>
<script>
$(function() {
	$(".propSelector").menu();
	$(".propSelector > li > a").click(function(){
		return false;
	});
});

var doChangeCheckValue = false;
$(function(){
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

<? } ?>

<? function style_docAll(){ ?>
<style>
.propSelector{
	max-height: 500px;
	z-index: 1000;
	position:relative;
}
.propSelector .ui-menu{
	position:absolute;
	max-height: 400px;
	max-width: 600px;
	min-width: 250px;
	font-weight:normal;
	overflow-y: auto;
	overflow-x: hidden;
}
.propSelector a{
	font-weight:normal;
	text-decoration:none;
}

.ajaxBody .propFilter{
	background:white;
	color:#333;
	padding:2px 5px;
}
.ajaxBody form{
	margin-top:10px;
}
</style>
<? } ?>
