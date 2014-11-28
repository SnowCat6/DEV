<?
function feedback_elm($type, $data)
{
	$fieldName	= $data['name'];
	$thisValue	= $data['value'];
	$values		= $data['values'];
	
	switch($type){
	case 'passport':	return feedbackPassport($fieldName, $thisValue, $values);
	case 'phone':		return feedbackPhone($fieldName, $thisValue, $values);
	case 'select':		return feedbackSelect($fieldName, $thisValue, $values);
	case 'chekbox':		return feedbackCheckbox($fieldName, $thisValue, $values);
	case 'radio':		return feedbackRadio($fieldName, $thisValue, $values);
	case 'textarea':	return feedbackTextArea($fieldName, $thisValue, $values);
	default:			return feedbackText($fieldName, $thisValue, $values);
	}
}

?>

<? function feedbackSelect(&$fieldName, &$thisValue, &$values){ ?>
<select name="{$fieldName}" class="input w100">
<? foreach($values as $name => $value){
	$class = $thisValue == $value?' selected="selected"':'';
?>
	<option value="{$value}"{!$class}>{$value}</option>
<? } ?>
</select>
<? } ?>

<? function feedbackCheckbox(&$fieldName, &$thisValue, &$values){ ?>
<?
if (!is_array($thisValue)) $thisValue = explode(',', $thisValue);
$thisValue = array_values($thisValue);

foreach($values as $name => $value){
	$class = $value && is_int(array_search($value, $thisValue))?' checked="checked"':'';
?>
    <div><label><input name="{$fieldName}[{$value}]" type="checkbox" value="{$value}"{!$class} /> {$value}</label></div>
<? } ?>
<? } ?>

<? function feedbackRadio(&$fieldName, &$thisValue, &$values){ ?>
<? foreach($values as $name => $value){
	$class = $thisValue == $value?' checked="checked"':'';
?>
    <div><label><input name="{$fieldName}" type="radio" value="{$value}"{!$class} /> {$value}</label></div>
<? } ?>
<? } ?>

<? function feedbackText(&$fieldName, &$thisValue, &$values){ ?>
<input name="{$fieldName}" type="text" class="input w100" value="{$thisValue}" />
<? } ?>

<? function feedbackTextArea(&$fieldName, &$thisValue, &$values){ ?>
<textarea name="{$fieldName}" rows="5" class="input w100">{$thisValue}</textarea>
<? } ?>

<? function feedbackPhone(&$fieldName, &$thisValue, &$values, $nStyle = ''){ 	module('script:maskInput') ?>
<input name="{$fieldName}" type="text" class="input w100 phone" value="{$thisValue}" />
<? } ?>

<? function feedbackPassport(&$fieldName, &$thisValue, &$values, $style = ''){
	switch($style){
?>
<? case 'vertical': ?>
<style>
.feedback .passport td{
	width:auto;
}
</style>
<table width="100%" cellpadding="2" cellspacing="0" class="passport">
<tr>
    <td nowrap="nowrap"><label for="f1">Серия:</label></td>
    <td width="100%"><input name="{$fieldName}[f1]" id="f1" type="text" class="input w100" value="{$thisValue[f1]}" /></td>
</tr>
<tr>
    <td nowrap="nowrap"><label for="f2">Номер:</label></td>
    <td><input name="{$fieldName}[f2]" id="f2" type="text" class="input w100" value="{$thisValue[f2]}" /></td>
</tr>
<tr>
    <td nowrap="nowrap"><label for="f3">Кем выдан:</label></td>
    <td><input name="{$fieldName}[f3]" id="f3" type="text" class="input w100" value="{$thisValue[f3]}" /></td>
</tr>
<tr>
    <td nowrap="nowrap"><label for="f4">Дата выдачи:</label></td>
    <td><input name="{$fieldName}[f4]" id="f4" type="text" class="input w100" value="{$thisValue[f4]}" /></td>
</tr>
</table>
<? break; ?>
<? default: ?>
<table width="100%" cellpadding="2" cellspacing="0" class="passport">
<tr>
    <td><label for="f1">Серия:</label></td><td width="25%"><input name="{$fieldName}[f1]" id="f1" type="text" class="input w100" value="{$thisValue[f1]}" /></td>
    <td><label for="f2">Номер:</label></td><td width="25%"><input name="{$fieldName}[f2]" id="f2" type="text" class="input w100" value="{$thisValue[f2]}" /></td>
    <td><label for="f3">Кем выдан:</label></td><td width="25%"><input name="{$fieldName}[f3]" id="f3" type="text" class="input w100" value="{$thisValue[f3]}" /></td>
    <td><label for="f4">Дата выдачи:</label></td><td width="25%"><input name="{$fieldName}[f4]" id="f4" type="text" class="input w100" value="{$thisValue[f4]}" /></td>
</tr>
</table>
<? }//	swith ?>
<? } ?>
