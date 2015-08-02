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
//	Сделать из пути файла (полный или сокращенный), полнуй путь к файлу
function makeFilePath($folder)
{
	if (is_array($folder)){
		foreach($folder as &$p) $p = makeFilePath($p);
		return $folder;
	}

	$folder	= localRootPath . '/' . imagePath2local($folder);
	return normalFilePath($folder);
}

//	Определить, можно ли редактировать папку с файлами или файл
function canEditFile($path)
{
	if (is_array($path)){
		foreach($path as $p){
			if (!canEditFile($p)) return false;
		}
		return true;
	}
	//	не пользователь не может загружать файлы
	if (!userID()) return false;
	//	Начало пути должно быть папкой с изображениями
	if (strncmp($path, images, strlen(images)) != 0) return false;
	//	Проверить доступ к файлу в модулях
	return access('write', "file:$path");
}
//	Определить ,что файл можно прочитать
function canReadFile($path){
	return true;
}
//	Получить расширение файла
function fileExtension($path)
{
	$file = explode('.', $path);
	$ext = array_pop($file);
	return array(implode('.', $file), $ext);
}
?>