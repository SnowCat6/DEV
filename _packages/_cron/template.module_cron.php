<? function module_cron($fn, &$data)
{
	list($fn, $val) = explode(':', $fn);
	$fn = getFn("cron_$fn");
	return $fn?$fn($val, $data):NULL;
}
function cron_synch($val, &$data)
{
	if (!defined('_CRON_')) return;
	
//	$dbUser	= module('user');
//	$dbUser->open("login LIKE 'cron'");
	$dbUser	= user::find(array('login' => 'cron'));
	if ($dbUser->next()) setUserData($dbUser);

	setTemplate('');
	event('cron.synch.start', $data);
	
	$crons = getCacheValue('cronWork');
	if (!$crons) $crons = array();
	foreach($crons as $name => $module){
		$ini	= readCronIni();
		if ($ini[$name]['disable']) continue;
		
		$ini[$name]['lastRun']		= time();
		$ini[$name]['lastRunEnd']	= 'running...';
		writeCronIni($ini);
		
		ob_start();
		echo "<h2>Запуск задачи \"$name\" как \"$module\"</h2>";
		$a	= $ini[$name];
		moduleEx($module, $a);
		$log= trim(ob_get_clean());
		
		$ini						= readCronIni();
		$ini[$name]					= $a;
		$ini[$name]['lastRunEnd']	= time();
		$ini[$name]['log']			= urlencode($log);
		writeCronIni($ini);
	}

	event('cron.synch.end', $data);
}
function cron_tools($vsl, &$data){
	if (!access('write', 'cron:')) return;
	$data['Задачи']	= getURL('cron_all');
}
function module_cron_access($access, &$data){
	return 	defined('_CRON_') || hasAccessRole('admin,developer,writer');
}
function cron_add($name, &$data){
	$crons = getCacheValue('cronWork');
	if (!is_array($crons)) $crons = array();
	$crons[$name] = $data;
	setCacheValue('cronWork', $crons);
}
function cronFolder(){
	return localRootPath.'/_cron/cron.ini';
}
function readCronIni(){
	$ini	= readIniFile(cronFolder());
	if (!is_array($ini)) $ini = array();
	return $ini;
}
function writeCronIni(&$ini){
	writeIniFile(cronFolder(), $ini);
}
?>