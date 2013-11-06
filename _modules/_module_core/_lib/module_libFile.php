<?
function echoEncode($value){
	echo $value;//iconv("windows-1251", "utf-8", $value);
}
//	Вывести на экран массив, как XML документ
//	Использовать знак '@' в дочерних нодах для записи как аттрибуты
function writeXML(&$xml, $date = NULL){
	// Prevent the browser from caching the result.
	if (!$date){
		// Date in the past
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT') ;
		// HTTP/1.1
		header('Cache-Control: no-store, no-cache, must-revalidate') ;
		header('Cache-Control: post-check=0, pre-check=0', false) ;
		// HTTP/1.0
		header('Pragma: no-cache') ;
		// always modified
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT') ;
	}else{
		//	Дата изменения
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $date) . ' GMT') ;
	}
	// Set the response format.
	header( 'Content-Type:text/xml; charset=UTF-8' ) ;
	echoEncode('<?xml version="1.0" encoding="UTF-8"?>');

	writeXMLtag($xml);
}

function writeXMLtag(&$xml){
//	while(list($tag, $child)=each($xml)){
	foreach($xml as $tag => &$child){
		if (is_int($tag)){
			writeXMLtag($child);
			continue;
		}
		if (!is_array($child)){
			if ($tag[0] == '!'){
				$tag = substr($tag, 1);
				echoEncode("<$tag><![CDATA[$child]]></$tag>");
			}else{
				echoEncode("<$tag>".htmlspecialchars($child)."</$tag>");
			}
			continue;
		}
		
		$tags = array();
		echoEncode("<$tag");
//		while(list($name, $value)=each($child)){
		foreach($child as $name => &$value){
			if ($name[0] != '@'){
				$tags[$name] = $value;
				continue;
			}
			$name	= substr($name, 1);
			$name	= $name;
			$valu	= htmlspecialchars($value);
			echoEncode(" $name=\"$value\"");
		}
		if ($tags){
			echoEncode(">");
			writeXMLtag($tags);
			echoEncode("</$tag>");
		}else echoEncode("/>");
	}
}
//	Нормализовать путь к файлу, преобразовать в транслит
function makeFileName($name, $fromUTF = false){
/*
	if ($fromUTF){
		if (function_exists('iconv')) $name = iconv('UTF-8', 'windows-1251', $name);
		elseif (function_exists('mb_convert_encoding')) $name = mb_convert_encoding($name, 'UTF-8', 'windows-1251');
		else $name = utf8_to_win($name);
	}
*/	moduleEx('translit', $name);
	$name = urlencode($name);
	return preg_replace('#%[0-9A-Fa-f]{2}#', '-', $name);
}
//	Нормальизовать путь
function normalFilePath($name){
	$name = preg_replace('#[.]{2,}#', '.', $name);
	$name = preg_replace('#[/]{2,}#', '/', $name);
	$name = preg_replace('#[./]{2,}#','',  $name);
	return trim($name, '/');
}
//	Определить, можно ли редактировать папку с файлами или файл
function canEditFile($path){
	return true;
}
//	Определить что файл можно прочитать
function canReadFile($path){
	return true;
}


?>