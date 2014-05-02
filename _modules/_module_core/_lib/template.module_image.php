<? function module_image(&$fn, &$data){
	$fn = getFn("image_$fn");
	return $fn?$fn($data):NULL;
}
function image_unlink(&$path)
{
	@unlink($path);			//	Удалить сам файл
	@unlink("$path.shtml");	//	Удалить комментарий к файлу
	unlinkAutoFile($path);
	event('image.delete', $path);
}
//	Удалить файл со всеми возможными сопровождающими данными
function image_unlinkAutoFile(&$path){
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
function image_displayThumbImage(&$data)
{
	$src	= $data['src'];
	$w		= $data['w'];
	$options= $data['options'];
	$altText= $data['altText'];
	$rel	=$data['rel'];
	$showFullUrl	=$data['showFullUrl'];

	$dir = dirname($src);
	list($file,) = fileExtension(basename($src));
	$wName = $w;
	if (is_array($w)){
		@list($w, $h) = $w;
		if (!list($iw, $ih) = getimagesize($src)) return;

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
	$options	.= " alt=\"$altText\" title=\"$altText\"";
	
	$ctx = "<img src=\"$dst\" width=\"$w\" height=\"$h\"$options />";
	if ($showFullUrl) showPopupImage($src, $showFullUrl, $ctx, $altText, $rel);
	else echo $ctx;
	
	return $dst;
}
//	Вывести картинку в виде уменьшенной копии, с наложением маски прозрачности (формат png)
function image_displayThumbImageMask(&$data)
{
	$src	= $data['src'];
	$options= $data['options'];
	$altText= $data['altText'];
	$rel	=$data['rel'];
	$maskFile		= $data['maskFile'];
	$showFullUrl	=$data['showFullUrl'];

	$maskFile	= cacheRootPath."/$maskFile";
	$dir		= dirname($src);
	list($file,)= fileExtension(basename($src));

	$m 		= basename($maskFile, '.png');
	$dst 	= "$dir/thumb_$m/$file.jpg";

	//	Если файла с маской нет, сделать его
	@list($w, $h) = getimagesize($dst);
	if (!$w || !$h){
		//	Получаем размеры изображений
		$mask = imagecreatefrompng($maskFile);
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
				$mask	= @imagecreatefrompng($rMask);
				$w		= $rw; $h = $rh;
				$m 		= basename($rMask, '.png');
			}
		}

		$maskCutFile= dirname($maskFile)."/$m.cut.png";;
		$cut		= NULL;	//	imagecreatefrompng($maskCutFile);

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
		if ($cut){
//			imagecopy($dimg, $cut, 0, 0, 0, 0, $w, $h);
//			imagealphablending($dimg, false);
//			imagesavealpha($dimg, true);
		}
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
	$options .= " alt=\"$altText\" title=\"$altText\"";
	
	$ctx =  "<img src=\"$dst\" width=\"$w\" height=\"$h\"$options />";
	if ($showFullUrl) showPopupImage($src, $showFullUrl, $ctx, $altText, $rel);
	else echo $ctx;
	return $d;
}

//	Изменить размер файла
function image_resizeImage(&$data)
{
	$srcPath= $data['srcPath'];
	$w		= $data['w'];
	$h		= $data['h'];
	$dstPath= $data['dstPath'];

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

?>