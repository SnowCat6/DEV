<?
define ('JPG_COMPRESS', 80);

function module_image($fn, $data)
{
	$fn = getFn("image_$fn");
	return $fn?$fn($data):NULL;
}
function image_unlink($path)
{

	unlink($path);			//	Удалить сам файл
	unlink("$path.shtml");	//	Удалить комментарий к файлу
	unlinkAutoFile($path);
	event('file.delete', $path);
}
//	Удалить файл со всеми возможными сопровождающими данными
function image_unlinkAutoFile($path)
{
	foreach(getThumbFiles($path) as $thumbPath)
	{
		unlink($thumbPath);			// Удалить миникартинку
		rmdir(dirname($thumbPath));	// Удалить пустую папку
	};
}

function image_display(&$property)
{
	$src	= $property['src'];
	$src	= getSiteFile($src);

	if (!$property['width'] && !$property['height'])
	{
		list($w, $h) = getimagesize($src);
		if (!$w || !$h) return false;
		
		$property['width']	= $w;
		$property['height']	= $h;
	}
	//	Lightbox zoom
	$property['src']	= globalRootURL . imagePath2local($src);
	if ($href = $property['href'])
	{
		$property2	= array();
		$copy		= array('href', 'rel', 'title', 'class');
		foreach($copy as $name){
			$property2[$name]	= $property[$name];
			$property[$name]	= '';
		}
		
		$property	= makeProperty($property);
		$property2	= makeProperty($property2);
		echo "<a $property2><img $property /></a>";
	}else{
		$property	= makeProperty($property);
		echo "<img $property />";
	}
	return $src;
}

function image_displayThumbImage(&$property)
{
	$src	= $property['src'];
	$w		= $property['width'];
	if (!is_array($w)) $w = explode('x', $w);
	if (count($w) == 1) $w = $w[0];
	
	$property['width']	= '';
	$property['height']	= '';
	
	$dir 		= dirname($src);
	list($file,)= fileExtension(basename($src));

	$wName = $w;
	if (is_array($w))
	{
		@list($w, $h) = $w;
		if (!list($iw, $ih) = getimagesize($src)) return;

		$wName	= $w.'x'.$h;
		$zoom	= ($iw>$ih)?$w/$iw:$h/$ih;
		if ($iw > $ih && $ih*$zoom < $h){
			$h	= 0;
			if ($iw <= $w) return image_display($property);
		}else{
			$w	= 0;
			if ($ih <= $h) return image_display($property);
		}
	}else $h = 0;

	$dst	= "$dir/thumb$wName/$file.jpg";

	if (!file_exists($dst) &&
		!image_resizeImage(array(
			'src'	=> $src,
			'dst'	=> $dst,
			'w'		=> $w,
			'h'		=> $h
		))) return;
	
	$property['src']	= $dst;
	return image_display($property);
}
//	Вывести картинку в виде уменьшенной копии, с наложением маски прозрачности (формат png)
function image_displayThumbImageMask(&$data)
{
	$src		= $data['src'];
	$dir		= dirname($src);
	list($file,)= fileExtension(basename($src));

	$maskFile	= $data[':mask'];
	$m 			= basename($maskFile, '.png');
	$dst 		= "$dir/thumb_$m/$file.jpg";
	$data['src']= $dst;

	//	Получаем размеры изображений
	$maskFile	= getSiteFile($maskFile);
	if (filemtime($dst) > filemtime($maskFile))
		return image_display($data);

	$mask		= imagecreatefrompng($maskFile);
	if (!$mask)	return false;
	
	//	Загружаем файл с маской
	$jpg	= loadImage($src);
	if (!$jpg) return false;
	
	$topOffset	= (int)$data[':offset']['top'];
	$w			= imagesx($mask);	$h = imagesy($mask);
	$iw			= imagesx($jpg);	$ih= imagesy($jpg);
	
	//	Определить соосность картинок, выбрать маску с нужной ориентацией
	if (($w < $h) != ($iw < $ih))
	{
		$dir	= dirname($maskFile);
		$file	= basename($maskFile);
		$rMask	= "$dir/r-$file";
		list($rw, $rh) = getimagesize($rMask);

		if ($rw && $rh){
			$mask	= @imagecreatefrompng($rMask);
			$w		= $rw; $h = $rh;
			$m 		= basename($rMask, '.png');
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
	//	Скопировать изображение
	$cx		= round(($cw-$w)/2);
/****************************/	
	//	СОздать базовую картинку
	$dimg	= imagecreatetruecolor($w, $h);
	imagecopyresampled($dimg, $jpg, 0, $topOffset, $cx, 0, $cw, $ch, $iw, $ih);
	//	Наложить маску
	imagecopy($dimg, $mask, 0, 0, 0, 0, $w, $h);
	//	Удалить временные картинки
	imagedestroy($mask);
	imagedestroy($jpg);
/**************************/	
	//	Сохранить картинку
	makeDir(dirname($dst));
	imagejpeg($dimg, $dst, JPG_COMPRESS);
	fileMode($dst);
	
	imagedestroy($dimg);

	//	Вывести на экран
	$data['width']	= $w;
	$data['height']	= $h;
	return image_display($data);
}

//	Вывести картинку в виде уменьшенной копии, с наложением маски прозрачности (формат png)
//	+function image_displayThumbImageClip
function image_displayThumbImageClip(&$data)
{
	list($w, $h)= is_array($data['width'])?$data['width']:explode('x', $data['width']);

	//	Вывести на экран
	$data['width']	= $w;
	$data['height']	= $h;
	$offset			= $data[':offset'];
	$data[':offset']= '';

	if (!$w || !$h)
		return image_displayThumbImage($data);

	$src		= $data['src'];
	$dir		= dirname($src);
	list($file,)= fileExtension(basename($src));
	$dst 		= "$dir/thumb_c$w"."x$h/$file.jpg";
	$data['src']= $dst;

	//	Получаем размеры изображений
	$existsFile	= getSiteFile($dst);
	if ($existsFile) return image_display($data);

	//	Загружаем файл с маской
	$jpg	= loadImage($src);
	if (!$jpg) return false;
	
	$iw			= imagesx($jpg);	$ih= imagesy($jpg);
	$topOffset	= (int)$offset['top'];

	//	Определяем конечные размеры картинки для масштабирования
	$zoom	= $w/$iw;
	$cw		= round($iw*$zoom); $ch = round($ih*$zoom);
	//	Если пропорции не совпадают, сменить плоскость масштабирования
	if ($cw < $w || $ch < $h){
		$zoom	= $h/$ih;
		$cw		= round($iw*$zoom); $ch = round($ih*$zoom);
	}
	//	Создать базовую картинку
	$dimg	= imagecreatetruecolor($w, $h);
	//	Скопировать изображение
	$cx		= round(($cw-$w)/2);
	
/*****************************/	
	imagecopyresampled($dimg, $jpg, 0, $topOffset, $cx, 0, $cw, $ch, $iw, $ih);
	imagedestroy($jpg);
/*****************************/	
	//	Сохранить картинку
	makeDir(dirname($dst));
	imagejpeg($dimg, $dst, JPG_COMPRESS);
	fileMode($dst);
	
	imagedestroy($dimg);

	return image_display($data);
}

//	Изменить размер файла
function image_resizeImage($data)
{
	$srcPath= $data['src'];
	$dstPath= $data['dst'];
	$w		= $data['w'];
	$h		= $data['h'];

	//	Задать путь для записи результата
	if (!$dstPath) $dstPath = $srcPath;
	//	Получит размер загруженного изображения
	@list($iw, $ih) = getimagesize($srcPath);
	if (!$iw || !$ih) return false;
	//	Прменить трансформацию
	//	Если установлены оба размера, изменить по минимальным размерам
	if ($w > 0 && $h > 0){
		$zoom	= ($iw>$ih)?$w/$iw:$h/$ih;
		$w		= $iw*$zoom;	$h = $ih*$zoom;
		if (!checkResize($srcPath, $dstPath, $iw, $ih, $w, $h)) return false;
		
		$jpg	= loadImage($srcPath);
		$iw		= imagesx($jpg); $ih = imagesy($jpg);
		$dimg	= imagecreatetruecolor($w, $h);
		$bgc	= imagecolorallocate ($dimg, 255, 255, 255);
		imagefilledrectangle ($dimg, 0, 0, $w, $h, $bgc);
		imagecopyresampled($dimg, $jpg, 0, 0, 0, 0, $w, $h, $iw, $ih);
		imagedestroy($jpg);
	}else
	//	Если установлена ширина, то сохранить пропорцию по высоте
	if ($w > 0)
	{
		$zoom	= $w/$iw;
		$w		= $iw*$zoom;	$h = $ih*$zoom;
		if (!checkResize($srcPath, $dstPath, $iw, $ih, $w, $h)) return false;
		
		$jpg	= loadImage($srcPath);
		$iw		= imagesx($jpg); $ih = imagesy($jpg);
		$dimg	= imagecreatetruecolor($w, $h);
		$bgc	= imagecolorallocate ($dimg, 255, 255, 255);
		imagefilledrectangle ($dimg, 0, 0, $w, $h, $bgc);
		imagecopyresampled($dimg, $jpg, 0, 0, 0, 0, $w, $h, $iw, $ih);
		imagedestroy($jpg);
	}else
	//	Если установлена высота, то сохранить пропорцию по ширине
	if ($h > 0)
	{
		$zoom	= $h/$ih;
		$w		= $iw*$zoom;	$h = $ih*$zoom;
		if (!checkResize($srcPath, $dstPath, $iw, $ih, $w, $h)) return false;
		
		$jpg	= loadImage($srcPath);
		$iw		= imagesx($jpg); $ih = imagesy($jpg);
		$dimg	= imagecreatetruecolor($w, $h);
		$bgc	= imagecolorallocate ($dimg, 255, 255, 255);
		imagefilledrectangle ($dimg, 0, 0, $w, $h, $bgc);
		imagecopyresampled($dimg, $jpg, 0, 0, 0, 0, $w, $h, $iw, $ih);
		imagedestroy($jpg);
	}else return false;


	makeDir(dirname($dstPath));
	list($file, $ext)	= fileExtension($dstPath);
	switch(strtolower($ext)){
	case 'jpg':	$b = imagejpeg($dimg,$dstPath, JPG_COMPRESS);	break;
	case 'png':	$b = imagepng($dimg, $dstPath);		break;
	case 'gif':	$b = imagegif($dimg, $dstPath);		break;
	default: return false;
	}
	fileMode($dstPath);

	return $b;
}
/****************************************/
function  loadImage($src)
{
	$img = NULL;
	list($file, $ext) = fileExtension($src);

	if (isMaxFileSize($src)){
		$src = getSiteFile('design/siteBigImage.gif');
		setNoCache();
	}
	
	switch(strtolower($ext))
	{
	case 'jpg':	@$img = imagecreatefromjpeg($src);	break;
	case 'png':	@$img = imagecreatefrompng($src);	break;
	case 'gif':	@$img = imagecreatefromgif($src);	break;
	}

	if (!$img) @$img = imagecreatefromjpeg($src);
	if (!$img) @$img = imagecreatefrompng($src);
	if (!$img) @$img = imagecreatefromgif($src);

	return $img;
}
function checkResize($src, $dst, $iw, $ih, $w, $h)
{
	if ($src==$dst && $iw==$w && $ih==h) return false;
	
	if ($src!=$dst && is_file($dst)){
		@list($iw, $ih)=getimagesize($dst);
		if ($iw==$w && $ih==h) return false;
	}
	return true;
}
function makeThumbFilePath($source, $thumbType)
{
	list($file, $ext) = fileExtension(basename($source));
	return dirname($source) . "/$thumbType/$file.jpg";
}
function getThumbFiles($source)
{
	$files	= array();
	//	Удалить все миникартинки файла
	foreach(getDirs(dirname($source), '^thumb') as $thumbPath)
	{
		$files[]	= makeThumbFilePath($source, basename($thumbPath));
	}
	return $files;
}
//	@return image resource
function makeImageThumb($sourceFileName, $size, $offset, $align = 'center|top')
{
	list($destWidth, $destHeight)	= explode('x', $size);
	list($offsetX, $offsetY)		= explode('x', $size);
	list($widthAlign, $heightAlign)	= explode('|', $align);
}
?>