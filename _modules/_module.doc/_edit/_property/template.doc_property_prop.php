<?
function doc_property_prop_update(&$data){
	$prop = getValue('docProperty');
	$data[':property'] = $prop;
	
	$propName = getValue('docPropertyName');
	$propValue= getValue('docPropertyValue');
	if (is_array($propName) && is_array($propValue))
	{
		foreach($propName as $ix => $name){
			@$val = $propValue[$ix];
			if ($name) $data[':property'][$name] = $val;
		}
	}
}
?>
<? function doc_property_prop(&$data){
	$db = module('doc', $data);
	$id	= $db->id();
?>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
<tr>
    <th>Свойство</th>
    <th>Значение</th>
</tr>
<?
foreach(module("prop:get:$id") as $name => $data){
?>
<tr>
    <td nowrap>{$name}</td>
    <td width="100%"><input type="text" name="docProperty[{$name}]" value="{$data[property]}" class="input w100" /></td>
</tr>
<? } ?>
<tr class="adminReplicate" id="addProp">
    <td><input name="docPropertyName[]" type="text" class="input" value="" size="20"  /></td>
    <td width="100%"><input type="text" name="docPropertyValue[]" value="" class="input w100" /></td>
</tr>
</table>
<p><input type="button" class="button adminReplicateButton" id="addProp" value="Добавть свойство"></p>
<? return '100-Характеристики и свойства'; } ?>