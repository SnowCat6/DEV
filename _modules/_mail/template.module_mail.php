<?
function module_mail($fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	$db 		= new dbRow('mail_tbl', 'mail_id');
	if (!$fn) return $db;
	
	$fn = getFn("mail_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}
function mail_send($db, $val, $mail)
{
	if ($val || !is_array($val)){
		list($mailFrom, $mailTo, $mailTemplate, $title) = explode(':', $val, 4);
	}else{
		$mailFrom		= $mail['mailFrom'];
		$mailTo			= $mail['mailTo'];
		$mailTemplate	= $mail['mailTemplate'];
		$title			= $mail['title'];
	}
	
	$mail = makeMail($mailTemplate, $mail);

	//	Глобальные настройки
	$ini		= getIniValue(':mail');
	$globalIni	= getGlobalCacheValue('ini');
	
	//	Если кому не задано - отправить администратору
	if ($mailTo == '') @$mailTo = $ini['mailAdmin'];
	if ($mailTo == '') @$mailTo = $globalIni[':mail']['mailAdmin'];
	
	if (!$mailFrom) @$mailFrom = $ini['mailFrom'];
	if (!$mailFrom) @$mailFrom = $globalIni[':mail']['mailFrom'];
	if (!$mailFrom) @$mailFrom = ini_get('sendmail_from');
	if (!cmsMail::checkValid($mailFrom)) $mailFrom = '';
	
	$d				= array();
	$d['user_id']	= 0;
	$d['mailStatus']= 'sendWait';
	$d['from']		= "$mailFrom";
	$d['to']		= "$mailTo";
	$d['subject']	= "$title";
	$d['document']	= $mail;
	$d['dateSend']	= time();
	$iid	= $db->update($d, false);
	$error	= mysql_error();

	if (!$error){
		$error	= cmsMail::send($mailFrom, $mailTo, $title, $mail);
	}
	
	if ($error){
		$d 					= array();
		$d['mailStatus']	= 'sendFalse';
		$d['mailError']		= $error;
		$db->setValues($iid, $d, false);
		return false;
	}
	$db->setValue($iid, 'mailStatus', 'sendOK', false);
	return true;
}
function mail_template($db, $val, $name)
{
	return getSiteFile(images . "/mailTemplates/mail_$name.txt");
}
function mail_check($db, $val, $mailAddress){
	return cmsMail::checkValid($mailAddress);
}
function mimeType($name)
{
	return cmsMail::mimeType($name);
}
function mailSendSMS($email_from, $email_subject, $message)
{
	$message['plain']	= ''; unset($message['plain']);
	$message['html']	= ''; unset($message['html']);
	return cmsMail::send($email_from, '', $email_subject, $message);
}
function mailAttachment($email_from, $email_to, $email_subject, $message, $headers, &$attachment)
{
	return cmsMail::send($email_from, $email_to, $email_subject, $message, $headers, $attachment);
}

function getMailValue($name)
{
	global $dataForMail;
	$name = trim($name);
	if ($name == '!')
	{
		ob_start();
		print_r($dataForMail);
		return ob_get_clean();
	}
	$name	= str_replace('.', '"]["', $name);
	eval("\$v = \$dataForMail[\"$name\"];");
	return $v;
}
function mail_tools($db, $val, &$data){
	if (!access('read', 'mail:')) return;
	$data[':mail']['Исходящая почта#ajax']	= getURL('admin_mail');
	$data[':mail']['[Шаблоны]#ajax']		= getURL('admin_mailTemplates');
}
function module_mail_access(&$access, $data){
	return hasAccessRole('admin,developer,writer,manager');
}

//	RULES
//	{variable} - print varuiable in data
//	{some text?=variable} - print text if variable not empty value
//	{!}	- show all variables
function makeMail($templatePath, $data)
{
	if (!is_array($data)) $data = array('plain' => $data);
	
	$folder		= dirname($templatePath);
	$template	= basename($templatePath, '.txt');
	
	global $dataForMail;
	$dataForMail= $data;
	
	$templates	= array(
		'plain'	=> '.txt',
		'html'	=> '.txt.html',
		'SMS'	=> '.SMS.txt',
	);
	
	foreach($templates as $name => $postfix)
	{
		$mail	= file_get_contents("$folder/$template$postfix");
		if(!$mail) continue;
		
		$data[$name]	= preg_replace_callback('#{([^}]+)}#', 
		function($matches) {
			$val	= $matches[1];
			$v1		= $v2	= '';
			list($v1, $v2) = explode('?=', $val, 2);
			if ($v2){
				$val = getMailValue($v2);
				return $val?$v1.$val:'';
			}
			return getMailValue($v1);
		}, $mail);
	}

	return $data;
}

?>