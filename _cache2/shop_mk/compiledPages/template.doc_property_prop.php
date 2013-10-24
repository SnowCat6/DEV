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
?><? function doc_property_prop(&$data)
{
	m('script:ajaxLink');

	$db		= module('doc', $data);
	$id		= $db->id();
	$type	= $data['doc_type'];

	$prop	= $id?module("prop:get:$id"):array();
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
	if ($type == 'catalog') docPropertyCatalog($db, $prop);
	else doc_propertyAll($db, $prop);
	
	return '100-Характеристики';
}
?><? function doc_propertyAll($db, &$prop){
	m('script:autocomplete');
?>
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
    <td nowrap><input type="text" name="docProperty[name][<? if(isset($name)) echo htmlspecialchars($name) ?>]" value="<? if(isset($d["name"])) echo htmlspecialchars($d["name"]) ?>" class="input autocomplete" options="propAutocomplete" size="20" /></td>
    <td width="100%"><input type="text" name="docProperty[value][<? if(isset($name)) echo htmlspecialchars($name) ?>]" value="<? if(isset($d["property"])) echo htmlspecialchars($d["property"]) ?>" class="input w100 autocomplete" options="propAutocomplete2" /></td>
    <td nowrap="nowrap"><? if(isset($type)) echo $type ?></td>
</tr>
<? } ?>
<tr class="adminReplicate" id="addProp">
    <td><a class="delete" href="">X</a></td>
    <td><input name="docPropertyName[]" id="propName" type="text" class="input autocomplete" options="propAutocomplete" value="" size="20"  /></td>
    <td width="100%"><input type="text" name="docPropertyValue[]" id="propValue" value="" class="input w100 autocomplete" options="propAutocomplete2" /></td>
    <td>&nbsp;</td>
</tr>
</table>
<p>
<input type="button" class="button adminReplicateButton" id="addProp" value="Добавть свойство">
<a href="<? module("getURL:property_all"); ?>" id="ajax">Посмотреть все свойства</a>
</p>

<div>Добавить множество свойств, пример строки: <strong>Операционная система: Android 4.0.4, Android 2.3</strong></div>
<textarea name="bulkPropAdd" id="bulkPropAdd" cols="45" rows="5" class="input w100"></textarea>
<p>Множественные значения вводятся в строку, через запятую с пробелом.</p>
<?
//	Получить названия свойств для поиска
$props	= module("prop:name:globalSearch,globalSearch2,productSearch,productSearch2");
$names	= array_keys($props);
foreach($names as &$val) $val = htmlspecialchars($val);
$n		= implode('","', $names);
if ($n) $n = "\"$n\"";

$names	= implode(',', $names);
$props	= module("prop:value:$names");
$n2		= '';
foreach($props as $name => &$names){
	if ($n2) $n2 .= ",\r\n";
	$n2	.= "\"$name\": [\"";
	foreach($names as &$val) $val = htmlspecialchars($val);
	$n2	.= implode('","', $names);
	$n2	.= '"]';
}
$n2	= '{'."$n2".'}';
?>
<script language="javascript" type="application/javascript">
var propAutocomplete = {
	source: new Array(<?= $n?>),
	minLength : 0
};
var propAutocomplete2 = {
	source: fnAuotocomplete2,
	select: fnAuotocomplete3,
	minLength : 0
};
function fnAuotocomplete2(request, respond){
	var prop = window[aoutocompleteNow.attr("options")];
	var name = $(aoutocompleteNow.parent().parent().find(".autocomplete").get(0)).val();
	var a = <?= $n2?>;
	respond(a[name]);
};
function fnAuotocomplete3(event, ui){
	var v = this.value?this.value.split(', '):new Array();
	if (v.indexOf(ui.item.value) < 0) v.push(ui.item.value);
	ui.item.value = v.join(', ');
	$("input").blur();
}

$(function(){
	$(".autocomplete").each(function(index, element) {
		$(this).autocomplete(window[$(this).attr("options")])
    });
});

$(function()
{
	$("#bulkPropAdd").change(function()
	{
		var lastName = '';
		var thisProp = '';
		var bTabRows = false;
		var rows = $(this).val().split("\n");
		for (row in rows)
		{
			row = rows[row];
			row	= row.split("\t", 2);
			if (row.length > 1)
			{
				addProperty(lastName, thisProp);
				
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
					addProperty(lastName, row[0]);
				}else{
					lastName = row[0];
				}
				continue;
			}
			addProperty(prop[0], prop[1]);
			lastName = '';
		}
		addProperty(lastName, thisProp);

		$(this).val("");
	});
});

function addProperty(key, value){
	key = key.replace(/^\s+|\s+$/g, '');
	value = value.replace(/^\s+|\s+$/g, '');
	if (!key || !value) return;
	$(".adminReplicate#addProp input#propName").val(key);
	$(".adminReplicate#addProp input#propValue").val(value);
	return adminCloneByID("addProp");
}
</script>
<? } ?><? function docPropertyCatalog($db, $prop)
{
	$data		= $db->data;
	$fields		= $data['fields'];

	$props		= module('prop:name:productSearch,productSearch2');
	$searchProps= $fields['any']['searchProps'];
	if (!is_array($searchProps)) $searchProps = array();
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top" width="50%">
    <h2>Свойства для поиска</h2>
<div id="searchProp">
<? foreach($searchProps as $name){?>
<div><label><input type="checkbox" checked="checked" name="searchProps[]" value="<? if(isset($name)) echo htmlspecialchars($name) ?>"  /><? if(isset($name)) echo htmlspecialchars($name) ?></label></div>
<? } ?>
</div>
<select name="searchProps[]" class="input w100" id="searchProp">
<option value="">-- нет ---</option>
<? foreach($props as $name => &$d){ ?>
<option value="<? if(isset($name)) echo htmlspecialchars($name) ?>"><? if(isset($name)) echo htmlspecialchars($name) ?></option>
<? } ?>
</select>
<script>
$(function(){
	$("select#searchProp").change(function(){
		var val = $(this).val();
		if (!val) return;
		$('<div><label><input type="checkbox" checked="checked" name="searchProps[]" value="' + val + '"  />' + val + '</label></div>')
			.appendTo("div#searchProp");
			$(this).attr("selectedIndex", 0);
	});
});
</script>
    </td>
    <td valign="top" width="50%"><? doc_propertyAll($db, $prop)?></td>
  </tr>
</table>
<? }?>
