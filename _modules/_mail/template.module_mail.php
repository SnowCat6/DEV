<?
function module_mail($fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	$db 		= new dbRow('mail_tbl', 'mail_id');
	if (!$fn) return $db;
	
	$fn = getFn("mail_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}
function mail_check($db, $val, $mailAddress){
	return preg_match('/\\b[A-Za-z0-9._%-]+@[A-Za-z0-9._%-]+\\.[A-Za-z]{2,4}\\b/', $mailAddress);
}
function mail_send($db, $val, $mail)
{
	@list($mailFrom, $mailTo, $mailTemplate, $title) = explode(':', $val, 4);
	if ($mailTemplate) $mail = makeMail($mailTemplate, $mail);

	//	Глобальные настройки
	$ini		= getCacheValue('ini');
	$globalIni	= getGlobalCacheValue('ini');
	
	//	Если кому не задано - отправить администратору
	if ($mailTo == '') @$mailTo = $ini[':mail']['mailAdmin'];
	if ($mailTo == '') @$mailTo = $globalIni[':mail']['mailAdmin'];
	
	if (!mail_check('', '', $mailFrom)) $mailFrom = '';
	if (!$mailFrom) @$mailFrom = $ini[':mail']['mailFrom'];
	if (!$mailFrom) @$mailFrom = $globalIni[':mail']['mailFrom'];
	if (!$mailFrom) @$mailFrom = ini_get('sendmail_from');
	
	$d	= array();
	$d['user_id']	= 0;
	$d['mailStatus']= 'sendWait';
	$d['from']		= "$mailFrom";
	$d['to']		= "$mailTo";
	$d['subject']	= "$title";
	$d['document']	= $mail;
	$d['dateSend']	= time();
	$iid	= $db->update($d, false);
	$error	= mysql_error();

	if (!$error)
	{
		$a		= array();
		$error	= mailAttachment($mailFrom, $mailTo, $title, $mail, '', $a);
	}
	
	if ($error){
		$d = array();
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
	$mailTemplate = images."/mailTemplates/mail_$name.txt";
	if (is_file($mailTemplate)) return $mailTemplate;
	$mailTemplate = cacheRootPath."/mailTemplates/mail_$name.txt";
	if (is_file($mailTemplate)) return $mailTemplate;
}

function mimeType($name){
	@$ext = strtolower(end(explode(".", $name)));
	switch($ext){
	case 'jpg':
	case 'jpeg':
				return 'image/jpg';
	case 'png': return 'image/png';
	case 'gif': return 'image/gif';
	}
	return 'application/octet-stream';
}
function mailSendSMS($email_from, $email_subject, $message)
{
	$email_message	= $message['SMS'];
	if (!$email_message) return;
	
	$email_to		= $message[':mailTo']['SMS'];
	if (!$email_to){
		$ini		= getIniValue(':mail');
		$email_to	= $ini['SMS_MAIL'];
	}

	$headers	= 	"From: $email_from\r\n".
			    	"MIME-Version: 1.0\r\n".
				    "Content-Type: text/plain;charset=utf-8";
		
	$email_subject	= '=?utf-8?B?'.base64_encode($email_subject).'?=';
	return mailSendRAW($email_to, $email_subject, $email_message, $headers);
}
function mailAttachment($email_from, $email_to, $email_subject, $message, $headers, &$attachment)
{
	if (!$email_to)		return "Нет адреса получателя.";
	if (!$email_from)	return "Нет адреса отправителя.";

	moduleEx('prepare:2fs', $message);

	if (is_array($message))
		mailSendSMS($email_from, $email_subject, $message);
	
	if (is_array($message) && $message['html'])
	{
		@$templ	= file_get_contents(getSiteFile("design/mailPage.html"));
		if ($templ) $message['html'] = str_replace('{%}', $message['html'], $templ);
	}
	//	Глобальные настройки
	$ini		= getCacheValue('ini');
	$globalIni	= getGlobalCacheValue('ini');

	//	Если задан сервер - отправить через него
	if (@$globalIni[':mail']['SMTP'])	ini_set("SMTP", $globalIni[':mail']['SMTP']);
	if (@$ini[':mail']['SMTP'])			ini_set("SMTP", $ini[':mail']['SMTP']);

	$fileatt_type = "application/octet-stream"; // File Type
	
	$semi_rand = md5(time());
	$mime_boundary = $semi_rand;
	
	$headers .= "From: $email_from\nMIME-Version: 1.0\n" .
	"Content-Type: multipart/related;\n boundary=\"mixed-$mime_boundary\"";
	
	$email_message = "This is a multi-part message in MIME format.\n\n" .
		"--mixed-$mime_boundary\n";
	//	Plain text only
	if (!is_array($message)){
		$email_message .= "Content-Type:text/plain; charset=\"UTF-8\"\n" .
		"Content-Transfer-Encoding: 8bit\n\n$message\n\n";
	}else{//	HTML
		reset($message);
		$email_message .= "Content-Type:multipart/alternative; boundary=\"alt-$mime_boundary\"\n";
		while(list($type, $val)=each($message))
		{
			switch($type)
			{
			case 'plain':
				$email_message .= "--alt-$mime_boundary\n" .
				"Content-Type:text/plain; charset=\"UTF-8\"\n" .
				"Content-Transfer-Encoding: 8bit\n\n$val\n\n";
			break;
			case 'html':
				$embedded 	= array();
				$val		= prepareHTML($val, $embedded);
				$email_message .= "--alt-$mime_boundary\n" .
				"Content-Type: multipart/related; boundary=\"related-$mime_boundary\"\n\n".
				"--alt-$mime_boundary\n" .
				"Content-Type:text/html; charset=\"UTF-8\"\n" .
				"Content-Transfer-Encoding: 8bit\n\n$val\n\n";
				
				foreach($embedded as $cid => $filepath)
				{
					$imageType	= mimeType($filepath);
					$inline		= chunk_split(base64_encode(file_get_contents($filepath)));
					$email_message .= "--related-$mime_boundary\n".
					"Content-Type: $imageType\n".
					"Content-Transfer-Encoding: base64\n".
					"Content-ID: <$cid>\n\n".
					"$inline".
					"--related-$mime_boundary--\n\n";
				}
//				echo '<pre>',htmlspecialchars($email_message), '</pre>'; die;
			break;
			}
		}
		$email_message .= "--alt-$mime_boundary--\n";

		reset($message);
		while(list($type, $val)=each($message)){
			if ($type != 'attach') continue;
			while(list($fileatt_name, $data)=each($val))
			{	
				$type = mimeType($fileatt_name);
				$data = chunk_split(base64_encode($data));
				$email_message .= "--mixed-$mime_boundary\n" .
				"Content-Type: $type;\n name=\"$fileatt_name\"\n" .
				"Content-ID: <$fileatt_name>\n" .
				"Content-Disposition: inline;\n filename=\"$fileatt_name\"\n" .
				"Content-Transfer-Encoding: base64\n\n$data\n\n";
				unset($data);
			}
		}
	}
	
	/********************************************** First File ********************************************/

	reset($attachment);
	while(list($fileatt_name, $data)=each($attachment))
	{	
		$data = chunk_split(base64_encode($data));
		$email_message .= "--mixed-$mime_boundary\n" .
		"Content-Type: $fileatt_type;\n name=\"$fileatt_name\"\n" .
		"Content-Disposition: attachment;\n filename=\"$fileatt_name\"\n" .
		"Content-Transfer-Encoding: base64\n\n$data\n\n";
		unset($data);
	}
	$email_message .= "--mixed-$mime_boundary--";
	return mailSendRAW($email_to, $email_subject, $email_message, $headers);
}

function mailSendRAW($email_to, $email_subject, $email_message, $headers)
{
	$bOK = '';
	$val = explode(';', $email_to);
	while(list(,$to) = each($val))
	{
		$to = trim($to);
		if (mail_check('', '', $to))
		{
			if (mail($to, $email_subject, $email_message, $headers) != true)
			{
				$error	= error_get_last();
				if ($error['type'] != 8)
				{
					$error	= $error['message'];
					$bOK	.="$error\r\n";
				}
			}
		}
	}
	return $bOK;
}
function parseEmbeddedMailFn($matches)
{
	global $embeddedImage;
	$val	= $matches[2];
	if (!is_file($val)) return $val;
	$id		= md5($val);
	$embeddedImage[$id] = $val;
	return "$matches[1]cid:$id$matches[3]";
}
function prepareHTML($mail, &$embedded)
{
	global $embeddedImage;
	$embeddedImage	= array();;
	$mail			= preg_replace_callback('/(<img.*src=[\'"]?)([^\'"]+)([\'"]?)/i', 'parseEmbeddedMailFn', $mail);
	$embedded		= $embeddedImage;
	return $mail;
}

function parseMailFn($matches)
{
	$val= $matches[1];
	$v1	= $v2	= '';
	list($v1, $v2) = explode('?=', $val, 2);
	if ($v2){
		$val = getMailValue($v2);
		return $val?$v1.$val:'';
	}
	return getMailValue($v1);
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
//	RULES
//	{variable} - print varuiable in data
//	{some text?=variable} - print text if variable not empty value
//	{!}	- show all variables
function makeMail($templatePath, $data)
{
	global $dataForMail;
	
	$folder		= dirname($templatePath);
	$template	= basename($templatePath, '.txt');

	$dataForMail= $data;
	if(@$mail	= file_get_contents($templatePath)){
		$mail	= preg_replace_callback('#{([^}]+)}#', 'parseMailFn', $mail);
	}else @$mail= $data['plain'];
	
	$dataForMail	= $data;
	$htmlFile		= "$templatePath.html";
	if (@$htmlMail	= file_get_contents($htmlFile)){
		$htmlMail	= preg_replace_callback('#{([^}]+)}#', 'parseMailFn', $htmlMail);
	}else $htmlMail = $data['html'];

	$dataForMail	= $data;
	$SMS_File		= "$folder/$template.SMS.txt";
	if ($SMS_Mail	= file_get_contents($SMS_File)){
		$SMS_Mail	= preg_replace_callback('#{([^}]+)}#', 'parseMailFn', $SMS_Mail);
	}else $SMS_Mail = $data['SMS'];
	
	return array(
		'plain'		=> $mail,
		'html'		=> $htmlMail,
		'SMS'		=> $SMS_Mail,
		':mailTo'	=> $data[':mailTo']
	);
}
function mail_tools($db, $val, &$data){
	if (!access('read', 'mail:')) return;
	$data[':mail']['Исходящая почта#ajax']	= getURL('admin_mail');
	$data[':mail']['Шаблоны#ajax']			= getURL('admin_mailTemplates');
}
function module_mail_access(&$access, $data){
	return hasAccessRole('admin,developer,writer,manager');
}
?>