<?
function doc_property_prop_update(&$data)
{
	$docProperty = getValue('docProperty');
	if (!is_array(@$docProperty['name'])) $docProperty['name'] = array();

	foreach($docProperty['name'] as $name => $value){
		$data[':property'][$name]	= '';
		@$data[':property'][$value]	= $docProperty['value'][$name];
	}
	
	$propName	= getValue('docPropertyName');
	$propValue	= getValue('docPropertyValue');
	if (is_array($propName) && is_array($propValue))
	{
		foreach($propName as $ix => $name){
			@$val = $propValue[$ix];
			if ($name) $dataProperty[$name] = $val;
		}
	}
	
	dataMerge($dataProperty, $data[':property']);
	$data[':property'] = $dataProperty;
}
?>
<? function doc_property_prop(&$data)
{
	module('script:ajaxLink');
	$db		= module('doc', $data);
	$id		= $db->id();
	$type	= $data['doc_type'];

	$prop = $id?module("prop:get:$id"):array();
	foreach($prop as $name => $d)
	{
		if ($name[0] == ':') continue;
		$name	= htmlspecialchars($name);
		echo "<input type=\"hidden\" name=\"docProperty[name][$name]\" />";
	}
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
		if ($name[0] == ':') continue;
		$iid	= $d['prop_id'];
		@$type	= $types[$d['valueType']];
		$nameFormat	= propFormat($name, $d);
?>
<tr>
    <td nowrap><a class="delete" href="">X</a></td>
    <td nowrap><input type="text" name="docProperty[name][{$name}]" value="{$d[name]}" class="input" size="20" /></td>
    <td width="100%"><input type="text" name="docProperty[value][{$name}]" value="{$d[property]}" class="input w100" /></td>
    <td nowrap="nowrap">{!$type}</td>
</tr>
<? } ?>
<tr class="adminReplicate" id="addProp">
    <td><a class="delete" href="">X</a></td>
    <td><input name="docPropertyName[]" id="propName" type="text" class="input" value="" size="20"  /></td>
    <td width="100%"><input type="text" name="docPropertyValue[]" id="propValue" value="" class="input w100" /></td>
    <td>&nbsp;</td>
</tr>
</table>
<style>
#propertyNames a{
	white-space:nowrap;
	margin:0 10px;
	color:#FC0;
}
</style>
<div id="propertyNames">
<?
$prop = module("prop:get");
foreach($prop as $name => $val){
	if ($name[0] == ':') continue;
	$nameFormat = propFormat($name, $val); ?>
<a href="" title="{$val[group]}: {$val[note]}">{!$nameFormat}</a>
<? } ?>
</div>
<p>
<input type="button" class="button adminReplicateButton" id="addProp" value="Добавть свойство">
<a href="{{getURL:property_all}}" id="ajax">Посмотреть все свойства</a>
</p>

<div>Добавить множество свойств, пример строки: <strong>Операционная система: Android 4.0.4, Android 2.3</strong></div>
<textarea name="bulkPropAdd" id="bulkPropAdd" cols="45" rows="5" class="input w100"></textarea>
<p>Множественные значения вводятся в строку, через запятую с пробелом.</p>

<script language="javascript" type="application/javascript">
$(function()
{
	var thisProperty = null;
	$("#propertyNames a").click(function(){
		if (!thisProperty) thisProperty = $(".adminReplicate#addProp input#propName");
		
		var val = $(this).html().replace(/<span>[^>]*<\/span>|<span[^>]*propFormat[^>]*>|<\/span>/ig, "");
		thisProperty.val(val);
		
		$(".adminReplicate#addProp input#propValue").focus();
		return false;
	});

	$("#bulkPropAdd").change(function()
	{
		var lastName = '';
		var rows = $(this).val().split("\n");
		for (row in rows)
		{
			row = rows[row];
			row	= row.replace("\t", ':');
			var prop = row.split(':', 2);
			if (prop.length < 2){
				if (lastName){
					addProperty(lastName, prop[0]);
					lastName = '';
				}else{
					lastName = prop[0];
				}
				continue;
			}
			addProperty(prop[0], prop[1]);
			lastName = '';
		}
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
<? return '100-Характеристики'; } ?>



