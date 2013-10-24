<? function import_xml($val, &$data)
{
	$synchs	= array();
	$files	= getFiles(importFolder, 'xml$');
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
			$bComplete	= doImportXML($synch);
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

//	Класс синхронизации для файлов
class importSynchXML
{
	var $baseSynch;
	var $filePath;
	var $parseRule;
	/**********************************/
	function importSynchXML($filePath, $userInfo = '')
	{
		$this->filePath	= $filePath;
		$thisFile		= "$filePath.synch/synch.txt";
		$this->parseRule= array();
//		$this->baseSynch= new baseSynch($thisFile, $userInfo);
		$this->baseSynch= module("baseSynch:$thisFile", $userInfo);
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
		$bOK	= $this->baseSynch->read();
		return $bOK;
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
		return $this->baseSynch->info();
	}
	function showInfo(){
		return $this->baseSynch->showInfo();
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
	function addRules(&$rules){
		$this->parseRule	= array_merge($this->parseRule, $rules);
	}
}
//	Выполнить синхронизацию файла
function doImportXML(&$synch)
{
	//	Получить путь к файлу
	$sourceFile	= $synch->source();
	$f			= fopen($sourceFile, 'r');
	if (!$f) return true;
	//	Продолжить импорт
	$bComplete	= doImportXML2($synch, $f);
	fclose($f);
	
	//	Если импорт завершен, вернуть true
	return $bComplete;
}
//	Импортировать файл
function doImportXML2(&$synch, &$f)
{
	//	Перечень задач для выполнения синхронизации
	$workPlan	= array();
	$workPlan['']				= 'doImportXMLprepare';
	$workPlan['importProduct']	= 'doImportXMLimport';
	$workPlan['importComplete']	= 'doImportXMLimportComplete';
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
function doImportXMLprepare(&$synch, &$f)
{
	//	Первичные стандартные настройки
	$synch->setValue('percent', 0);
	//	Подготовить импорт
	return importPrepareBulk($synch);
}
function doImportXMLimportComplete(&$synch, &$f)
{
	$synch->setValue('percent', 100);
	return importCommitBulk($synch);
}
function doImportXMLimport(&$synch, &$f)
{
	global $thisSynch;
	//	Сконфигурировать и загрузить обработчики XML файлов
	event('importXML.prepare', $synch);
	$thisSynch = $synch;
	
	fseek($f, 0, SEEK_END);
	$fileSize	= ftell($f);
	fseek($f, 0, SEEK_SET);
	
	$xml_parser	= xml_parser_create('UTF-8');
	xml_set_element_handler($xml_parser, "xmlStartElement", "xmlEndElement");
	xml_set_character_data_handler($xml_parser, "xmlContents");

	while(!feof($f) && sessionTimeout() > 5)
	{
		$data	= fread($f, 128*1024);
		if (!xml_parse($xml_parser, $data, feof($f))){
			return true;
//			die(sprintf("Ошибка XML: %s на строке %d",
//			xml_error_string(xml_get_error_code($xml_parser)),
//			xml_get_current_line_number($xml_parser)));
		}
		$seek	= ftell($f);
		$percent= $seek * 100 / $fileSize;
		$synch->setValue('percent', round($percent));
		$synch->flush();
	}
	xml_parser_free($xml_parser);
	return feof($f);
}

function xmlStartElement($parser, &$name, &$attrs)
{ 
	global $thisSynch;
	if (sessionTimeout() < 5) return;
	$seek		= xml_get_current_byte_index($parser);
	if ($seek <= $thisSynch->getValue('seek')) return;
	$thisSynch->setValue('seek', $seek);

	$tagTree	= $thisSynch->getValue('tagTree');
	$tagTree[]	= array('tagName' => $name, 'attrs' => $attrs, 'contents' => '');
	$thisSynch->setValue('tagTree', $tagTree);
}

function xmlEndElement($parser, &$name)
{ 
	global $thisSynch;
	if (sessionTimeout() < 5) return;
	$seek		= xml_get_current_byte_index($parser);
	if ($seek <= $thisSynch->getValue('seek')) return;
	$thisSynch->setValue('seek', $seek);
	
	$tagTree	= $thisSynch->getValue('tagTree');

	//	Найти родительский тег с таким же именем, что и закрывающий тег
	while($thisTag	= array_pop($tagTree)){
		if ($thisTag['tagName'] == $name) break;
	}
	//	Если открывающий тег найден, то обрабоать данные
	if ($thisTag)
	{
		array_push($tagTree, $thisTag);
		//	Создать строку родительскийх тегов через пробел, для обработки правил
		$parentTags	= array();
		foreach($tagTree as &$val){
			$parentTags[]	= $val['tagName'];
		}
		$parentTags		= implode(' ', $parentTags);
		//	Посмотреть все правила обработки тегов
		foreach($thisSynch->parseRule as $parentRule => $fn)
		{
			//	Проверить правило родительских элементов
			if (!preg_match("#$parentRule$#", $parentTags)) continue;
			//	Если функция обработчик найдена, то выполним код функции
			if ($fn) $fn($thisSynch, $tagTree);
			break;
		}
		array_pop($tagTree);
	}
	//	Сохранить текущий стек тегов	
	$thisSynch->setValue('tagTree', $tagTree);
	$thisSynch->flush();
}
function xmlContents($parser, &$data)
{ 
	global $thisSynch;
	if (sessionTimeout() < 5) return;
	$seek		= xml_get_current_byte_index($parser);
	if ($seek <= $thisSynch->getValue('seek')) return;
	$thisSynch->setValue('seek', $seek);

	$tagTree	= $thisSynch->getValue('tagTree');
	if (!$tagTree) return;

	end($tagTree);
	$tagTree[key($tagTree)]['contents']	= $data;
	
	$thisSynch->setValue('tagTree', $tagTree);
}
?>
