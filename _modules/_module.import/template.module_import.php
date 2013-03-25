<? function module_import($fn, &$data)
{
	@list($fn, $val) = explode(':', $fn, 2);
	$fn = getFn("import_$fn");
	return $fn?$fn($val, $data):NULL;
}
?>
<?
function getImportProcess($path, $bCreateTask = false)
{
	$baseName	= basename($path);
	$baseDir	= dirname($path)."/$baseName.import";
	//	Если перезапускаем задачу, удалить все файлы
	if ($bCreateTask) delTree($baseDir);

	//	Проверим, что исходный файл не изменился, если что, перезапустить процесс
	$process	= readData("$baseDir/import.bin.txt");
	if (@$process['fileUpdate'] != filemtime($path) || !is_array($process))
	{
		$process 				= array();
		$process['fileUpdate']	= filemtime($path);
		$process['startTime']	= mktime();
		$process['status']		= 'wait';
		delTree($baseDir);
	}
	
	$process['baseDir']		= $baseDir;
	$process['importFile']	= $path;
	
	//	Дата последнего действия, по сути, дата обновления хранилища данных
	@$process['processDate']= filemtime("$baseDir/import.bin.txt");
	
	//	Смещение от начала файла, текущий процент чтения файла
	if (!is_int($process['offset'])) $process['offset'] = 0;
	
	//	Размер файла
	$process['size']		= filesize($path);
	
	//	Вычислить процент обработки
	if ($process['size']){
		$process['percent']	= floor(100 * $process['offset'] / $process['size']);
	}else{
		$process['percent']	= 100;
	}
	//	Если перезапускаем задачу, записать начальное состояние
	if ($bCreateTask) setImportProcess($process, false);

	return $process;
}

function setImportProcess($process, $bCompleted)
{
	$baseDir			= $process['baseDir'];
	$process['status']	= $bCompleted?'complete':'working';
	
	makeDir($baseDir);
	writeData("$baseDir/import.bin.txt", $process);
}
function importLog(&$process, $message){
	$process['log'][] = $message;
}
?>