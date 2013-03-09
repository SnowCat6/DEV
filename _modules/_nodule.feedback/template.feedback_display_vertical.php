<?
function feedback_display_vertical(&$formName, &$form, &$formData)
{
	$class		= $form[':']['class'];
	$url		= $form[':']['url'];
	$buttonName	= $form[':']['button'];
?>
<link rel="stylesheet" type="text/css" href="feedback/feedback.css">
<div class="{$class}">
<form action="{!$url}" method="post" enctype="multipart/form-data" id="{$formName}">
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
@$default	= $data['default'];
@$values	= explode(',', $data[$type]);

if (is_array($formData)) @$thisValue = $formData[$thisField];
else $thisValue = $default;
?>
<? switch($type){ ?>
<? default:	//	text field?>
<tr><th>{!$name}{!$note}</th></tr>
<tr><td><? feedbackText($fieldName, $thisValue, $values)?></td></tr>
<? break; ?>
<? case 'textarea':	//	textarea field?>
<tr><th>{!$name}{!$note}</th></tr>
<tr><th><? feedbackTextArea($fieldName, $thisValue, $values)?></th></tr>
<? break; ?>
<? case 'radio':	//	radio field?>
<tr><th valign="top">{!$name}{!$note}</th></tr>
<tr><td><? feedbackRadio($fieldName, $thisValue, $values)?></td></tr>
<? break; ?>
<? case 'checkbox':	//	checkbox field?>
<tr><th valign="top">{!$name}{!$note}</th></tr>
<tr><td><? feedbackCheckbox($fieldName, $thisValue, $values)?></td></tr>
<? break; ?>
<? case 'select':	//	select field?>
<tr><th valign="top">{!$name}{!$note}</th></tr>
<tr><td><? feedbackSelect($fieldName, $thisValue, $values)?> </td></tr>
<? break; ?>
<? }//	switch ?>
<? }//	foreach ?>
</table>
<p><input type="submit" value="{$buttonName}" class="button" /></p>
</form>
</div>
<? } ?>