<?
function doc_property_prop_update(&$data)
{
	$docProperty = getValue('docProperty');
	if (!is_array(@$docProperty['name'])) $docProperty['name'] = array();

	foreach($docProperty['name'] as $name => $value){
		$data[':property'][$name]	= $docProperty['value'][$name];
	}

	$propName	= getValue('docPropertyName');
	$propValue	= getValue('docPropertyValue');
	if (is_array($propName) && is_array($propValue))
	{
		foreach($propName as $ix => $name){
			@$val = $propValue[$ix];
			if ($name) $data[':property'][$name] = $val;
		}
	}
	
	$data['fields']['any']['searchProps']	= array();
	$searchProps	= getValue('searchProps');
	if (!is_array($searchProps))	$searchProps = array();
	foreach($searchProps as $name){
		if ($name) $data['fields']['any']['searchProps'][$name] = $name;
	}
}

function doc_property_prop(&$data)
{
	m('script:ajaxLink');

	$db		= module('doc', $data);
	$id		= $db->id();
	$type	= $data['doc_type'];

	$prop	= $id?module("prop:getEx:$id"):array();
	prop_filer($prop);
	foreach($prop as $name => $d)
	{
		if ($name == ':parent'){
			unset($prop[$name]);
			continue;
		}
		$name	= htmlspecialchars($name);
		echo "<input type=\"hidden\" name=\"docProperty[name][$name]\" />";
	}
	
	if ($type == 'catalog') return docPropertyCatalog($db, $prop);
?>
<div id="propertyTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#propertyValues">Свойства</a></li>
    <li class="ui-corner-top"><a href="#propertyMassValues">Массовый ввод</a></li>
</ul>

<div id="propertyValues">
<? doc_propertyAll($db, $prop)?>
</div>

<div id="propertyMassValues">
<? doc_propertyMass($db, $prop)?>
</div>

</div>

<script>
$(function() { $("#propertyTabs").tabs(); });
</script>
<? return '100-Характеристики'; } ?>

<? function docPropertyCatalog($db, $prop)
{
	$data		= $db->data;
	$fields		= $data['fields'];

	$props		= module('prop:name:productSearch,productSearch2');
	$searchProps= $fields['any']['searchProps'];
	
	if (!is_array($searchProps)) $searchProps = array();
	m('script:jq_ui');
?>
<div id="propertyTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#pripertySelect">Свойства поиска в каталоге</a></li>
    <li class="ui-corner-top"><a href="#propertyValues">Свойства каталога</a></li>
    <li class="ui-corner-top"><a href="#propertyMassValues">Массовый ввод</a></li>
</ul>

<div id="pripertySelect">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="50%" valign="top">
<div style="max-height:400px; overflow:auto">
<table border="0" cellspacing="0" cellpadding="0">
<tbody id="sortProperty">
<? foreach($searchProps as $name => &$d){ ?>
<tr>
    <td><div  class="ui-icon ui-icon-arrowthick-2-n-s"></div></td>
    <td width="100%"><label><input type="checkbox" name="searchProps[]" checked="checked" value="{$name}">{$name}</label></td>
</tr>
<? } ?>
<? foreach($props as $name => &$d){
	if (isset($searchProps[$name])) continue;
?>
<tr>
    <td><div  class="ui-icon ui-icon-arrowthick-2-n-s"></div></td>
    <td width="100%"><label><input type="checkbox" name="searchProps[]" value="{$name}">{$name}</label></td>
</tr>
<? } ?>
</tbody>
</table>
</div>
    </td>
    <td width="50%" valign="top">{{script:ajaxLink}}
<p>Выберите свойства по которым будет происходить отбор товаров в панели поиска.</p>
      <p>Если свойства не выбраны, будут использоваться заданные в <a href="{{url:property_all}}" id="ajax">настройках свойств</a>.</p>
Для изменения порядка отображения свойства, перетащите мышкой на нужную позицию.
    </td>
  </tr>
</table>
</div>

<div id="propertyValues">
<? doc_propertyAll($db, $prop)?>
</div>

<div id="propertyMassValues">
<? doc_propertyMass($db, $prop)?>
</div>

</div>

<script>
$(function() {
	$("#propertyTabs").tabs();
	$("#sortProperty").sortable({ axis: 'y' });
});
</script>

<? return '100-Характеристики'; }?>
<?
function doc_propertyAll($db, &$prop){
	m('script:autocomplete');
?>
<div style="max-height:500px; overflow:auto">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
    <th>&nbsp;</th>
    <th>Свойство</th>
    <th>Значение</th>
    <th>&nbsp;</th>
</tr>
<?
	$types	= array();
	$types['valueDigit']	= ' ( Число )';
	foreach($prop as $name => $d)
	{
		$iid	= $d['prop_id'];
		$type	= $types[$d['valueType']];
		$nameFormat	= propFormat($name, $d);
?>
<tr>
    <td nowrap><a class="delete" href="">X</a></td>
    <td nowrap><input type="text" name="docProperty[name][{$name}]" value="{$d[name]}" class="input autocomplete" options="propAutocomplete" size="40" /></td>
    <td width="100%"><input type="text" name="docProperty[value][{$name}]" value="{$d[property]}" class="input w100 autocomplete" options="propAutocomplete2" /></td>
    <td nowrap="nowrap">{!$type}</td>
</tr>
<? } ?>
<tr class="adminReplicate" id="addProp">
    <td><a class="delete" href="">X</a></td>
    <td><input name="docPropertyName[]" id="propName" type="text" class="input autocomplete" options="propAutocomplete" value="" size="40"  /></td>
    <td width="100%"><input type="text" name="docPropertyValue[]" id="propValue" value="" class="input w100 autocomplete" options="propAutocomplete2" /></td>
    <td>&nbsp;</td>
</tr>
</table>
<p>
<input type="button" class="button adminReplicateButton" id="addProp" value="Добавть свойство">
<a href="{{getURL:property_all}}" id="ajax">Посмотреть все свойства</a>
</p>
<p>Множественные значения вводятся в строку, через запятую с пробелом.</p>
</div>
{{script:property}}
<? } ?>
<? function doc_propertyMass($db, $prop){?>
<table width="100%" border="0">
  <tr>
    <td valign="top" width="50%">
<textarea name="bulkPropAdd" id="bulkPropAdd" cols="45" rows="15" class="input w100"></textarea>
    </td>
    <td valign="top" style="padding-left:10px">
<div class="propertySample"></div>
<div class="propertyHelp">
Добавить множество свойств, пример строки:<br>
<strong>Операционная система: Android 4.0.4, Android 2.3</strong><br>
<strong>Тип экрана: IPS</strong><br><br>

<i>Вы можете попробовать скопировать текст на сайте, интернет-магазина или спецификации, и вставить текст. Возможно, что программа сама распознает свойства.</i>
</div>
    </td>
  </tr>
</table>
<input type="button" class="button" value="Добавить свойства" id="bulkPropButton" />

<style>
.propertySample b{
	color:green;
}
</style>
<script>
$(function()
{
	$("#bulkPropAdd").keyup(function()
	{
		var val = '';
		var property = parseProperty($(this).val());
		for(name in property){
			var v = trimProperty(property[name]);
			name = trimProperty(name);
			if (!name || !v) continue;
			val += "<div><b>" + name + ":</b> " + v + "</div>";
		}
		$(".propertySample").html(val);
		$(".propertyHelp").css('display', val?'none':'block');
	});
	$("#bulkPropButton").click(function(){
		var property = parseProperty($("#bulkPropAdd").val());
		for(name in property){
			addProperty(name, property[name]);
		}
		$("#bulkPropAdd").val("");
		$(".propertySample").html("");
		$(".propertyHelp").css('display', 'block');
	});
});
function trimProperty(val){
	return val.replace(/^\s+|\s+$/g, '');
}
function addProperty(key, value){
	key = trimProperty(key);
	value = trimProperty(value);
	if (!key || !value) return;
	$(".adminReplicate#addProp input#propName").val(key);
	$(".adminReplicate#addProp input#propValue").val(value);
	return adminCloneByID("addProp");
}
function parseProperty(val)
{
	var lastName = '';
	var thisProp = '';
	var bTabRows = false;
	var property = new Array();
	var rows = val.split("\n");

	for (row in rows)
	{
		row = rows[row];
		row	= row.split("\t", 2);
		if (row.length > 1)
		{
			property[lastName] = thisProp;
			
			bTabRows = true;
			lastName = row[0];
			thisProp = row[1];
			continue;
		}
		if (bTabRows){
			thisProp += ", " + row[0];
			continue;
		}
		
		var prop = row[0].split(': ');
		if (prop.length != 2){
			var p = row[0].split(', ');
			if (p.length == 1) p = row[0].split(' & ');
			if (p.length == 1) p = row[0].split(':');
			if (p.length == 1) p = row[0].split('.');
			if (p.length > 1 && lastName){
				property[lastName] = row[0];
			}else{
				lastName = row[0];
			}
			continue;
		}
		property[prop[0]] = prop[1];
		lastName = '';
	}
	property[lastName] = thisProp;
	return property;
}
</script>
<? } ?>
