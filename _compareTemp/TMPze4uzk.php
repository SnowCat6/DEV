<?
class baseSynch
{
	/************************************/
	var $filePath;	//	Путь к данным сессии
	var $lockFile;	//	Путь к файлу блокировки ресурса
	var $info;		//	Произвольная информация о рабочем процессе, пользователь, IP и прочее.
	var	$lastWrite;	//	Время последней записи на диск, для функции flush
	var $data;		//	Пользовательские данные
	/************************************/
	function baseSynch($filePath, $userInfo = '')
	{
		$filePath		= localRootPath."/$filePath";
		$this->filePath	= $filePath;
		$this->lockFile	= "$filePath.lock";
		
		$info	= array();
		$info['userInfo']	= $userInfo;
		$timeout	= (int)ini_get('max_execution_time');
		if (!$timeout && defined('_CRON_')) $timeout = 4*60;
		$info['maxTimeout']	= $timeout;
		$info['userIP']		= userIP();
		$info['userID']		= userID();
		$info['sessionID']	= sessionID;
		$this->info			= $info;
		
		$this->lastWrite	= 0;
	}
	//	Блокрировать ресурс
	function lock(){
		$this->unlock();
		$this->info['lockTime']	= time();
		makeDir(dirname($this->lockFile));
		file_put_contents($this->lockFile, serialize($this->info));
	}
	//	Удалить блокировку ресурса
	function unlock(){
		unlink($this->lockFile);
	}
	//	Узнать время блокрирования ресурса
	//	0 - Не блокирован или превышено время блокировки
	//	Иначе время работы
	function lockTimeout(){
		//	Прочитать информацию
		$info	= unserialize(file_get_contents($this->lockFile));
		//	Если файла нет, то рапортовать об отсутствии блокировки
		if (!is_array($info)) return 0;
		//	Получить время выполнения скрипта
		$timeout= time() - $info['lockTime'];
		if ($timeout > $info['maxTimeout']) return 0;
		//	Вернуть время выполнения скрипта
		return $timeout;
	}
	//	Получить максимальное время выполенния скрипта
	function lockMaxTimeout(){
		//	Прочитать информацию
		$info	= $this->info();
		return $info['maxTimeout'];
	}
	/**************************************/
	//	Считать данные
	function read(){
		$this->data	= unserialize(file_get_contents($this->filePath));
		if (!$this->data) $this->data = array();
	}
	//	Записать данные
	function write(){
		//	Не перезаписывать чжую сессию
		$info	= unserialize(file_get_contents($this->lockFile));
		if ($info && $info['sessionID'] != sessionID) return;
		
		makeDir(dirname($this->filePath));
		file_put_contents($this->filePath, serialize($this->data));
		return true;
	}
	//	Записывать данные каждые 20 сек
	function flush(){
		if (time() - $this->lastWrite < 20) return true;

		$this->lastWrite	= time();
		return $this->write();
	}
	//	
	function writeTime(){
		return filemtime($this->filePath);
	}
	//	Удалить данные и файл блокировки
	function delete(){
		unlink($this->filePath);
		$this->unlock();
	}
	/************************************/
	function info(){
		$info	= unserialize(file_get_contents($this->lockFile));
		if (!$info) $info = $this->info;
		return $info;
	}
	function showInfo(){
		$info	= $this->info();
	}
};
/*
$synch = new baseSynch('_exchange/synch.txt');
if ($timeout = $synch->lockTimeout()){
	$maxLock = $synch->lockMaxTimeout();
	echo "Locked: $timeout/$maxLock сек.";
}else{
	echo "No lock";
	//	Блокировать ресурс
	$synch->lock();
	//	Прочитать данные
	$symch->read();
	while(any time){
		//	Внести изменения в данные
		$synch->data['anyKey']	= 'any data';
		//	Записать данные раз в 20 сек.
		$synch->flush();
	}
	//	Записать данные
	if ($synch->write()){
		$synch->unlock();
	}
}
*/
?>