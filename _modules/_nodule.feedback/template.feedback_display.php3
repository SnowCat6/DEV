<? function feedback_display($formName, $data)
{
	module('script:maskInput');
	@list($formName, $template) = explode(':', $formName);

	if (!$formName) $formName = $data[1];
	if (!$formName) $formName = 'feedback';
	
	$form = module("feedback:get:$formName");
	if (!$form) return;
	if ($formName && is_array($data)) dataMerge($form, $data);
	
	@$class	= $form[':']['class'];
	if (!$class) $class="feedback";
	$form[':']['class'] = $class;

	@$url	= $form[':']['url'];
	if (!$url) $url	= getURL("#");
	$form[':']['url'] = $url;

	@$buttonName	= $form[':']['button'];
	if (!$buttonName) $buttonName = 'Отправить';
	$form[':']['button'] = $buttonName;
	
	@$title	= $form[':']['title'];
	if ($title) module("page:title", $title);
	
	$fn = getFn("feedback_display_$template");
	if ($fn) return $fn($formName, $form);
	
	$formData = getValue($formName);
	if (feedbackSend($formName, $formData))
		return module('display:message');
	
	@$title2 = $form[':']['formTitle'];
?>
<link rel="stylesheet" type="text/css" href="feedback/feedback.css">
<div class="{$class}">
<form action="{!$url}" method="post" enctype="multipart/form-data" id="{$formName}">
<? if ($title2){ ?><h2>{$title2}</h2><? } ?>
{{display:message}}
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<? foreach($form as $name => $data){ ?>
<?
if ($name[0] == ':') continue;

$thisField	= $name;
$fieldName	= $formName."[$thisField]";

$name	= htmlspecialchars($name);
$bMustBe= $data['mustBe'] != false;
if ($bMustBe) $name = "<b>$name<span>*</span></b>";

$note	= htmlspecialchars($data['note']);
if ($note) $note = "<div>$note</div>";

$type		= getFormFeedbackType($data);
@$values	= explode(',', $data[$type]);

if (is_array($formData)) @$thisValue = $formData[$thisField];
else @$thisValue = $data['default'];
?>
<? switch($type){ ?>
<? default:	//	text field?>
<tr>
    <th>{!$name}{!$note}</th>
    <td><? feedbackText($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?>
<? case 'textarea':	//	textarea field?>
<tr>
    <th colspan="2">{!$name}{!$note}</th>
</tr>
<tr>
  <th colspan="2"><? feedbackTextArea($fieldName, $thisValue, $values)?></th>
</tr>
<? break; ?>
<? case 'phone':	//	text field?>
<tr>
    <th>{!$name}{!$note}</th>
    <td><? feedbackPhone($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?>
<? case 'radio':	//	radio field?>
<tr>
    <th valign="top">{!$name}{!$note}</th>
    <td><? feedbackRadio($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?>
<? case 'checkbox':	//	checkbox field?>
<tr>
    <th valign="top">{!$name}{!$note}</th>
    <td><? feedbackCheckbox($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?>
<? case 'select':	//	select field?>
<tr>
    <th valign="top">{!$name}{!$note}</th>
    <td><? feedbackSelect($fieldName, $thisValue, $values)?> </td>
</tr>
<? break; ?>
<? }//	switch ?>
<? }//	foreach ?>
</table>
<p><input type="submit" value="{$buttonName}" class="button" /></p>
</form>
</div>
<? } ?>

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

<? function feedbackPhone(&$fieldName, &$thisValue, &$values){ 	module('script:maskInput') ?>
<input name="{$fieldName}" type="text" class="input w100 phone" value="{$thisValue}" />
<? } ?>
