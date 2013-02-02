<?
function doc_property_prop_update(&$data)
{
	$prop = getValue('docProperty');
	$data[':property'] = $prop;
	
	$docPropertyDelete	= getValue('docPropertyDelete');
	if (is_array($docPropertyDelete)){
		foreach($docPropertyDelete as $name){
			$data[':property'][$name] = '';
		}
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
	module('script:jq');
}
?>
<? function doc_property_prop(&$data)
{
	module('script:ajaxLink');
	$db = module('doc', $data);
	$id	= $db->id();
	
?>
<style>
#propertyNames a{
	white-space:nowrap;
	margin:0 10px;
	color:#FC0;
}
</style>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <th>&nbsp;</th>
    <th>Свойство</th>
    <th>Значение</th>
</tr>
<?
foreach(module("prop:get:$id") as $name => $data){
?>
<tr>
    <td nowrap><input name="docPropertyDelete[]" type="checkbox" value="{$name}" /></td>
    <td nowrap>{$name}</td>
    <td width="100%"><input type="text" name="docProperty[{$name}]" value="{$data[property]}" class="input w100" /></td>
</tr>
<? } ?>
<tr class="adminReplicate" id="addProp">
    <td>&nbsp;</td>
    <td><input name="docPropertyName[]" id="propName" type="text" class="input" value="" size="20"  /></td>
    <td width="100%"><input type="text" name="docPropertyValue[]" id="propValue" value="" class="input w100" /></td>
</tr>
</table>
<div id="propertyNames" style="display:none">
<? foreach(module('prop:get') as $name => $val){ ?>
<a href="#">{$name}</a>
<? } ?>
</div>
<p>
<input type="button" class="button adminReplicateButton" id="addProp" value="Добавть свойство">
<a href="{{getURL:property_all}}" id="ajax">Посмотреть все свойства</a>
</p>
<p>При сохранении документа, все отмеченные свойства будут удалены</p>

<script language="javascript" type="application/javascript">
$(function()
{
	$("#propertyNames a").click(function(){
		thisProperty.val($(this).text());
		$(".adminReplicate#addProp input#propValue").focus();
	});
	
	$("#propName").click(function(){
		thisProperty = $(this);
		$("#propertyNames").show();
	});
});
</script>
<? return '100-Характеристики и свойства'; } ?>



