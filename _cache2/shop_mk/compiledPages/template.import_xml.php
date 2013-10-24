<? function import_xml($val, &$data)
{
	$synchs	= array();
	$files	= getFiles(importFolder, 'xml$');
	foreach($files as $file => $path){
		$synch 			= new importSynchXML($path, "$path");
		$synchs[$file]	= $synch;
	}
	
	switch($val){
	case 'dekete':
		foreach($synchs as $name => $synch){
			if (!$data[$name]) continue;
			$synch->deleteAll();
		}
		return;
	case 'source':
		foreach($synchs as $name => $synch){
			$data[$name]	= $synch;
		}
		return;
	case 'synch':
		foreach($synchs as $name => &$synch)
		{
			if (!$data[$name]) continue;
			if ($synch->lockTimeout()) continue;
			
			$synch->lock();
			$synch->read();
			$bComplete	= doImportXML($synch);
			if ($synch->write())
				$synch->unlock();
				
			if (!$bComplete) return;
		}
		return;
	case 'cancel':
		foreach($synchs as $name => &$synch)
		{
			if (!$data[$name]) continue;
			$synch->delete();
		}
		return;
	}
}?><?
class importSynchXML
{
	var $baseSynch;
	var $filePath;
	/**********************************/
	function importSynchXML($filePath, $userInfo = '')
	{
		$this->filePath	= $filePath;
		$thisFile		= "$filePath.synch/synch.txt";
		$this->baseSynch= new baseSynch($thisFile, $userInfo);
	}
	//	Блокрировать ресурс
	function lock(){
		return $this->baseSynch->lock();
	}
	//	Удалить блокировку ресурса
	function unlock(){
		return $this->baseSynch->unlock();
	}
	//	Узнать время блокрирования ресурса
	//	0 - Не блокирован или превышено время блокировки
	//	Иначе время работы
	function lockTimeout(){
		return $this->baseSynch->lockTimeout();
	}
	//	Получить максимальное время выполенния скрипта
	function lockMaxTimeout(){
		return $this->baseSynch->lockMaxTimeout();
	}
	/**************************************/
	//	Считать данные
	function read(){
		return $this->baseSynch->read();
	}
	//	Записать данные
	function write(){
		return $this->baseSynch->write();
	}
	//	Записывать данные каждые 20 сек
	function flush(){
		return $this->baseSynch->flush();
	}
	//	
	function writeTime(){
		return $this->baseSynch->writeTime();
	}
	//	Удалить данные и файл блокировки
	function delete(){
		$filePath	= $this->filePath;
		$thisDir	= "$filePath.synch";
		$this->baseSynch->unlock();
		delTree($thisDir);
	}
	function deleteAll(){
		$filePath	= $this->filePath;
		$this->delete();
		unlink($filePath);
	}
	/************************************/
	function info(){
		return $this->baseSynch->writeTime();
	}
	function showInfo(){
		return $this->baseSynch->info();
	}
	function getValue($key){
		return $this->baseSynch->getValue($key);
	}
	function setValue($key, $value)
	{
		return $this->baseSynch->setValue($key, $value);
	}
	/*************************************/
	function log($val, $nLevel = 0){
		return $this->baseSynch->log($val, $nLevel = 0);
	}
	function logLabel($label, $val, $nLevel = 0){
		return $this->baseSynch->logLabel($label, $val, $nLevel = 0);
	}
	//	Прочитать строки из файла и вернуть как массив
	function logRead($nMaxRows = 100, $nSeek = 0)
	{
		return $this->baseSynch->logRead($nMaxRows = 100, $nSeek = 0);
	}
	function logLines(){
		return $this->baseSynch->logLines();
	}
	/*******************************/
	function source(){
		return $this->filePath;
	}
}
?><? function doImportXML(&$synch)
{
	$sourceFile	= $synch->source();
	$f			= fopen($sourceFile, 'r');
	if (!$f) return true;
	
	$seek	= (int)$synch->getValue('seek');
	fseek($f, $seek);
	$bComplete	= doImportXML2($synch, $f);
	fclose($f);
	
	return $bComplete;
}

function doImportXML2(&$synch, &$f)
{
	$workPlan	= array();
	$workPlan['']				= 'doImportXMLprepare';
//	$workPlan['cacheCatalog']	= 'doImportXMLcacheCatalog';
//	$workPlan['cacheProduct']	= 'doImportXMLcacheProduct';
//	$workPlan['importProduct']	= 'doImportXMLimportProduct';
//	$workPlan['importComplete']	= 'doImportXMLimportComplete';
	$workPlan['complete']		= '';
	
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
			while(true)
			{
				list($plan,) = each($workPlan);
				if ($status == $plan) break;
			}
			$plan = 'complete';
			if (!list($plan,) = each($workPlan)) $plan = 'complete';
			$synch->setValue('status', $plan);
		}

		//	Прервать импорт, если запись не удалась
		if (!$synch->write()) return true;
	}
}
function doImportXMLprepare(&$synch, &$f)
{
	return true;
}
?>