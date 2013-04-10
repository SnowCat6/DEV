<?
//	Задать папку для импорта файлов
define('importFolder', localHostPath.'/_exchange');

function module_import($fn, &$data)
{
	@list($fn, $val) = explode(':', $fn, 2);
	$fn = getFn("import_$fn");
	return $fn?$fn($val, $data):NULL;
}
?>
<?
function getImportProcess($file, $bCreateTask = false)
{
	$baseName	= basename($file);
	$path		= importFolder."/$baseName";
	$baseDir	= importFolder."/$baseName.import";
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
		$process['log']			= array();
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
	if ($bCreateTask){
		makeDir($baseDir);
		setImportProcess($process, false);
	}

	return $process;
}

function setImportProcess($process, $bCompleted)
{
	$baseDir			= $process['baseDir'];
	if (!is_dir($baseDir)) return false;

	$process['status']	= $bCompleted?'complete':'working';
	
	makeDir($baseDir);
	writeData("$baseDir/import.bin.txt", $process);
	return true;
}
function importLog(&$process, $message){
	$process['log'][] = $message;
}
function parseInt($val){
	$val = preg_replace('#[^\d.,]#', '', $val);
	$val = (float)str_replace(',',  '.', $val);
	return $val;
}
?>