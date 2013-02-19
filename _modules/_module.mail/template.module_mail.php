<?
function module_mail($fn, $data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("mail_$fn");
	return $fn?$fn($val, &$data):NULL;
}
function mail_check($val, $mailAddress){
	return preg_match('/\\b[A-Za-z0-9._%-]+@[A-Za-z0-9._%-]+\\.[A-Za-z]{2,4}\\b/', $mailAddress);
}
function mail_send($val, $mail)
{
	@list($title, $mailTo, $mailTemplate) = explode(':', $val, 3);
	if ($mailTemplate) $mail = makeMail($mailTemplate, $mail);

	$a = array();
	mailAttachment('', $mailTo, $title, $mail, '', $a);
}

if (!function_exists('mime_content_type'))
{
	function mime_content_type($name){
		@$ext = strtolower(end(explode(".", $name)));
		switch($ext){
		case 'gif': return 'image/gif';
		case 'jpg': return 'image/jpg';
		case 'png': return 'image/png';
		}
		return 'application/octet-stream';
	}
}

function mailAttachment($email_from, $email_to, $email_subject, $message, $headers, &$attachment)
{
	//	Глобальные настройки
	$ini		= getCacheValue('ini');
	$globalIni	= getGlobalCacheValue('ini');
	//	Если кому не задано - отправить администратору
	if ($email_to == '') @$email_to = $ini[':mail']['mailTo'];
	if ($email_to == '') @$email_to = $globalIni[':mail']['mailTo'];
	if (!$email_to) return;
	
/*	$time 	= mktime();
	$date 	= date('d.m.Y',	$time);
	$timeSec= date('d.m.Y H:i:s', $time);
	$time 	= date('H-i', 	$time);
	$folder	= "log/$date";
	$file	= "$folder/$time.txt";
	makeDir($folder); 

	$log = "[$timeSec] from: $email_from\r\nto: $email_to\r\nsubject: $email_subject\r\n$message: $message\r\n\r\n";
	$f = fopen($file, 'a');
	fwrite($f, $log);
	fclose($f);
*/
	if (!mail_check('', $email_from)) $email_from = '';
	if (!$email_from) @$email_from = $ini[':mail']['mailFrom'];
	if (!$email_from) @$email_from = $globalIni[':mail']['mailFrom'];
	if (!$email_from) @$email_from = ini_get('sendmail_from');

	//	Если задан сервер - отправить через него
	if (@$globalIni[':mail']['smtp'])	ini_set("SMTP", $globalIni[':mail']['smtp']);
	if (@$ini[':mail']['smtp'])			ini_set("SMTP", $ini[':mail']['smtp']);

	$fileatt_type = "application/octet-stream"; // File Type
	
	$semi_rand = md5(time());
	$mime_boundary = $semi_rand;
	
	$headers .= "From: $email_from\nMIME-Version: 1.0\n" .
	"Content-Type: multipart/related;\n boundary=\"mixed-{$mime_boundary}\"";
	
	$email_message = "This is a multi-part message in MIME format.\n\n" .
	"--mixed-{$mime_boundary}\n";
	//	Plain text only
	if (!is_array($message)){
		$email_message .= "Content-Type:text/plain; charset=\"UTF-8\"\n" .
		"Content-Transfer-Encoding: 8bit\n\n$message\n\n";
	}else{//	HTML
		reset($message);
		$email_message .= "Content-Type:multipart/alternative; boundary=\"alt-{$mime_boundary}\"\n";
		while(list($type, $val)=each($message)){
			switch($type){
			case 'plain':
				$email_message .= "--alt-{$mime_boundary}\n" .
				"Content-Type:text/plain; charset=\"UTF-8\"\n" .
				"Content-Transfer-Encoding: 8bit\n\n$val\n\n";
			break;
			case 'html':
				$embedded 	=array();
				$val		= prepareHTML($val, $embedded);
				$email_message .= "--alt-{$mime_boundary}\n" .
				"Content-Type: multipart/related; boundary=\"related-{$mime_boundary}\"\n\n".
				"--alt-{$mime_boundary}\n" .
				"Content-Type:text/html; charset=\"UTF-8\"\n" .
				"Content-Transfer-Encoding: 8bit\n\n$val\n\n";
				
				foreach($embedded as $cid=>$filepath){
					$imageType	= mime_content_type($filepath);
					$inline		= chunk_split(base64_encode(file_get_contents($filepath)));
					$email_message .= "--related-{$mime_boundary}\n".
					"Content-Type: $imageType\n".
					"Content-Transfer-Encoding: base64\n".
					"Content-ID: <$cid>\n\n".
					"{$inline}".
					"--related-{$mime_boundary}--\n\n";
				}
//				echo '<pre>',htmlspecialchars($email_message), '</pre>'; die;
			break;
			}
		}
		$email_message .= "--alt-{$mime_boundary}--\n";

		reset($message);
		while(list($type, $val)=each($message)){
			if ($type != 'attach') continue;
			while(list($fileatt_name, $data)=each($val)){	
				$type = mime_content_type($fileatt_name);
				$data = chunk_split(base64_encode($data));
				$email_message .= "--mixed-{$mime_boundary}\n" .
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
	while(list($fileatt_name, $data)=each($attachment)){	
		$data = chunk_split(base64_encode($data));
		
		$email_message .= "--mixed-{$mime_boundary}\n" .
		"Content-Type: $fileatt_type;\n name=\"$fileatt_name\"\n" .
		"Content-Disposition: attachment;\n filename=\"$fileatt_name\"\n" .
		"Content-Transfer-Encoding: base64\n\n$data\n\n";
		unset($data);
	}
	$email_message .= "--mixed-{$mime_boundary}--";

	$val = split(';', $email_to);
	while(list(,$to) = each($val)){
		$to = trim($to);
		if (mail_check('', $to)){
			mail($to, $email_subject, $email_message, $headers);
		}
	}
}
function parseEmbeddedMailFn($matches){
	global $embeddedImage;
	$val	= $matches[2];
	if (!is_file($val)) return $val;
	$id		= md5($val);
	$embeddedImage[$id] = $val;
	return "$matches[1]cid:$id$matches[3]";
}
function prepareHTML($mail, &$embedded){
	global $embeddedImage;
	$embeddedImage	= array();;
	$mail			= preg_replace_callback('/(<img.*src=[\'"]?)([^\'"]+)([\'"]?)/i', parseEmbeddedMailFn, $mail);
	$embedded		= $embeddedImage;
	return $mail;
}

function parseMailFn($matches){
	global $dataForMail;
	$val	= $matches[1];

	$v1 = $v2	= '';
	list($v1, $v2) = explode('=', $val, 2);
	if ($v2){
		$val = $dataForMail[trim($v2)];
		return $val?$v1.$val:'';
	}
	
	return $dataForMail[trim($v1)];
}

function makeMail($templatePath, $data)
{
	global $dataForMail;
	$dataForMail = $data;
	
	@$mail	= file_get_contents($templatePath);
	$mail	= preg_replace_callback('#%([^%]+)%#', parseMailFn, $mail);
	
	$htmlFile	= "$templatePath.html";
	if (!is_file($htmlFile)) return $mail;
	
	$dataForMail = $data;
	@$htmlMail	= file_get_contents($htmlFile);
	$htmlMail	= preg_replace_callback('#{([^}]+)}#', parseMailFn, $htmlMail);
	
	return array('plain'=>$mail, 'html'=> $htmlMail);
}

?>