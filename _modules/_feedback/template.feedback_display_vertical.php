<?
function feedback_display_vertical(&$formName, &$form)
{
	$formData = getValue($formName);
	if ($_POST[$formName] && feedbackSend($formName, $formData, $form))
		return module('display:message');

	$class		= $form[':']['class'];
	$url		= $form[':']['url'];
	$buttonName	= $form[':']['button'];
	@$titleForm	= $form[':']['titleForm'];
	
	$style		= 'vertical';
?>
<link rel="stylesheet" type="text/css" href="../../_templates/baseStyle.css">
<link rel="stylesheet" type="text/css" href="css/feedback.css">
<div class="{$class} vertical">
<form action="{$url}" method="post" enctype="multipart/form-data" id="{$formName}" class="feedbackForm">
{{display:message}}
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<? if ($titleForm){ ?>
<tr><th><h2>{$titleForm}</h2></th></tr>
<? } ?>
<? foreach($form as $name => $data){ ?>
<?
if ($name[0] == ':') continue;

$thisField	= $name;
$fieldName	= $formName."[$thisField]";

$name	= htmlspecialchars($name);
$bMustBe= $data['mustBe'] != false;
if ($bMustBe) $name = "<b>$name<span>*</span></b>";
$mustBeClass	= $bMustBe?' class="fieldMustBe"':'';

$note	= htmlspecialchars($data['note']);
if ($note) $note = "<div>$note</div>";

$type		= getFormFeedbackType($data);
@$default	= $data['default'];
@$values	= explode(',', $data[$type]);

if (is_array($formData)) @$thisValue = $formData[$thisField];
else $thisValue = $default;
?>
<? switch($type){ ?>
<? case 'hidden': break; ?>
<? default:	//	text field?>
<tr><th>{!$name}{!$note}</th></tr>
<tr><td {!mustBeClass}>{{feedback:elm:$type=name:$fieldName;value:$thisValue;values:$values}}</td></tr>
<? break; ?>
<? }//	switch ?>
<? }//	foreach ?>
</table>
<module:feedback:rules />
<p><input type="submit" value="{$buttonName}" class="button" /></p>
</form>
</div>
<? } ?>