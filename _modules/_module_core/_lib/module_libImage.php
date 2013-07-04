<?
if (!extension_loaded('gd'))	dl('gd.so') || dl('gd2.dll');
if (function_exists('imagecreatetruecolor')) 
	define('gd2', true);
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

//////////////////////////////////////////////////////
//	Различные общие функции
/////////////////////////////////////////////////////
function isMaxFileSize($path)
{
	if (!$path) return true;
	m("message:trace", "Read image $path");

	if (!defined('gd2')) return true;
	@list($w,$h) = getimagesize($path);
	if (!$w || !$h) return true;
	if ($w*$h < 1500*1500*3) return false;

	m("message:error", "Big image size $path");
	return true;
}
//	Изменить размер файла
function resizeImage($srcPath, $w, $h, $dstPath='')
{
	if (isMaxFileSize($srcPath)) return false;
	//	Задать путь для записи результата
	if (!$dstPath) $dstPath = $srcPath;
	//	Получит размер загруженного изображения
	@list($iw, $ih) = getimagesize($srcPath);
	if (!$iw || !$ih) return false;
	//	Прменить трансформацию
	//	Если установлены оба размера, изменить по минимальным размерам
	if ($w > 0 && $h > 0){
		$zoom = ($iw>$ih)?$w/$iw:$h/$ih;
		$w = $iw*$zoom;	$h = $ih*$zoom;
		if (!checkResize($srcPath, $dstPath, $iw, $ih, $w, $h)) return false;
		$jpg = loadImage($srcPath);
		$dimg= imagecreatetruecolor($w, $h);
		$bgc = imagecolorallocate ($dimg, 255, 255, 255);
		imagefilledrectangle ($dimg, 0, 0, $w, $h, $bgc);
		imagecopyresampled($dimg, $jpg, 0, 0, 0, 0, $w, $h, $iw, $ih);
	}else
	//	Если установлена ширина, то сохранить пропорцию по высоте
	if ($w > 0){
		$zoom = $w/$iw;
		$w = $iw*$zoom;	$h = $ih*$zoom;
		if (!checkResize($srcPath, $dstPath, $iw, $ih, $w, $h)) return false;
		$jpg = loadImage($srcPath);
		$dimg = imagecreatetruecolor($w, $h);
		$bgc = imagecolorallocate ($dimg, 255, 255, 255);
		imagefilledrectangle ($dimg, 0, 0, $w, $h, $bgc);
		@imagecopyresampled($dimg, $jpg, 0, 0, 0, 0, $w, $h, $iw, $ih);
	}else
	//	Если установлена высота, то сохранить пропорцию по ширине
	if ($h > 0){
		$zoom = $h/$ih;
		$w = $iw*$zoom;	$h = $ih*$zoom;
		if (!checkResize($srcPath, $dstPath, $iw, $ih, $w, $h)) return false;
		$jpg = loadImage($srcPath);
		$dimg = imagecreatetruecolor($w, $h);
		$bgc = imagecolorallocate ($dimg, 255, 255, 255);
		imagefilledrectangle ($dimg, 0, 0, $w, $h, $bgc);
		@imagecopyresampled($dimg, $jpg, 0, 0, 0, 0, $w, $h, $iw, $ih);
	}else return false;

	makeDir(dirname($dstPath));
	list($file, $ext)=fileExtension($dstPath);
	switch(strtolower($ext)){
	case 'jpg':	$b = imagejpeg($dimg,$dstPath, 90);	break;
	case 'png':	$b = imagepng($dimg, $dstPath);		break;
	case 'gif':	$b = imagegif($dimg, $dstPath);		break;
	default: return false;
	}
	chmod($dstPath, 0755);
	makeDir(dirname($dstPath));
	return $b;
}
function checkResize($src, $dst, $iw, $ih, $w, $h){
	if ($src==$dst && $iw==$w && $ih==h) return false;
	if ($src!=$dst && is_file($dst)){
		@list($iw, $ih)=getimagesize($dst);
		if ($iw==$w && $ih==h) return false;
	}
	return true;
}
function  loadImage($src)
{
	list($file, $ext) = fileExtension($src);
	$img = NULL;
	switch(strtolower($ext)){
	case 'jpg':	@$img = imagecreatefromjpeg($src);	break;
	case 'png':	@$img = imagecreatefrompng($src);	break;
	case 'gif':	@$img = imagecreatefromgif($src);	break;
	}
	if (!$img) @$img = imagecreatefromjpeg($src);
	if (!$img) @$img = imagecreatefrompng($src);
	if (!$img) @$img = imagecreatefromgif($src);
	return $img;
}
function createFileDir($path){
	$dir='';
	$path=explode('/',str_replace('\\', '/', $path));
	while(list(,$name)=each($path))	@mkdir($dir.="$name/");
}
//	Получить список файлов по фильтру
function getFileList($dir, $filter, $isFiles=true){
	@$d=opendir($dir);
	$files = array();
	while((@$file=readdir($d))!=NULL){
		$f = "$dir/$file";
		if (!preg_match("#$filter#", $file)) continue;
		if ($isFiles){
			if (!is_file($f)) continue;
		}else{
			if ($file=='.' || $file=='..' || !is_dir($f)) continue;
		}
		$files[$file]=$f;
	}
	@closedir($d);
	ksort($files);
	return $files;
}
//	Удалить файл со всеми возможными сопровождающими данными
function unlinkAutoFile($path){
	//	Удалить расширение файла
	list($file,) = fileExtension(basename($path));
	//	Получтить все папки с миникартинками
	$path = dirname($path);
	$thumbs = getFileList($path, '^thumb', false);
	//	Удалить все миникартинки файла
	while(list($ndx, $path)=each($thumbs)){
		@unlink("$path/$file.jpg");	// Удалить миникартинку
		@rmdir($path);				// Удалить пустую папку
	}
}
function unlinkFile($path){
	@unlink($path);			//	Удалить сам файл
	@unlink("$path.shtml");	//	Удалить комментарий к файлу
	unlinkAutoFile($path);
}
//	Получить расширение файла
function fileExtension($path){
	$file = explode('.', $path);
	$ext = array_pop($file);
	return array(implode('.', $file), $ext);
}
//	
function displayThumbImage($src, $w, $options='', $altText='', $showFullUrl='', $rel='')
{
	if (isMaxFileSize($src)) return false;

	$dir = dirname($src);
	list($file,) = fileExtension(basename($src));
	$wName = $w;
	if (is_array($w)){
		@list($w, $h) = $w;
		if (!@list($iw, $ih) = getimagesize($src)) return;

		$wName= $w.'x'.$h;
		$zoom = ($iw>$ih)?$w/$iw:$h/$ih;
		if ($iw > $ih && $ih*$zoom < $h){
			$h = 0;
			if ($iw <= $w) return displayImage($src, $options, $altText);
		}else{
			$w = 0;
			if ($ih <= $h) return displayImage($src, $options, $altText);
		}
	}else $h = 0;
	
	$dst = "$dir/thumb$wName/$file.jpg";
	if (!file_exists($dst) && !resizeImage($src, $w, $h, $dst)) return false;
	
	list($w, $h) = getimagesize($dst);

	$dst 	= imagePath2local($dst);
	$dst	= htmlspecialchars($dst);
	if (!$altText) $altText = @file_get_contents("$src.shtm");
	$altText	= htmlspecialchars($altText);
	$options	.= " alt=\"$altText\"";
	
	$ctx = "<img src=\"$dst\" width=\"$w\" height=\"$h\"$options />";
	if ($showFullUrl) showPopupImage($src, $showFullUrl, $ctx, $altText, $rel);
	else echo $ctx;
	
	return $dst;
}
//	Вывести картинку в виде уменьшенной копии, с наложением маски прозрачности (формат png)
function displayThumbImageMask($src, $maskFile, $options='', $altText='', $showFullUrl='', $rel='')
{
	if (isMaxFileSize($src)) return false;

	$maskFile	= localCacheFolder."/siteFiles/$maskFile";
	$dir		= dirname($src);
	list($file,) = fileExtension(basename($src));
	$m 		= basename($maskFile, '.png');
	$dst 	= "$dir/thumb_$m/$file.jpg";
	//	Если файла с маской нет, сделать его
	@list($w, $h) = getimagesize($dst);
	if (!$w || !$h){
		//	Получаем размеры изображений
		$mask = @imagecreatefrompng($maskFile);
		if (!$mask)	return false;
		
		//	Загружаем файл с маской
		$jpg = loadImage($src);
		if (!$jpg) return false;
		
		$w = imagesx($mask);$h = imagesy($mask);
		$iw= imagesx($jpg);	$ih= imagesy($jpg);
		
		//	Определить соосность картинок, выбрать маску с нужной ориентацией
		if (($w < $h) != ($iw < $ih)){
			$dir = dirname($maskFile);
			$file= basename($maskFile);
			$rMask = "$dir/r-$file";
			@list($rw, $rh) = getimagesize($rMask);

			if ($rw && $rh){
				$mask = @imagecreatefrompng($rMask);
				$w = $rw; $h = $rh;
			}
		}
		//	Определяем конечные размеры картинки для масштабирования
		$zoom	= $w/$iw;
		$cw		= round($iw*$zoom); $ch = round($ih*$zoom);
		//	Если пропорции не совпадают, сменить плоскость масштабирования
		if ($cw < $w || $ch < $h){
			$zoom	= $h/$ih;
			$cw		= round($iw*$zoom); $ch = round($ih*$zoom);
		}
		//	СОздать базовую картинку
		$dimg = imagecreatetruecolor($w, $h);
		//	Скопировать изображение
		$cx = round(($cw-$w)/2);
		imagecopyresampled($dimg, $jpg, 0, 0, $cx, 0, $cw, $ch, $iw, $ih);
		//	Наложить маску
		imagecopy($dimg, $mask, 0, 0, 0, 0, $w, $h);
		//	Сохранить картинку
		makeDir(dirname($dst));
		imagejpeg($dimg, $dst, 90);

		chmod($dst, 0755);
		makeDir(dirname($dst));
		
		//	Удалить временные картинки
		imagedestroy($mask);
		imagedestroy($jpg);
		imagedestroy($dimg);
	}
	//	Вывести на экран
	$dst 	= imagePath2local($dst);

	$d = $dst = htmlspecialchars($dst);
	if (!$altText) $altText = @file_get_contents("$src.shtm");
	$altText = htmlspecialchars($altText);
	$options .= " alt=\"$altText\"";
	
	$ctx =  "<img src=\"$dst\" width=\"$w\" height=\"$h\"$options />";
	if ($showFullUrl) showPopupImage($src, $showFullUrl, $ctx, $altText, $rel);
	else echo $ctx;
	return $d;
}
function displayImage($src, $options='', $altText=''){
	if (isMaxFileSize($src)) return false;

	@list($w, $h) = getimagesize($src);
	if (!$w || !$h) return false;

	$src 	= imagePath2local($src);
	$altText= htmlspecialchars($altText);
	$altText= " alt=\"$altText\"";
	echo "<img src=\"$src\" width=\"$w\" height=\"$h\"$altText$options />";
	return true;
}
function showPopupImage($src, $showFullUrl, $ctx, $alt='', $rel='')
{
	module('script:lightbox');
	$rel 		= $rel?"lightbox[$rel]":'lightbox';
	$showFullUrl= imagePath2local($showFullUrl);
	echo "<a href=\"$showFullUrl\" class=\"zoom\" title=\"$alt\" target=\"image\" rel=\"$rel\">", $ctx, "<span></span></a>";
}
function imagePath2local($src){
	$src		= str_replace(globalRootURL.'/'.localHostPath.'/',	'', globalRootURL."/$src");
	$src		= str_replace('/'.localHostPath.'/', 				'', globalRootURL."/$src");
	return $src;
}
function clearThumb($folder){

	$files = getFileList($folder, '^thumb', false);
	while(list(,$path)=each($files)) delTree($path);
	
	$files = getFileList($folder, '', false);
	while(list(,$path)=each($files)) clearThumb($path);
}
?>