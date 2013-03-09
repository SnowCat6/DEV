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
function utf8_to_win($string){
	$out = '';
	for ($c=0;$c<strlen($string);$c++){
		$i=ord($string[$c]);
		if ($i <= 127) @$out .= $string[$c];
		if (@$byte2){
			$new_c2=($c1&3)*64+($i&63);
			$new_c1=($c1>>2)&5;
			$new_i=$new_c1*256+$new_c2;
			if ($new_i==1025){
				$out_i=168;
			} else {
				if ($new_i==1105){
					$out_i=184;
				} else {
					$out_i=$new_i-848;
				}
			}
			@$out .= chr($out_i);
			$byte2 = false;
		}
		if (($i>>5)==6) {
			$c1 = $i;
			$byte2 = true;
		}
	}
	return $out;
}
//	Нормализовать путь к файлу, преобразовать в транслит
function makeFileName($name, $fromUTF = false){
/*
	if ($fromUTF){
		if (function_exists('iconv')) $name = iconv('UTF-8', 'windows-1251', $name);
		elseif (function_exists('mb_convert_encoding')) $name = mb_convert_encoding($name, 'UTF-8', 'windows-1251');
		else $name = utf8_to_win($name);
	}
*/	module('module_translit', &$name);
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

//	Удалить дерево директорий с файлами
function delTree($dir, $bRemoveBase = true, $bUseRename = false)
{
	$dir	= rtrim($dir, '/');
	if ($bUseRename){
		$rdir	= "$dir.del";
		@rename($dir, $rdir);
		if (!$bRemoveBase) makeDir($dir);
		$dir	= $rdir;
	}

	@$d		= opendir($dir);
	if (!$d) return;
	
	while(($file = readdir($d)) != null){
		if ($file == '.' || $file == '..') continue;
		$file = "$dir/$file";
		if (is_file($file))	unlink($file);
		else
		if (is_dir($file)) delTree($file, true, false);
	}
	@closedir($d);
	if ($bRemoveBase || $bUseRename) @rmdir($dir);
}

?>