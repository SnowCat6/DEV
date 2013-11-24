<? function module_fileUpload(&$val, &$data){
	switch($val){
	case 'upload':
		setTemplate('');
		
		$folder	= getValue('fileImagesPath');
		$folder	= normalFilePath(images."/$folder");
		
		$bTitle	= strpos($folder, '/Title') > 0;
		if ($bTitle) delTree($folder);
		makeDir($folder);

		$files		= $_FILES['imageFieldUpload'];
		foreach($files['name'] as $ix => $file)
		{
			$fileName	= makeFileName($file);
			$filePath	= "$folder/$fileName";
			unlinkFile($filePath);
			if ($ix) echo ', ';
			if (move_uploaded_file($files['tmp_name'][$ix], $filePath)){
				fileMode($filePath);
				echo "$fileName OK";
				if ($bTitle) break;
			}else{
				echo "$fileName FALSE";
			}
		}
	break;
	case 'delete':
		setTemplate('');
		$filePath	= getValue('fileImagesPath');
		$filePath	= normalFilePath(images."/$filePath");
		if (canEditFile($filePath)){
			unlinkFile($filePath);
		}else{
			echo 'Error';
		}
	break;
	}
}
?>