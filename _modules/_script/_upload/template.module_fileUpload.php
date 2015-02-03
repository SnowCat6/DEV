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
		$copy		= array();

		foreach($files['name'] as $ix => $file)
		{
			$fileName	= makeFileName($file);
			$dst		= "$folder/$fileName";
			
			if (!canEditFile($dst)){
				$result[$fileName]	= array(
					'error' => "Error upload file '$dst', no write access"
					);
				continue;
			}
			
			$src	= $files['tmp_name'][$ix];
			if (!is_file($src)) continue;
			
			$copy[$src]	= $dst;
		}
		
		beginUndo();
		$names	= implode(',', $copy);
		logData("Upload $names", 'file');
		module("file:unlink", $copy);
		endUndo();
		
		foreach($copy as $src => $dst)
		{
			$fileName		= basename($dst);
			$bFileExists	= is_file($dst);
			if (copy2folder($src, $dst))
			{
				$w = $h = 0;
				list($w, $h) = getimagesize($dst);
				
				$result[$fileName]	= array(
					'path'=>	imagePath2local($dst),
					'size'=>	filesize($dst),
					'date'=>	date('d.m.Y H:i', filemtime($dst)),
					'dimension'=>"$w x $h",
					'action'=>	$bFileExists?'replace':'new'
				);
				if (isFileTitle($dst)) break;
			}else{
				$result[$fileName]	= array(
					'error' => "Error upload file '$dst'"
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
		
		$files	= array();
		$result	= array();
		foreach($delete as $folder)
		{
			$folder	= makeFilePath($folder);
			if (canEditFile($folder)){
				$files[]			= $folder;
				$result['result']	= array();
			}else{
				$result['result']	= array(
					'error' => "Error delete file '$folder', no write access"
				);
			}
		}
		module("file:unlink", $files);
		echo json_encode($result);
	break;
	}
}
?>