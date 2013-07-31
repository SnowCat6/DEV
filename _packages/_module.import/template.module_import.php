<?
//	Задать папку для импорта файлов
define('importFolder', localHostPath.'/_exchange');

function module_import($fn, &$data)
{
	@list($fn, $val) = explode(':', $fn, 2);
	$fn = getFn("import_$fn");
	return $fn?$fn($val, $data):NULL;
}
function getImportProcess($file, $bCreateTask = false)
{
	$baseName	= basename($file);
	$path		= importFolder."/$baseName";
	$baseDir	= importFolder."/$baseName.import";
	if (!is_file($path)) return;

	if ($bCreateTask){
		$process = NULL;
	}else{
		$process	= readData("$baseDir/import.bin.txt");
		if (is_dir($baseDir) && !is_array($process)){
			sleep(1);
			$process	= readData("$baseDir/import.bin.txt");
		}
	}
	//	Проверим, что исходный файл не изменился, если что, перезапустить процесс
/*	if (@$process['fileUpdate'] != filemtime($path) || $bCreateTask){
		delTree($baseDir);
		$process = NULL;
	}
*/	
	if (!is_array($process))
	{
		$process 				= array();
		$process['fileUpdate']	= filemtime($path);
		$process['startTime']	= time();
		$process['endTime']		= '';
		$process['status']		= 'wait';
		$process['log']			= array();
	}
	
	$process['baseDir']		= $baseDir;
	$process['importFile']	= $path;
	
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
		delTree($baseDir);
		makeDir($baseDir);
		logData("import: \"$baseDir\" start", 'import');
		setImportProcess($process, false);
	}

	return $process;
}

function setImportProcess($process, $bCompleted)
{
	$baseDir			= $process['baseDir'];
	if (!is_dir($baseDir)) return false;

	$process['endTime']	= time();
	if ($bCompleted){
		$process['status']	= 'complete';
		importLog($process, 'Импорт завершен', 'status');
		logData("import: \"$baseDir\" complete", 'import', $process['log']);
	}else{
		$process['status']	= 'working';
	}
	
	makeDir($baseDir);
	writeData("$baseDir/import.bin.txt", $process);
	return true;
}
function importLog(&$process, $message, $entryName = NULL){
	if ($entryName){
		$process['log'][$entryName] = $message;
	}else{
		$process['log'][] = $message;
	}
}
function parseInt(&$val){
	$v = preg_replace('#[^\d.,]#', '', $val);
	$v = (float)str_replace(',',  '.', $v);
	return $v;
}
?>
<? function import_tools($fn, &$data){
	if (!access('add', 'doc:product')) return;
	$data['Импорт товаров']	= getURL('import');
 } ?>

