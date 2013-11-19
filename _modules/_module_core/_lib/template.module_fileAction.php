<?
function module_fileAction(&$bClearBaseDir, &$baseDir){
	return modFileAction($baseDir, $bClearBaseDir);
}
////////////////////////////
//	Обработка комманд файлов
function modFileAction($baseDir, $bClearBaseDir = false)
{
	$clear	= false;
	$modFile= getValue('modFile');
	removeSlash($modFile);
	$baseDir .= '/';

	//	Файлы для удаления:
	//	Список: <input type="checkbox" name="modFile[files][]" value="file name'>
	//	Кнопка: <input type="submit" name="modFile[delButton]">
	$delFiles = array();
	if (!@$modFile) $modFile = array();
	
	if (@$modFile['delButton'] && @is_array($modFile['files']))
		$delFiles = array_merge($delFiles, $modFile['files']);
		
	if (@is_array($modFile['delete']))
		$delFiles = array_merge($delFiles, $modFile['delete']);

	if ($delFiles){
//	print_r($delFiles);
		//	Просмотреть список папок с файлами
		while(@list($folder, $val)=each($delFiles)){
			//	Удалить файл из папки
			if (is_array($val)){
				while(list($ndx, $file)=each($val)){
					if (is_int($ndx))
						unlinkFile($baseDir.normalFilePath("$folder/$file"));
					else
						unlinkFile($baseDir.normalFilePath("$folder/$ndx"));
					$clear = true;
				}
			}else{
				unlinkFile($baseDir.normalFilePath("$folder/$val"));
				$clear = true;
			}
		}
	}

	//	Файлы для загрузки:
	//	Список: <input type="file" name="modFileUpload[folder name]['' или 'file name']">
	@$fileUpload = $_FILES['modFileUpload'];
	$bFirstEntry = true;
	//	Просмотреть названия файлов для загрузки по именам
	while(@list($folder, $val)=each($fileUpload['name'])){
		//	Получить индекс файла и его реальное имя
		while(list($ndx, $srcName)=each($val)){
			//	Получить временное имя файла на компьютере
			$tmp = $fileUpload['tmp_name'][$folder][$ndx];
			//	Если файл не закачен, то пропустить
			if (!$tmp) continue;
			//	Ограничить размер заливаемого файла
//			if (!is_writer() && filesize($tmp) > 5*1024*1024) continue;
			//	Если индекс файла не цифра а текстовое поле, то присвоить новое имя файла
			if (!is_int($ndx) && (int)$ndx==0){
				$ext = explode('.', $srcName);
				$ext = strtolower(array_pop($ext));
				$srcName="$ndx.$ext";
			}
			$path = "$folder/$srcName";
			//	Удалить предыдущий файл с таким же названием, если он есть
			unlinkFile($baseDir.normalFilePath($path));
			//	Убрать все левые символы
			$srcName= normalFilePath(makeFileName($srcName));
			$path 	= $baseDir.normalFilePath("$folder/$srcName");
			//	Удалить папку назначения, если задано
			if ($bFirstEntry && $bClearBaseDir){
				$bFirstEntry = false;
				delTree(dirname($path));
			}
			//	Создать папку для размещения файла
			if (is_file(dirname($path))) @unlink(dirname($path));
			createFileDir(dirname($path));
			//	Переместить файл
//			echo $tmp, ' ', $path;
			move_uploaded_file($tmp, $path);
			//	Задать аттрибуты доступа на чтение
			fileMode($path);
			//	Добавить в список отмеченных файлов
			$modFile['files'][$folder][]=$srcName;
			$clear = true;
		}
	}
	//	Файлы для изменения размеров
	//	Список: <input type="checkbox" name="modFile[files][]" value="file name'>
	//	Кнопка: <input type="submit" name="modFile[sizeButton]">
	if (@$modFile['sizeButton']){
		//	Просмотреть все папки с файлами
		@reset($modFile['files']);
		while(@list($folder, $val)=each($modFile['files'])){
			//	Изменить размер каждого файла по зпдпнным параметрам
			while(list($ndx, $file)=each($val)){
				$file = "$baseDir/".normalFilePath("$folder/$file");
				resizeImage($file, $modFile['sizeW'], $modFile['sizeH']);
				$clear = true;
			}
		}
	}
	//	Установить комментарии для файлов, или нажата кнопка или имеется комментарий
	//	Список: <input type="checkbox" name="modFile[files][]" value="file name'>
	//	Комменткарий: <input type="text" name="modFile[comment]">
	//	Кнопка: <input type="submit" name="modFile[commentButton][?file]">
	if (@$modFile['commentButton'] || @$modFile['comment']){
//		@$modFile['comment'] = stripslashes($modFile['comment']);
		//	Просмотреть все папки с файлами
		@reset($modFile['files']);
		while(@list($folder, $val)=each($modFile['files'])){
			$path = "$baseDir/$folder";
			//	Просмотреть каждый файл
			while(list($ndx, $fileName)=each($val)){
				$file = normalFilePath("$path/$fileName");
				//	Если файла нет, то пропустить
				if (!is_file($file)) continue;
				//	Если есть комментарий, то задать новый иначе удалить файл
				if (is_array($modFile['comment'])){
					if (!isset($modFile['comment'][$fileName])) continue;
					$comment = $modFile['comment'][$fileName];
				}else $comment = $modFile['comment'];
				
				if ($comment) file_put_contents_safe("$file.shtm", $comment);
				else @unlink("$file.shtm");
	
				$clear = true;
			}
		}
	}
	return $clear;
}
?>