<? function feedback_display($formName, $data)
{
	module('script:maskInput');
	@list($formName, $template) = explode(':', $formName);

	if (!$formName) $formName = $data[1];
	if (!$formName) $formName = 'feedback';
	
	$form = getCacheValue("form_$formName");
	if (!isset($form)){
		$form = readIniFile(images."/feedback/form_$formName.txt");
		if (!$form) $form = readIniFile(localCacheFolder."/siteFiles/feedback/form_$formName.txt");
		setCacheValue("form_$formName", $form);
	}
	if (!$form) return;

	$formData	= getValue($formName);
	if ($formData && !defined("formSend_$formName"))
	{
		define("formSend_$formName", true);
		$error = sendFeedbackForm($formName, $form, $formData);
		if (!is_string($error)){
			module('message', "Ваше сообщение отправлено.");
			return module('display:message');
		}
		module('message:error', $error);
	}
	
	@$class	= $form[':']['class'];
	if (!$class) $class="feedback";
	$form[':']['class'] = $class;

	@$url	= $form[':']['url'];
	if (!$url) $url="#";
	$form[':']['url'] = $url;

	@$buttonName	= $form[':']['button'];
	if (!$buttonName) $buttonName = 'Отправить';
	$form[':']['button'] = $buttonName;
	
	$fn = getFn("feedback_display_$template");
	if ($fn) return $fn($formName, $form, $formData);
	
	@$title	= $form[':']['title'];
	if ($title) module("page:title", $title);
?>
<link rel="stylesheet" type="text/css" href="feedback/feedback.css">
<div class="{$class}">
<form action="{!$url}" method="post" enctype="multipart/form-data" id="{$formName}">
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
@$default	= $data['default'];
@$values	= explode(',', $data[$type]);

if (is_array($formData)) @$thisValue = $formData[$thisField];
else $thisValue = $default;
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
	$class = is_int(array_search($value, $thisValue))?' checked="checked"':'';
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

<? function feedbackPhone(&$fieldName, &$thisValue, &$values){ ?>
<input name="{$fieldName}" type="text" class="input w100 phone" value="{$thisValue}" />
<? } ?>

<?
function sendFeedbackForm($formName, $form, $formData)
{
	$error = checkValidFeedbackForm($formName, $form, $formData);
	if (is_string($error))
		return $error;
		
	$ini		= getCacheValue('ini');
	
	$mail		= '';
	$mailHtml	= '';
	@$mailTo	= $form[':']['mailTo'];
	@$title		= $form[':']['title'];

	$mailFrom	= '';
	$nameFrom	= '';
	
	if (!$mailTo) @$mailTo = $ini[':mail']['mailFeedback'];
	if (!$mailTo) @$mailTo = $ini[':mail']['mailAdmin'];
	
	foreach($form as $name => $data){ 
		if ($name[0] == ':') continue;
		
		$thisField	= $name;
		$type		= getFormFeedbackType($data);
		@$thisValue = $formData[$thisField];
		if (!$thisValue) continue;
		
		switch($type){
		default:
			$thisValue	= trim($thisValue);
			$mail		.= "$name: $thisValue\r\n\r\n";
			$thisValue	= htmlspecialchars($thisValue);
			$mailHtml	.= "<p><b>$name:</b> $thisValue<b></p>";
		break;
		case 'checkbox':
			$thisValue	= implode(', ', $thisValue);
			$thisValue	= trim($thisValue);
			$mail 		.= "$name: $thisValue\r\n\r\n";
			$thisValue	= htmlspecialchars($thisValue);
			$mailHtml	.= "<p><b>$name:</b> $thisValue</b></p>";
		break;
		case 'email':
			$thisValue	= trim($thisValue);
			$mailFrom	= $thisValue;
			$mail		.= "$name: $thisValue\r\n\r\n";
			$thisValue	= htmlspecialchars($thisValue);
			$mailHtml	.= "<p><b>$name:</b> <a href=\"mailto:$thisValue\">$thisValue</a><b></p>";
		break;
		}
	}

	if (!is_file($mailTemplate = images."/mailTemplates/mail_$formName.txt")) $mailTemplate = '';
	if (!$mailTemplate && !is_file($mailTemplate = localCacheFolder."/siteFiles/mailTemplates/mail_$formName.txt")) $mailTemplate = '';

	$mailData = array('plain'=>$mail, 'html'=>$mailHtml);
	$mailData['mailFrom']	= $mailFrom;
	$mailData['nameFrom']	= $nameFrom;
	$mailData['mailTo']		= $mailTo;
	$mailData['title']		= $title;
	
	if (module("mail:send:$title:$mailFrom:$mailTo:$mailTemplate", $mailData))
		return true;

	return true;
}
function checkValidFeedbackForm($formName, $form, $formData)
{
	 foreach($form as $name => $data){ 
		if ($name[0] == ':') continue;

		$thisField	= $name;
		$fieldName	= $formName."[$thisField]";

		$name	= htmlspecialchars($name);
		$type	= getFormFeedbackType($data);
		
		@$values	= explode(',', $data[$type]);
		@$thisValue = $formData[$thisField];

		$bMustBe		= $data['mustBe'] != '';
		$mustBe			= explode('|', $data['mustBe']);
		$bValuePresent	= trim($thisValue) != '';
		
		foreach($mustBe as $orField){
			@$bValuePresent |= trim($formData[$orField]) != '';
		}
		if ($bMustBe && !$bValuePresent){
			if (count($mustBe) > 1){
				$name = implode('"</b> или <b>"', $mustBe);
			}
			return "Заполните обязательное поле \"<b>$name</b>\"";
		}

		switch($type){
		case 'select':
		case 'radio':
			if (!is_int(array_search($thisValue, $values)))
				return "Неверное значение в поле \"<b>$name</b>\"";
			break;
		case 'checkbox':
			if (!is_array($thisValue))
				return "Неверное значение в поле \"<b>$name</b>\"";
			$thisValue = array_values($thisValue);
			foreach($thisValue as $val){
				if (!is_int(array_search($val, $values)))
					return "Неверное значение в поле \"<b>$name</b>\"";
			}
			break;
		case 'email':
			if (!$thisValue) break;
			if (!module('mail:check', $thisValue))
				return "Неверное значение в поле \"<b>$name</b>\"";
			break;
		}
	 }
	 return true;
}
function getFormFeedbackType($data){
	if (isset($data['select']))		return 'select';
	if (isset($data['checkbox']))	return 'checkbox';
	if (isset($data['radio']))		return 'radio';
	if (isset($data['textarea']))	return 'textarea';
	if (isset($data['email']))		return 'email';
	if (isset($data['phone']))		return 'phone';
}
?>


