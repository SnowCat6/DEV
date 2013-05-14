<?
function import_doImport($val, $files)
{
	if ($val)
	{
		if (!is_array($files)) return;
		
		switch($val){
		//	Перезапустить импорты указаных файлов
		case 'create':
			// Импортировать все файлы и массиве
			foreach($files as $file){
				getImportProcess($file, true);
			}
		//	Задачи созданы, обработать
		break;
		//	Продолжить импорт файлов
		case 'continue':
		break;
		//	Остановтить импорт указаных файлов
		case 'cancel':
			// Удалить все файлы и массиве
			foreach($files as $file){
				$process	= getImportProcess($file);
				$baseDir	= $process['baseDir'];
				delTree($baseDir);
			}
		//	Задачи остановлены, можно выйти
		return;
		//	Остановтить импорт указаных файлов
		case 'delete':
			// Удалить все файлы и массиве
			foreach($files as $file){
				$process	= getImportProcess($file);
				$baseDir	= $process['baseDir'];
				delTree($baseDir);
				@unlink($process['importFile']);
			}
		//	Задачи остановлены, можно выйти
		return;
		//	Если команда неизвестна, ничего не делать
		default:
			return;
		}
	}else{
		$files = array_keys(getFiles(importFolder, '(xml|txt)$'));
	}

	$baseDir	= importFolder;
	//	Файл блокировки импорта
	$lockFile	= "$baseDir/lock.txt.bin";
	//	Если файл существует, и время его создания не превысило таймаут, то не обрабатываем
	//	Выйти, ибо импорт уже идет
	if (is_file($lockFile) && mktime() - filemtime($lockFile) < (int)ini_get('max_execution_time')) return;
	//	Создать файл блокировки
	file_put_contents_safe($lockFile, $lockFile);
	
	// Импортировать все файлы и массиве
	foreach($files as $file)
	{
		$path	= importFolder."/$file";
		if (!is_file($path)) continue;
		//	Получить данные по импорту
		$process = getImportProcess($file);
		if ($process['status'] == 'complete') continue;

		//	Импортировать
		$bCompleted	= module('import:file', &$process);
		//	Есои импорт не завершен, то вывести страницу и продолжить импорт
		if (!$bCompleted){
			//	Удалить блокировку
			@unlink($lockFile);
			return setImportProcess($process, false);
		}
		//	Если импорт завершен, заисать результат, продолжить со следующим файлом
		setImportProcess($process, true);
	}
	//	Удалить блокировку
	@unlink($lockFile);
}
?>
