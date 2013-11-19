<?
//	Нормализовать путь к файлу, преобразовать в транслит
function makeFileName($name, $fromUTF = false){
	moduleEx('translit', $name);
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