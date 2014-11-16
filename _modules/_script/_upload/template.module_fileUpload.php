<? function module_fileUpload(&$val, &$data)
{
	$folder	= getValue('fileImagesPath');
	if (!$folder)	$folder = getValue('fileImagesPathFull');
	$folder	= makeFilePath($folder);
	
	switch($val){
	case 'get':
		setTemplate('');
		$result		= array();
		$folders	= getDirs($folder);
		foreach($folders as $group => $path)
		{
			$files	= getFiles($path, '(jpg|png|gif)$');
			foreach($files as $file=>$path)
			{
				$size	= getimagesize($path);
				$size	= "$size[0]x$size[1]";
				$comment	= file_get_contents("$path.shtml");
				$name		= file_get_contents("$path.name.shtml");
				
				$result[$group][$file]	= array(
					'path'	=> $path,
					'size'	=> $size,
					'name'		=> "$name",
					'comment'	=> "$comment"
				);
			};
		}
		
		echo json_encode($result);
	break;
	case 'upload':
		setTemplate('');
		
		$result		= array();
		$files		= $_FILES['imageFieldUpload'];
		foreach($files['name'] as $ix => $file)
		{
			$fileName	= makeFileName($file);
			$filePath	= "$folder/$fileName";
			
			if (!canEditFile($filePath)){
				$result[$fileName]	= array(
					'error' => "Error upload file '$filePath', no write access"
					);
				continue;
			}
			
			$src		= $files['tmp_name'][$ix];
			if (!is_file($src)) continue;
			
			$bFileExists= is_file($filePath);
			if (copy2folder($src, $filePath))
			{
				$w = $h = 0;
				list($w, $h) = getimagesize($filePath);
				
				$result[$fileName]	= array(
					'path'=>	imagePath2local($filePath),
					'size'=>	filesize($filePath),
					'date'=>	date('d.m.Y H:i', filemtime($filePath)),
					'dimension'=>"$w x $h",
					'action'=>	$bFileExists?'replace':'new'
				);
				if (isFileTitle($filePath)) break;
			}else{
				$result[$fileName]	= array(
					'error' => "Error upload file '$filePath'"
					);
			}
		}
		echo json_encode($result);
	break;
	case 'delete':
		setTemplate('');
		$delete	= getValue('delete');
		if (!is_array($delete)) $delete = array();
		if ($folder) $delete[] = $folder;
		
		$result		= array();
		foreach($delete as $folder)
		{
			$folder	= makeFilePath($folder);
			if (canEditFile($folder)){
				unlinkFile($folder);
				$result['result']	= array();
			}else{
				$result['result']	= array(
					'error' => "Error delete file '$folder', no write access"
				);
			}
		}
		echo json_encode($result);
	break;
	}
}
?>