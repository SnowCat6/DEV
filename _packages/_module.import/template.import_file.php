<?
function import_file($val, &$data)
{
	m('import:xml');
	
	$synchs	= array();
	$files	= getFiles(importFolder, '(txt|csv)$');
	foreach($files as $file => $path){
		$synch 	= new importSynchXML($path, $path);
		if (!$synch->lockTimeout() &&
			$synch->getValue('filemtime') != filemtime($synch->source())){
			$synch->delete();
		}
		$synchs[$file]	= $synch;
	}
	
	switch($val){
	//	Удплить файл синхонизации
	case 'delete':
		foreach($synchs as $name => $synch){
			if (!$data[$name]) continue;
			$synch->deleteAll();
		}
		return;
	//	Вернуть объекты с файлами синхронизации
	case 'source':
		foreach($synchs as $name => $synch){
			$data[$name]	= $synch;
		}
		return;
	//	Синхронизировать файлы
	case 'synch':
		foreach($synchs as $name => &$synch)
		{
			if (!$data[$name]) continue;
			if ($synch->lockTimeout()) continue;
			
			$synch->lock();
			$synch->read();
			$synch->setValue('filemtime', filemtime($synch->source()));
			$bComplete	= doImportFile($synch);
			if ($synch->write())
				$synch->unlock();
				
			if (!$bComplete) return;
		}
		return;
	//	Остановить синхронизацию
	case 'cancel':
		foreach($synchs as $name => &$synch)
		{
			if (!$data[$name]) continue;
			$synch->delete();
		}
		return;
	}
}
//	Выполнить синхронизацию файла
function doImportFile(&$synch)
{
	//	Получить путь к файлу
	$sourceFile	= $synch->source();
	$f			= fopen($sourceFile, 'r');
	if (!$f) return true;
	//	Продолжить импорт
	$bComplete	= doImportFile2($synch, $f);
	fclose($f);
	
	//	Если импорт завершен, вернуть true
	return $bComplete;
}
//	Импортировать файл
function doImportFile2(&$synch, &$f)
{
	//	Перечень задач для выполнения синхронизации
	$workPlan	= array();
	$workPlan['']				= 'doImportFilePrepare';
	$workPlan['importProduct']	= 'doImportFileImport';
	$workPlan['importComplete']	= 'doImportFileImportComplete';
	$workPlan['complete']		= '';
	
	//	Выполнять план работ, пока есть время для работы и задачи не выполнены.
	while($synch->getValue('status') != 'complete' && sessionTimeout() > 5)
	{
		//	Статус неизвестен, завершить работу
		$status	= $synch->getValue('status');
		if (!isset($workPlan[$status]) || $workPlan[$status] == ''){
			$synch->setValue('status', 'complete');
			return true;
		}
		$fn	= $workPlan[$status];
		if ($fn($synch, $f)){
			//	Задача выполнена, начать следующую
			while(list($plan,) = each($workPlan))
			{
				if ($status == $plan) break;
			}
			list($plan,) = each($workPlan);
			if (!$plan) $plan = 'complete';
			
			$synch->setValue('status', $plan);
		}

		//	Прервать импорт, если запись не удалась
		if (!$synch->write()) return true;
	}
	return true;
}
function doImportFilePrepare(&$synch, &$f)
{
	//	Первичные стандартные настройки
	$synch->setValue('percent', 0);
	//	Подготовить импорт
	return importPrepareBulk($synch);
}
function doImportFileImportComplete(&$synch, &$f)
{
	$synch->setValue('percent', 100);
	return importCommitBulk($synch);
}
function doImportFileImport(&$synch, &$f)
{
	//	Сконфигурировать и загрузить обработчики XML файлов
	$ev		= array(&$synch, &$f);
	event('importFile.prepare', $ev);
	
	$seek		= (int)$synch->getValue('seek');
	fseek($f, 0, SEEK_END);
	$fileSize	= ftell($f);
	fseek($f, $seek, SEEK_SET);
	
	while(!feof($f) && sessionTimeout() > 5)
	{
		$line	= fgets($f);
		$ev		= array(&$synch, &$f, &$line);
		event('importFile.import', $ev);
		
		$seek	= ftell($f);
		$percent= $seek * 100 / $fileSize;
		$synch->setValue('percent', round($percent));
		$synch->setValue('seek', $seek);
		$synch->flush();
	}

	return feof($f);
}

?>