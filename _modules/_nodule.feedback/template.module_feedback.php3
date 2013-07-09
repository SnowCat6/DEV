<?
function module_feedback($fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("feedback_$fn");
	return $fn?$fn($val, &$data):NULL;
}
function feedback_get($formName, $data)
{
	$form = getCacheValue("form_$formName");
	if (!isset($form)){
		$form = readIniFile(images."/feedback/form_$formName.txt");
		if (!$form) $form = readIniFile(localCacheFolder."/siteFiles/feedback/form_$formName.txt");
		setCacheValue("form_$formName", $form);
	}
	return $form;
}
function getFormFeedbackType($data){
	$types = getFormFeedbackTypes();
	foreach($types as $name => $type){
		if (isset($data[$type])) return $type;
	}
}
function getFormFeedbackTypes()
{
	$types = array();
	$types['Текстовое поле']= 'text';
	$types['Тема']			= 'subject';
	$types['Ф.И.О.']		= 'name';
	$types['Телефон']		= 'phone';
	$types['Скрытое поле'] = 'hidden';
	$types['Адрес эл. почты']	= 'email';
	$types['Список выбора']		= 'select';
	$types['Чекбоксы']			= 'checkbox';
	$types['Радиоконпки']		= 'radio';
	$types['Поле ввода текста'] = 'textarea';
	return $types;
}
function checkValidFeedbackForm($formName, &$formData)
{
	$form = module("feedback:get:$formName");
	if (!$form) return 'Не данных для формы';
	
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
			if (!$thisValue) break;
			if (!is_int(array_search($thisValue, $values)))
				return "Неверное значение в поле \"<b>$name</b>\"";
			break;
		case 'checkbox':
			if (!$thisValue) break;
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
function makeFeedbackMail($formName, &$formData, $form = NULL)
{
	$error = checkValidFeedbackForm($formName, $formData);
	if (is_string($error)) return $error;

	if (!$form)	$form = module("feedback:get:$formName");
	$ini		= getCacheValue('ini');
	
	$mail		= '';
	$mailHtml	= '';
	@$mailTo	= $form[':']['mailTo'];

	@$title = $form[':']['mailTitle'];
	if (!$title) @$title = $form[':']['title'];
	if (!$title) @$title =  $form[':']['formTitle'];

	$mailFrom	= '';
	$nameFrom	= '';
	
	if (!$mailTo) @$mailTo = $ini[':mail']['mailFeedback'];
	if (!$mailTo) @$mailTo = $ini[':mail']['mailAdmin'];
	
	foreach($form as $name => $data)
	{ 
		if ($name[0] == ':') continue;
		
		$thisField	= $name;
		$type		= getFormFeedbackType($data);
		@$thisValue = $formData[$thisField];

		switch($type){
		default:
			if (!$thisValue) continue;
			$thisValue	= trim($thisValue);
			$mail		.= "$name: $thisValue\r\n\r\n";
			$thisValue	= htmlspecialchars($thisValue);
			$mailHtml	.= "<p><b>$name:</b> $thisValue</p>";
		break;
		case 'checkbox':
			if (!$thisValue) continue;
			$thisValue	= implode(', ', $thisValue);
			$thisValue	= trim($thisValue);
			$mail 		.= "$name: $thisValue\r\n\r\n";
			$thisValue	= htmlspecialchars($thisValue);
			$mailHtml	.= "<p><b>$name:</b> $thisValue</p>";
		break;
		case 'email':
			if (!$thisValue) continue;
			$thisValue	= trim($thisValue);
			$mailFrom	= $thisValue;
			$mail		.= "$name: $thisValue\r\n\r\n";
			$thisValue	= htmlspecialchars($thisValue);
			$mailHtml	.= "<p><b>$name:</b> <a href=\"mailto:$thisValue\">$thisValue</a></p>";
		break;
		case 'hidden':
			$thisValue	= trim($data['hidden']);
			$mail		.= "$name: $thisValue\r\n\r\n";
			$thisValue	= htmlspecialchars($thisValue);
			$mailHtml	.= "<p><b>$name:</b> $thisValue</p>";
		break;
		}
	}

	$mailTemplate = mail("mail:template", $formName);

	$mailData = array('plain'=>$mail, 'html'=>$mailHtml);
	$mailData['mailFrom']	= $mailFrom;
	$mailData['nameFrom']	= $nameFrom;
	$mailData['mailTo']		= $mailTo;
	$mailData['title']		= $title;
	$mailData['template']	= $mailTemplate;
	return $mailData;
}
function sendFeedbackForm($formName, &$formData, $form = NULL)
{
	$mailData = makeFeedbackMail($formName, $formData, $form);
	if (is_string($mailData)) return $mailData;
	
	if (module("mail:send:$mailData[mailFrom]:$mailData[mailTo]:$mailData[template]:$mailData[title]", $mailData))
		return true;

	return true;
}

function feedbackSend(&$formName, &$formData, $form = NULL)
{
	if ($formData && !defined("formSend_$formName"))
	{
		define("formSend_$formName", true);
		$error = sendFeedbackForm($formName, $formData, $form);
		if (!is_string($error)){
			module('message', "Ваше сообщение отправлено.");
			return true;
		}
		module('message:error', $error);
	}
}
?>