<?
function module_feedback($fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("feedback_$fn");
	return $fn?$fn($val, $data):NULL;
}
function feedback_get($formName, $data)
{
	$form = getCacheValue("form_$formName");
	if (!is_array($form))
	{
		$form = readIniFile(images."/feedback/form_$formName.txt");
		if (!$form) $form 	= readIniFile(getSiteFile("feedback/form_$formName.txt"));
		if (!$form) $form	= array();
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
	$types['Скрытое поле']	= 'hidden';
	$types['Адрес эл. почты']	= 'email';
	$types['Список выбора']		= 'select';
	$types['Чекбоксы']			= 'checkbox';
	$types['Радиоконпки']		= 'radio';
	$types['Паспорт'] 			= 'passport';
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
		
		$values		= explode(',', $data[$type]);
		$thisValue	= $formData[$thisField];

		$bMustBe		= $data['mustBe'] != '';
		$mustBe			= explode('|', $data['mustBe']);
		$bValuePresent	= trim($thisValue) != '';
		
		foreach($mustBe as $orField){
			$bValuePresent |= trim($formData[$orField]) != '';
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
		case 'passport':
			if (!$bMustBe) break;
			if (!is_array($thisValue))
				return "Неверное значение в поле \"<b>$name</b>\"";

			foreach($thisValue as &$f) $f = trim($f);
			
			if (!$thisValue['f1'] ||
				!$thisValue['f2'] ||
				!$thisValue['f3'] ||
				!$thisValue['f4'])
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
	$mailSMS	= '';
	$mailTo	= $form[':']['mailTo'];

	$title = $form[':']['mailTitle'];
	if (!$title) $title = $form[':']['title'];
	if (!$title) $title =  $form[':']['formTitle'];

	$mailFrom	= '';
	$nameFrom	= '';
	
	if (!$mailTo) @$mailTo = $ini[':mail']['mailFeedback'];
	if (!$mailTo) @$mailTo = $ini[':mail']['mailAdmin'];
	
	foreach($form as $name => $data)
	{ 
		if ($name[0] == ':') continue;
		
		$thisField	= $name;
		$type		= getFormFeedbackType($data);
		$thisValue	= $formData[$thisField];
		$notify		= $data['notify'];

		switch($type){
		default:
			if (!$thisValue) continue;
			$thisValue	= trim($thisValue);
			$mail		.= "$name: $thisValue\r\n\r\n";
			$thisValue	= htmlspecialchars($thisValue);
			$mailHtml	.= "<p><b>$name:</b> $thisValue</p>";
			if ($notify) $mailSMS .= "$name: $thisValue\r\n";
		break;
		case 'checkbox':
			if (!$thisValue) continue;
			$thisValue	= implode(', ', $thisValue);
			$thisValue	= trim($thisValue);
			$mail 		.= "$name: $thisValue\r\n\r\n";
			
			$thisValue	= htmlspecialchars($thisValue);
			$mailHtml	.= "<p><b>$name:</b> $thisValue</p>";
			
			if ($notify) $mailSMS .= "$name: $thisValue\r\n";
		break;
		case 'email':
			if (!$thisValue) continue;
			$thisValue	= trim($thisValue);
			$mailFrom	= $thisValue;
			$mail		.= "$name: $thisValue\r\n\r\n";
			
			$thisValue	= htmlspecialchars($thisValue);
			$mailHtml	.= "<p><b>$name:</b> <a href=\"mailto:$thisValue\">$thisValue</a></p>";
			
			if ($notify) $mailSMS .= "$name: $thisValue\r\n";
		break;
		case 'hidden':
			$thisValue	= trim($data['hidden']);
			$mail		.= "$name: $thisValue\r\n\r\n";
			
			$thisValue	= htmlspecialchars($thisValue);
			$mailHtml	.= "<p><b>$name:</b> $thisValue</p>";
			
			if ($notify) $mailSMS .= "$name: $thisValue\r\n";
		break;
		case 'passport':
			if (!is_array($thisValue)) continue;
			
			$mail		.= "$name: \r\n";
			$mail		.= "Серия $thisValue[f1]\r\n";
			$mail		.= "Номер $thisValue[f2]\r\n";
			$mail		.= "Кем выдан $thisValue[f3]\r\n";
			$mail		.= "Дата выдачи $thisValue[f4]\r\n";
			$mail		.= "\r\n";
			
			foreach($thisValue as &$f) $f = htmlspecialchars($f);
			
			$mailHtml	.= "<p><b>$name:</b><br />";
			$mailHtml	.= "Серия $thisValue[f1]<br />";
			$mailHtml	.= "Номер $thisValue[f2]<br />";
			$mailHtml	.= "Кем выдан $thisValue[f3]<br />";
			$mailHtml	.= "Дата выдачи $thisValue[f4]";
			$mailHtml	.= "</p>";
			
			if ($notify){
				$mailSMS	.= "$name: \r\n";
				$mailSMS	.= "Серия $thisValue[f1]\r\n";
				$mailSMS	.= "Номер $thisValue[f2]\r\n";
				$mailSMS	.= "Кем выдан $thisValue[f3]\r\n";
				$mailSMS	.= "Дата выдачи $thisValue[f4]\r\n";
				$mailSMS	.= "\r\n";
			}
		break;
		}
	}

	$mailTemplate = module("mail:template", $formName);
	if (!$mailTemplate) $mailTemplate = module("mail:template", 'feedback');

	$mailData = array('plain'=>$mail, 'html'=>$mailHtml, 'SMS' => $mailSMS);
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
function module_feedback_access($access, &$data){
	return hasAccessRole('admin,developer,writer');
}
function feedback_tools($val, &$data){
	if (!access('write', 'feedback:')) return;
	$data['Формы обратной связи#ajax']	= getURL('feedback_all');
}
?>