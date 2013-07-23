<? function module_merlion($fn, &$data)
{
	if (!hasAccessRole('admin,developer')) return;

	define('merlionFolder', localHostPath.'/_exchange/merlion');
	define('merlionFile', 	merlionFolder.'/synch.txt');
	define('merlionLock', 	merlionFolder.'/lock.txt');
	define('merlionLog', 	merlionFolder.'/synch.log');

	merlionLogin();

	if ($fn){
		list($fn, $val) = explode(':', $fn, 2);
		$fn = getFn("merlion_$fn");
		return $fn?$fn($val, $data):NULL;
	}
	
}
function merlionLogin()
{
	$ini	= getCacheValue('ini');
	$merlion= $ini[':merlion'];
    $params = array
	(
	   'wsdl'	=> "https://api-iz.merlion.ru/mlservice.php?wsdl",
	   'login'	=> "$merlion[code]|$merlion[login]",
	   'password' => $merlion['passw']
    );
	m('soap:login', $params);
}
function readMerlionSynch(){
	return unserialize(file_get_contents(merlionFile));
}
function saveMerlionSynch(&$synch)
{
	if (!is_array($synch)){
		echo 'Bad synch';
		return;
	}
	$synchFile = $synch['thisFile'];
	if (!is_file($synchFile)) return;
	
	$synch['userIP']	= GetStringIP(userIP());
	$synch['userID']	= userID();
	$synch['fileTime']	= time();
	file_put_contents($synchFile, serialize($synch));
}
function merlionFlush(&$synch){
	if (synchMerlionTimeout($synch) < 20) return;
	saveMerlionSynch($synch);
}
function synchMerlionTimeout(&$synch)
{
	$synchFile = $synch['thisFile'];
	if (!is_file($synchFile)) return 0;
	$s	= readMerlionSynch();
	return time() - $s['fileTime'];
}
function clearMerlionSynch(){
	unlink(merlionFile);
}
function merlionInfo(&$synch)
{
	$ini		= getCacheValue('ini');
	$merlion	= $ini[':merlion'];

	$thisValue	= $merlion['ShipmentMethod'];
	if (!$thisValue) $thisValue = 'Не задано';
	echo "<div>Метод отгрузки: <b>$thisValue</b></div>";
	$thisValue	= $merlion['ShipmentDate'];

	if (!$thisValue) $thisValue = 'Не задано';
	echo "<div>Дата отгрузки: <b>$thisValue</b></div>";
	
	$thisValue	= $merlion['Currency'];
	if (!$thisValue) $thisValue = 'Не задано';
	echo "<div>Валюта и курс: <b>$thisValue</b></div>";

if (is_file(merlionLock)){ ?>
<p>
Импорт производится в фоновом режиме <?= round(time() - $synch['importStart'])?> / <?= round($synch['importTimeout'])?> сек.<br />
UserID: {$synch[userID]}<br />
UserIP: {$synch[userIP]}
</p>
<?
	}

	if ($synch){
		$name	= $synch['thisCatalog'];
		if (!$name) $name = '---';
		echo "<p>Обработка каталога: <b>$name</b></p>";
		
		$count = (int)count($synch['avalible']);
		echo "<div>Осталось каталогов: <b>$count</b></div>";
		$count = (int)count($synch['passPriceProduct']);
		echo "<div>Осталось товаров в каталоге: <b>$count</b></div>";
	
		echo "<div>Добавлено: <b>$synch[added]</b></div>";
		echo "<div>Обновлено: <b>$synch[updated]</b></div>";
		echo "<div>Обработано: <b>$synch[dones]</b></div><br />";

		
		if ($synch['action'] == 'complete') echo '<p><b>Импорт товаров завершен</b></p>';

		$message = $merlion['synchImages']?'':', загрузка отключена';
		$count = (int)count($synch['passImage']);
		echo "<div>Осталось изображений товаров: <b>$count</b>$message</div>";
		$count	= (int)$synch['copyImages'];
		$size	= round($synch['sizeImages'] / 1024 / 1024, 2);
		echo "<div>Загружено изображений: <b>$count</b>, $size Мб.</div>";
	}else{
		echo 'Импортирование не производилось';
	}

	$log = &$synch['log'];
	if (is_array($log) && $log){
		echo '<h2>Лог:</h2>';
		echo '<div>', implode('</div><div>', $log), '</div>';
	}
	$timeout	= (int)$synch['importTimeout'];
	echo "<script>var merlionTimeout = $timeout; </script>";
}

function getShipmentMethods(){
	merlionLogin();
	$xml = module('soap:exec:getShipmentMethods', array('Code'=>''));
	if (!$xml) return array();
	
	$res = array();
	foreach($xml as &$val){
		$res[$val->Code] = array('Description'=>$val->Description, 'IsDefault'=>$val->IsDefault);
	}
	return $res;
}
function getShipmentDates(){
	merlionLogin();
	$xml = module('soap:exec:getShipmentDates', array('Code'=>''));
	if (!$xml) return array();
	
	$res = array();
	foreach($xml as &$val){
		$res[$val->Date] = $val->Date;
	}
	return $res;
}
function getCurrencyRate(){
	merlionLogin();
	$xml = module('soap:exec:getCurrencyRate', array('Date'=>''));
	if (!$xml) return array();
	
	$res = array();
	foreach($xml as &$val){
		$res[$val->Code] = $val->ExchangeRate;
	}
	return $res;
}
?>
<? function merlion_tools($val, $data){ ?>
<p>
<h2>Мерлион</h2>
<a href="{{getURL:import_merlion}}">Каталоги</a> <a href="{{getURL:import_merlion_synch}}">Товары</a>
</p>
<? } ?>


