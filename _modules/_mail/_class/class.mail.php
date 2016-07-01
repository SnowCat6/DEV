<?
class cmsMail
{
static function send($email_from, $email_to, $email_subject, $message, $headers = '')
{
	if (!$email_to)		return "Нет адреса получателя.";
	if (!$email_from)	return "Нет адреса отправителя.";

	moduleEx('prepare:2fs', $message);

	//	Глобальные настройки
	$ini		= getIniValue(':mail');
	$globalIni	= getGlobalCacheValue('ini');

	//	Если задан сервер - отправить через него
	@$smtp	= $ini['SMTP'];
	if (!$smtp)	@$smtp = $globalIni[':mail']['SMTP'];
	if ($smtp)	ini_set("SMTP", $smtp);

	$email_subject	= '=?utf-8?B?'.base64_encode($email_subject).'?=';
	$mimeMessage	= array();
	
	if (!is_array($message)) $message = array('plain' => $message);
	
	$altMime	= array();
	foreach($message as $type => $val)
	{
		switch($type)
		{
		case 'plain':
			$altMime[] = 
				"Content-Type: text/plain; charset=\"UTF-8\"\n" .
				"Content-Transfer-Encoding: base64\n\n".
				chunk_split(base64_encode(trim($val)));
			break;
			
		case 'html':
			$templ	= file_get_contents(getSiteFile("design/mailPage.html"));
			if ($templ) $val = str_replace('{%}', $val, $templ);

			$embedded 	= array();
			$altRelated	= array();

			$altRelated[] = 
				"Content-Type: text/html; charset=\"UTF-8\"\n" .
				"Content-Transfer-Encoding: base64\n\n".
				chunk_split(base64_encode(trim(self::prepareHTML($val, $embedded))));

			foreach($embedded as $cid => $filepath)
			{
				$imageType		= self::mimeType($filepath);
				$altRelated[]	=
					"Content-Type: $imageType\n".
					"Content-Transfer-Encoding: base64\n".
					"Content-ID: <$cid>\n\n".
					chunk_split(base64_encode(file_get_contents($filepath)));
			}
			
			if (count($altRelated) > 1) $altMime[] = self::packMime('multipart/related', $altRelated);
			else $altMime[]	= $altRelated[0];
			
			break;
			
		case 'SMS':
			$SMSTO	= $message[':mailTo']['SMS'];
			if (!$SMSTO) $SMSTO	= $ini['SMS_MAIL'];
			if (!$SMSTO) break;
			
			$SMSHEADER	= 	"From: $email_from\r\n".
							"MIME-Version: 1.0\r\n".
							"Content-Type: text/plain; charset=utf-8";

			self::sendRAW($SMSTO, $email_subject, $val, $SMSHEADER);
			break;
		}
	}

	if ($altMime)
		$mimeMessage[]	= self::packMime('multipart/alternative', $altMime);

	/********************************************** First File ********************************************/

	$files	= $message[':attach'];
	if (!is_array($files)) $files = array();
	
	foreach($files as $fileName => $data)
	{
		$type = mimeType($fileName);
		$mimeMessage[] = 
			"Content-Type: $type;\n name=\"$fileName\"\n" .
			"Content-Disposition: attachment;\n filename=\"$fileName\"\n" .
			"Content-Transfer-Encoding: base64\n\n" .
			chunk_split(base64_encode($data));
	}

	/********************************************** First File ********************************************/

	if (!$mimeMessage) return;

	$boundary 	= 'mixed-' . md5(rand());;
	$endBoundary= "\n\n--$boundary\n";

	$headers .= 
		"From: $email_from\n".
		"MIME-Version: 1.0\n" .
		"Content-Type: multipart/mixed; boundary=\"$boundary\"\n";
		
	$email_message =  
		"This is a multi-part message in MIME format." .
		$endBoundary .
		implode($endBoundary, $mimeMessage) .
		"\n\n--$boundary--\n";

	return self::sendRAW($email_to, $email_subject, $email_message, $headers);
}
/********************/
	static function sendRAW($email_to, $email_subject, $email_message, $headers)
	{
		$bOK = '';
		$val = explode(';', $email_to);
		foreach($val as $to)
		{
			$to = trim($to);
			$to	= self::parseMailAddress($to);
			if (!self::checkValid($to)) continue;
			if (mail($to, $email_subject, $email_message, $headers) == true) continue;

			$error	= error_get_last();
			if ($error['type'] == 8) continue;

			$error	= $error['message'];
			$bOK	.="$error\r\n";
		}
		return $bOK;
	}
/********************/
	static function prepareHTML($mail, &$embedded)
	{
		$mail		= preg_replace_callback('/(<img.*src\s*=\s*[\'"]?)([^\'"]+)([\'"]?)/i', 
		function($matches) use (&$embedded)
		{
			$val	= $matches[2];
			if (!is_file($val)) return $val;
			
			$id				= md5($val);
			$embedded[$id]	= $val;
			return "$matches[1]cid:$id$matches[3]";
		}, $mail);
	
		return $mail;
	}
/********************/
	static function checkValid($mailAddress){
		$mailAddress	= self::parseMailAddress($mailAddress);
		return preg_match('/\\b[A-Za-z0-9._%-]+@[A-Za-z0-9._%-]+\\.[A-Za-z]{2,4}\\b/', $mailAddress);
	}
	static function parseMailAddress($mail)
	{
		if (!preg_match('#<(.*)>#', $mail, $val)) return $mail;
		return $val[1];
	}
	static function mimeType($name)
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

	static function packMime($contentType, $mimeParts)
	{
		$boundary	= md5(rand());
		$endBoundary= "\n\n--$boundary\n";
		$message 	=
			"Content-Type: $contentType; boundary=\"$boundary\"\n" .
			$endBoundary .
			implode($endBoundary, $mimeParts) .
			"\n\n--$boundary--\n";
		return $message;
	}
};
?>