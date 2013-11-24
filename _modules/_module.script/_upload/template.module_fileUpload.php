<? function module_fileUpload(&$val, &$data)
{
	switch($val){
	case 'upload':
		setTemplate('');
		
		$folder	= getValue('fileImagesPath');
		$folder	= normalFilePath(localRootPath."/$folder");
		
		$bTitle	= strpos($folder, '/Title') > 0;
		if ($bTitle) delTree($folder);
		makeDir($folder);

		$result		= array();
		$files		= $_FILES['imageFieldUpload'];
		foreach($files['name'] as $ix => $file)
		{
			$fileName	= makeFileName($file);
			$filePath	= "$folder/$fileName";
			unlinkFile($filePath);
			if (move_uploaded_file($files['tmp_name'][$ix], $filePath)){
				fileMode($filePath);
				$result[$fileName]	= imagePath2local($filePath);
				if ($bTitle) break;
			}else{
			}
		}
		echo json_encode($result);
	break;
	case 'delete':
		setTemplate('');
		$filePath	= getValue('fileImagesPath');
		$filePath	= normalFilePath(localRootPath."/$filePath");
		if (canEditFile($filePath)){
			unlinkFile($filePath);
		}else{
			echo 'Error';
		}
	break;
	}
}
?>