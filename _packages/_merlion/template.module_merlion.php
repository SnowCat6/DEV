<? function module_merlion($fn, &$data)
{
	if (!hasAccessRole('admin,developer')) return;

	define('merlionFile', 	localRootPath.'/_exchange/merlion/merlion.txt');
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
	   'login'	=> iconv('UTF-8', 'CP1251', "$merlion[code]|$merlion[login]"),
	   'password' => iconv('UTF-8', 'CP1251', $merlion['passw'])
    );
//	print_r($params);
	m('soap:login', $params);
}
function merlionInfo(&$sy)
{
	$synch		= $sy->data;
	
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

if ($timeout = $sy->lockTimeout()){
	$maxTimeout = $sy->lockMaxTimeout();
	$info		= $sy->info();
	$userID		= $info['userID'];
	$userIP		= GetStringIP($info['userIP']);
	$lastWrite	= $sy->writeTime();
	if ($lastWrite) $lastWrite = round(time() - $lastWrite) . ' сек. назад';
	else $lastWrite = '---';
	
	$userData	= user::get($userID);
	$userName	= m('user:name', $userData);
 ?>
<p>
Импорт производится в фоновом режиме <?= round($timeout)?> / <?= $maxTimeout?> сек.<br />
UserID: {$userID} <b>{!$userName}</b><br />
UserIP: {$userIP}<br />
Последняя запись: {$lastWrite}
</p>
<?
	}

	if ($synch){
		if ($synch['action']) echo "<p>Статус: <b>$synch[action]</b></p>";
		
		$name	= $synch['thisCatalog'];
		if (!$name) $name = '---';
		echo "<p>Обработка каталога: <b>$name</b></p>";
		
		$count = (int)count($synch['avalible']);
		echo "<div>Осталось каталогов: <b>$count</b></div>";
		$count = (int)count($synch['passPriceProduct']);
		echo "<div>Осталось товаров в каталоге: <b>$count</b></div>";
	
		$count = (int)count($synch['avalibleProduct']);
		echo "<div>Всего товаров на сайте: <b>$count</b></div>";
		echo "<div>Добавлено: <b>$synch[added]</b></div>";
		echo "<div>Обновлено: <b>$synch[updated]</b></div>";
		echo "<div>Обработано: <b>$synch[dones]</b></div><br />";

		
		if ($synch['action'] == 'complete') echo '<p><b>Импорт товаров завершен</b></p>';

		$message = $merlion['synchImages']?'':', загрузка отключена';
		echo "<div>Всего изображений: <b>$synch[imageTotal]</b>$message</div>";
		$count	= (int)$synch['copyImages'];
		$size	= round($synch['sizeImages'] / 1024 / 1024, 2);
		echo "<div>Загружено изображений: <b>$count</b>, $size Мб.</div>";
		echo "<div>Обработано изображений: <b>$synch[imageSeek]</b></div>";
	}else{
		echo 'Импортирование не производилось';
	}

	$log = &$synch['log'];
	if (is_array($log) && $log){
		echo '<h2>Лог:</h2>';
		echo '<div>', implode('</div><div>', $log), '</div>';
	}
	$timeout	= $sy->lockMaxTimeout();
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
function getShipmentDates()
{
	merlionLogin();
	$xml = module('soap:exec:getShipmentDates', array('Code'=>''));
	if (!$xml) return array();
	
	$res = array();
	foreach($xml as &$val){
		$res[$val->Date] = $val->Date;
	}
	return $res;
}
function getShipmentDate()
{
	$ini	= getCacheValue('ini');
	$merlion= $ini[':merlion'];

	$days	= $merlion['deliveryDays'];
	$days	= $days?explode(',', $days):array();

	$time	= $merlion['deliveryTime'];
	if (!$time) $time = '15';
	$time	= (int)$time;
	//	День недели
	$thisDay= date('w');
	//	Воскресение будет 7 днем
	if ($thisDay == 0) $thisDay = 7;
	//	Если на сегодня день закончен, считаем следующий день
	if (date('H') >= $time) $thisDay += 1;
	//	При выхождении за пределы, снова понедельник
	if ($thisDay > 7) $thisDay = 1;

	$shipmentDay	= 0;
	foreach($days as $day){
		if ($thisDay <= $day){
			$shipmentDay = $day;
			break;
		}
	}
	if (!$shipmentDay) return NULL;
	
	$dates	= getShipmentDates();
	if (!$dates) return NULL;
	
	foreach($dates as $ship){
		list($y, $m, $d) = explode('-', $ship);
		$date	= mktime(0, 0, 0, $m, $d, $y);
		$day	= date('w', $date);
		if ($day == 0) $day = 7;
		if ($shipmentDay == $day) return $ship;
	}
	return NULL;
}
function getItemsImages($parentID, $itemID)
{
	$d = array();
	$d['Cat_id']	= $parentID;
	$d['Item_id']	= $itemID;
	$xml = module('soap:exec:getItemsImages', $d);
	if (!$xml) return array();

	$images		= array();
	foreach($xml as &$image)
	{
		if ($image->ViewType != 'v') continue;
		
		$fileName	= $image->FileName;
		if (!preg_match('#(.*)_(v\d+)_#', $fileName, $v)) continue;

		$folders= $images[$image->No];
		if (!is_array($folders)) $folders = array();

		$folderName	= 'Gallery';
		if ($v[2] == 'v01') $folderName	= 'Title';

		$folder	= $folders[$folderName];
		if (!is_array($folder)) $folder = array();
		
		$imageName	= $v[1].$v[2];
		
		$size		= $image->Size;
		if ($size < $folder[$imageName]['Size']) continue;
		
		$folder[$imageName]['Size']	= $size;
		$folder[$imageName]['Image']= $fileName;

		$images[$image->No][$folderName]	= $folder;
	}
	return $images;
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
<? function merlion_tools($val, &$data){
	$data[':merlion']['Мерлион']	=	'';
	$data[':merlion']['Каталоги']	=	getURL('import_merlion');
	$data[':merlion']['Товары']		=	getURL('import_merlion_synch');
} ?>


