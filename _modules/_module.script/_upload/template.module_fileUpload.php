﻿<? function module_fileUpload(&$val, &$data)
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
			$bFileExists= is_file($filePath);
			unlinkFile($filePath);
			
			if (move_uploaded_file($files['tmp_name'][$ix], $filePath))
			{
				fileMode($filePath);
				$w = $h = 0;
				list($w, $h) = getimagesize($filePath);
				
				$result[$fileName]	= array(
					'path'=>	imagePath2local($filePath),
					'size'=>	filesize($filePath),
					'date'=>	date('d.m.Y H:i', filemtime($filePath)),
					'dimension'=>"$w x $h",
					'action'=>	$bFileExists?'replace':'new'
				);
				if ($bTitle) break;
			}else{
				$result[$fileName]	= array(
					'error' => 'Error upload file'
					);
			}
		}
		echo json_encode($result);
	break;
	case 'delete':
		setTemplate('');
		$filePath	= getValue('fileImagesPath');
		$filePath	= normalFilePath(localRootPath."/$filePath");
		$fileName	= makeFileName(basename($filePath));
		
		$result		= array();
		if (canEditFile($filePath)){
			unlinkFile($filePath);
			$result['result']	= array();
		}else{
			$result['result']	= array(
				'error' => 'Error delete file'
			);
		}
		echo json_encode($result);
	break;
	}
}
?>