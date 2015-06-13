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
	$ini		= getCacheValue('ini');
	$globalIni	= getGlobalCacheValue('ini');
	
	//	Если кому не задано - отправить администратору
	if ($mailTo == '') @$mailTo = $ini[':mail']['mailAdmin'];
	if ($mailTo == '') @$mailTo = $globalIni[':mail']['mailAdmin'];
	
	if (!$mailFrom) @$mailFrom = $ini[':mail']['mailFrom'];
	if (!$mailFrom) @$mailFrom = $globalIni[':mail']['mailFrom'];
	if (!$mailFrom) @$mailFrom = ini_get('sendmail_from');
	if (!mail_check('', '', $mailFrom)) $mailFrom = '';
	
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
/*
	$mailTemplate = images."/mailTemplates/mail_$name.txt";
	if (is_file($mailTemplate)) return $mailTemplate;
	$mailTemplate = cacheRootPath."/mailTemplates/mail_$name.txt";
	if (is_file($mailTemplate)) return $mailTemplate;
*/
}

function mimeType($name)
{
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

	//	Глобальные настройки
	$ini		= getCacheValue('ini');
	$globalIni	= getGlobalCacheValue('ini');

	//	Если задан сервер - отправить через него
	if (@$globalIni[':mail']['SMTP'])	ini_set("SMTP", $globalIni[':mail']['SMTP']);
	if (@$ini[':mail']['SMTP'])			ini_set("SMTP", $ini[':mail']['SMTP']);

	$fileatt_type	= "application/octet-stream"; // File Type
	$semi_rand		= md5(time());
	$mime_boundary	= $semi_rand;
	$email_message	= '';
	$email_subject	= '=?utf-8?B?'.base64_encode($email_subject).'?=';
	
	$headers .= 
		"From: $email_from\nMIME-Version: 1.0\n" .
		"Content-Type: multipart/mixed;\n boundary=\"mixed-$mime_boundary\"";

	$email_message .= 
		"This is a multi-part message in MIME format.\n\n".
		"--mixed-$mime_boundary\n" .
		"Content-Type: multipart/alternative; boundary=\"alt-$mime_boundary\"\n\n";
	
	if (!is_array($message)) $message = array('plain' => $message);
	else reset($message);
	
	foreach($message as $type => $val)
	{
		switch($type)
		{
		case 'plain':
			$email_message .= 
				"--alt-$mime_boundary\n" .
				"Content-Type: text/plain; charset=\"UTF-8\"\n" .
				"Content-Transfer-Encoding: quoted-printable\n\n".
				php_quot_print_encode(trim($val)) .
				"\n\n";
			break;
		case 'html':
			$templ	= file_get_contents(getSiteFile("design/mailPage.html"));
			if ($templ) $val = str_replace('{%}', $val, $templ);

			$embedded 	= array();
			$email_message .= 
				"--alt-$mime_boundary\n" .
				"Content-Type: multipart/related; boundary=\"related-$mime_boundary\"\n\n".
				
				"--related-$mime_boundary\n".
				"Content-Type: text/html; charset=\"UTF-8\"\n" .
				"Content-Transfer-Encoding: quoted-printable\n\n".
				php_quot_print_encode(trim(prepareHTML($val, $embedded))) .
				"\n\n";
			
			foreach($embedded as $cid => $filepath)
			{
				$imageType		= mimeType($filepath);
				$email_message .= 
					"--related-$mime_boundary\n".
					"Content-Type: $imageType\n".
					"Content-Transfer-Encoding: base64\n".
					"Content-ID: <$cid>\n\n".
					chunk_split(base64_encode(file_get_contents($filepath))) .
					"\n\n";
			}
			$email_message .= "--related-$mime_boundary--\n\n";
			break;
		case 'SMS':
			$SMSTO		= $message[':mailTo']['SMS'];
			if (!$SMSTO){
				$ini	= getIniValue(':mail');
				$SMSTO	= $ini['SMS_MAIL'];
			}
			if (!$SMSTO) break;
			
			$SMSHEADER	= 	"From: $email_from\r\n".
							"MIME-Version: 1.0\r\n".
							"Content-Type: text/plain; charset=utf-8";
				
			mailSendRAW($SMSTO, $email_subject, $val, $SMSHEADER);
			break;
		}
	}
	$email_message .= "--alt-$mime_boundary--\n\n";

	reset($message);
	while(list($type, $val)=each($message)){
		if ($type != 'attach') continue;
		while(list($fileatt_name, $data)=each($val))
		{	
			$type = mimeType($fileatt_name);
			$data = chunk_split(base64_encode($data));
			$email_message .= 
				"--mixed-$mime_boundary\n" .
				"Content-Type: $type;\n name=\"$fileatt_name\"\n" .
				"Content-ID: <$fileatt_name>\n" .
				"Content-Disposition: inline;\n filename=\"$fileatt_name\"\n" .
				"Content-Transfer-Encoding: base64\n\n" .
				$data .
				"\n\n";
			unset($data);
		}
	}
	
	/********************************************** First File ********************************************/

	reset($attachment);
	while(list($fileatt_name, $data)=each($attachment))
	{	
		$data = chunk_split(base64_encode($data));
		$email_message .= 
			"--mixed-$mime_boundary\n" .
			"Content-Type: $fileatt_type;\n name=\"$fileatt_name\"\n" .
			"Content-Disposition: attachment;\n filename=\"$fileatt_name\"\n" .
			"Content-Transfer-Encoding: base64\n\n".
			"$data\n\n";
		unset($data);
	}
	$email_message .= "--mixed-$mime_boundary--";
//	file_put_contents('v:\\mail.txt', $email_message); return;

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
function mail_tools($db, $val, &$data){
	if (!access('read', 'mail:')) return;
	$data[':mail']['Исходящая почта#ajax']	= getURL('admin_mail');
	$data[':mail']['Шаблоны#ajax']			= getURL('admin_mailTemplates');
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
		$data[$name]	= preg_replace_callback('#{([^}]+)}#', 'parseMailFn', $mail);
	}

	return $data;
}

define('PHP_QPRINT_MAXL', 75);
function php_quot_print_encode($str)
{
    $lp = 0;
    $ret = '';
    $hex = "0123456789ABCDEF";
    $length = strlen($str);
    $str_index = 0;
    
    while ($length--) {
        if ((($c = $str[$str_index++]) == "\015") && ($str[$str_index] == "\012") && $length > 0) {
            $ret .= "\015";
            $ret .= $str[$str_index++];
            $length--;
            $lp = 0;
        } else {
            if (ctype_cntrl($c) 
                || (ord($c) == 0x7f) 
                || (ord($c) & 0x80) 
                || ($c == '=') 
                || (($c == ' ') && ($str[$str_index] == "\015")))
            {
                if (($lp += 3) > PHP_QPRINT_MAXL)
                {
                    $ret .= '=';
                    $ret .= "\015";
                    $ret .= "\012";
                    $lp = 3;
                }
                $ret .= '=';
                $ret .= $hex[ord($c) >> 4];
                $ret .= $hex[ord($c) & 0xf];
            } 
            else 
            {
                if ((++$lp) > PHP_QPRINT_MAXL) 
                {
                    $ret .= '=';
                    $ret .= "\015";
                    $ret .= "\012";
                    $lp = 1;
                }
                $ret .= $c;
            }
        }
    }

    return $ret;
}
?>