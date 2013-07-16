<? function merlion_synch($val, &$data)
{
	m("page:title", "Обновление товаров");

	$timeout = (int)ini_get('max_execution_time');
	if ($timeout > 60) $timeout = 60;
	else $timeout = 60;
	set_time_limit($timeout);
	
	define('merlionFolder', localHostPath.'/_exchange/merlion');
	
	if (testValue('doSynchImages'))
	{
		$ini		= getCacheValue('ini');
		@$merlion	= $ini[':merlion'];
		$ini[':merlion']['synchProduct']= (int)getValue('doSynchProduct');
		$ini[':merlion']['synchPrice']	= (int)getValue('doSynchPrice');
		$ini[':merlion']['synchImages']	= (int)getValue('doSynchImages');
		$ini[':merlion']['synchYandex']	= (int)getValue('doSynchYandex');
		setIniValues($ini);
	}

	if (getValue('doSynchMerlion')) clearMerlionSynch();

	if (testValue('ajax')){
		setTemplate('');
		$bSuccess = merlionSych();
		return merlionImportUI($bSuccess);
	}
	if (testValue('synch')) $bSuccess = merlionSych();
	else $bSuccess = true;

	m('script:jq');
?>
<div id="importProcess"><? merlionImportUI($bSuccess) ?></div>
<script>
//	Счетчик секунд до обновления
var lastImportUpdate = 0;
$(function(){
	updateImportData();
});
//	Загрузить через AJAX обновленные данные
function updateImportData()
{
	if (lastImportUpdate++ >= 5){
		$("#reloadImportButton").val("Обновляется");
		setTimeout(updateImportButton, 1000);
		try{
			$("#importProcess").load("<? $module_data = array(); $module_data[] = "ajax"; moduleEx("getURL:import_merlion_synch", $module_data); ?>", function(data)
			{
				$(document).trigger("jqReady");
				lastImportUpdate = 0;
				updateImportData();
			});
		}catch(e){
			lastImportUpdate = 0;
			updateImportData();
		}
	}else{
		$("#reloadImportButton").val("Обновить через " + (6 - lastImportUpdate) + " сек.");
		setTimeout(updateImportData, 1000);
	}
}
function updateImportButton(){
	if (lastImportUpdate < 5) return;
	$("#reloadImportButton").val("Обновляется (" + (lastImportUpdate - 5) + " / <? if(isset($timeout)) echo htmlspecialchars($timeout) ?>) сек.");
	++lastImportUpdate;
	setTimeout(updateImportButton, 1000);
}
</script>
<? } ?>
<? function merlionImportUI($bSuccess = true){ ?>
<form action="<? module("getURL:import_merlion_synch"); ?>" method="post">
<input type="hidden" name="synch" value="1" />
<?
	$ini		= getCacheValue('ini');
	@$merlion	= $ini[':merlion'];
	@$bSynchImages	= $merlion['synchImages'];
	@$bSynchProduct	= $merlion['synchProduct'];
	@$bSynchPrice	= $merlion['synchPrice'];
	@$bSynchYandex	= $merlion['synchYandex'];

	@$thisValue	= $merlion['ShipmentMethod'];
	if (!$thisValue) $thisValue = 'Не задано';
	echo "<div>Метод отгрузки: <b>$thisValue</b></div>";
	@$thisValue	= $merlion['ShipmentDate'];

	if (!$thisValue) $thisValue = 'Не задано';
	echo "<div>Дата отгрузки: <b>$thisValue</b></div>";
	
	$synchFile	= merlionFolder.'/synch.txt';
	@$synch		= unserialize(file_get_contents($synchFile));
	
	if ($synch){
		@$name	= $synch['thisCatalog'];
		if (!$name) $name = '---';
		echo "<p>Обработка каталога: <b>$name</b></p>";
		
		@$count = (int)count($synch['passProduct']);
		echo "<div>Осталось каталогов: <b>$count</b></div>";
		
		@$count = (int)count($synch['merlionProduct']);
		echo "<div>Осталось товаров в каталоге: <b>$count</b></div>";
		
		echo "<div>Добавлено: <b>$synch[added]</b></div>";
		echo "<div>Обновлено: <b>$synch[updated]</b></div>";

		@$count = (int)count($synch['passPrice']);
		echo "<div>Осталось каталогов цен: <b>$count</b></div>";
		@$count = (int)count($synch['passPriceProduct']);
		echo "<div>Осталось товаров цен: <b>$count</b></div>";
		
		if ($synch['action'] == 'complete') echo '<p>Импорт товаров завершен</p>';
		
		$message = $bSynchImages?'':', загрузка отключена';
		@$count = (int)count($synch['passImage']);
		echo "<div>Осталось изображений товаров: <b>$count</b>$message</div>";
		@$count	= (int)$synch['copyImages'];
		@$size	= round($synch['sizeImages'] / 1024 / 1024, 2);
		echo "<div>Загружено изображений: <b>$count</b>, $size Мб.</div>";
	}else{
		echo 'Импортирование не производилось';
	}
?>
<p>
<div>
<input  type="hidden" name="doSynchProduct" value="0" />
<label><input name="doSynchProduct" type="checkbox" value="1"<?= $bSynchProduct?' checked="checked"':''?>> Импортировать товары</label>
</div>
<div>
<input  type="hidden" name="doSynchPrice" value="0" />
<label><input name="doSynchPrice" type="checkbox" value="1"<?= $bSynchPrice?' checked="checked"':''?>> Импортировать цены</label>
</div>
<div>
<input  type="hidden" name="doSynchYandex" value="0" />
<label><input name="doSynchYandex" type="checkbox" value="1"<?= $bSynchYandex?' checked="checked"':''?>> Создать Yandex XML</label>
</div>
<div>
<input  type="hidden" name="doSynchImages" value="0" />
<label><input name="doSynchImages" type="checkbox" value="1"<?= $bSynchImages?' checked="checked"':''?>> Импортировать картинки</label>
</div>
<? if ($synch){ ?>
<div>
<label><input name="doSynchMerlion" type="checkbox" value="1"> Импортировать товары с начала</label>
</div>
<? } ?>
</p>
<p>
<input type="submit" value="Обновить" class="button" id="reloadImportButton">
</p>
<? if (!$bSuccess){ ?>
<p>
Импорт производится в фоновом режиме <?= round(synchMerlionTimeout($synch))?> / <?= (int)ini_get('max_execution_time')?> сек.:<br />
UserID: <? if(isset($synch["userID"])) echo htmlspecialchars($synch["userID"]) ?><br />
UserIP: <? if(isset($synch["userIP"])) echo htmlspecialchars($synch["userIP"]) ?>
</p>
<? } ?>
</form>
<?
@$log = &$synch['log'];
if (is_array($log) && $log){
	echo '<h2>Лог:</h2>';
	echo '<div>', implode('</div><div>', $log), '</div>';
}
?>
<? } ?>
<? function saveMerlionSynch(&$synch){
	if (!is_array($synch)){
		echo 'Bad synch';
		return;
	}
	$synchFile = $synch['thisFile'];
	if (!is_file($synchFile)) return;
	$synch['userIP']	= GetStringIP(userIP());
	$synch['userID']	= userID();
	file_put_contents_safe($synchFile, serialize($synch));
	return true;
};
function synchMerlionTimeout(&$synch){
	$synchFile = $synch['thisFile'];
	if (!is_file($synchFile)) return 0;
	return time() - filemtime($synchFile);
}
function clearMerlionSynch(){
	$lockFile	= merlionFolder.'/lock.txt';
	$synchFile	= merlionFolder.'/synch.txt';
	@unlink($synchFile);
}
?>
<? function merlionSych()
{
	$lockFile	= merlionFolder.'/lock.txt';
	$synchFile	= merlionFolder.'/synch.txt';
	
	if (is_file($lockFile) && time() - filectime($lockFile) < (int)ini_get('max_execution_time')) return;

	$val = time();
	file_put_contents_safe($lockFile, $val);

	@$synch		= unserialize(file_get_contents($synchFile));
	if (!is_array($synch)){
		$synch = array();
		$synch['thisFile']		= $synchFile;
		file_put_contents_safe($synchFile, serialize($synch));
	}

	doMerlionImport($synch);
	saveMerlionSynch($synch);
	//	Сохранить состояние
	@unlink($lockFile);
	return true;
}?>
<? function doMerlionImport(&$synch)
{
	$ini		= getCacheValue('ini');
	@$merlion	= $ini[':merlion'];
	
	merlionLogin();

	if (!merlionImportInit($synch)) 	return;

	if (!merlionCacheCatalog($synch))	return;
	if (!merlionCacheProduct($synch))	return;

	if (!merlionImportProduct($synch))	return;
	if (!merlionImportPrice($synch))	return;

	merlionImportComplete($synch);
	
	if (@$merlion['synchYandex'] && !isset($synch['YandexXML'])){
		if (!moduleEx('import:YandexXML', $synch)) return;
		$synch['YandexXML'] = true;
	}

	if (!merlionImportImage($synch))	return;

	return true;
} ?>
<? function merlionImportInit(&$synch)
{
	if (isset($synch['action'])) return true;
	$synch['action'] 	= 'init';

	@$property	= &$synch['merlionProperty'];
	@$product 	= &$synch['merlionProduct'];
	@$pass		= &$synch['pass'];
	@$passParent= &$synch['passParent'];
	@$added		= &$synch['added'];
	@$updated	= &$synch['updated'];
	@$log		= &$synch['log'];
	$passImage	= &$synch['passImage'];
	$copyImages	= &$synch['copyImages'];
	$sizeImages	= &$synch['sizeImages'];
	@$productCount		= &$synch['merlionProductCount'];
	@$pricePercent		= &$synch['pricePercent'];

	//	Подготовить структуры для импорта
	$log			= array();
	$pass			= array();
	$passParent		= array();
	$added			= 0;
	$updated		= 0;
	$copyImages		= 0;
	$sizeImages		= 0;
	$pricePercent	= array();
	
	$db			= module('doc');
	$db->sql	='';
	
	$s						= array();
	$s['prop'][':import']	= 'merlion';
	$s['type']				= 'product';
	
	$sql	= array();
	doc_sql($sql, $s);
	$sql	= $db->makeRawSQL($sql);
	
	$s	= "UPDATE $sql[from] $sql[join] SET `price_merlion` = 0 $sql[where]";
	$db->exec($s);

	return true;
}
?>
<? function merlionImportComplete(&$synch)
{
	saveMerlionSynch($synch);
	if ($synch['action'] == 'complete') return true;

	$ini		= getCacheValue('ini');
	@$merlion	= $ini[':merlion'];
	if (@$merlion['synchPrice'])
	{
		$db			= module('doc');
		$db->sql	='';
		
		$s						= array();
		$s['prop'][':import']	= 'merlion';
		$s['type']				= 'product';
		
		$sql	= array();
		doc_sql($sql, $s);
		$sql	= $db->makeRawSQL($sql);
		
		$s	= "UPDATE $sql[from] $sql[join] SET `price` = `price_merlion`, `visible`=`price_merlion` > 0 $sql[where]";
		$db->exec($s);
	}
	
	clearCache();
	module('doc:clear');
	
	$synch['action'] 	= 'complete';
}?>
<? function merlionCacheCatalog(&$synch)
{
	@$avalible 		= &$synch['avalible'];
	if (isset($avalible)) return true;
	
	@$names 		= &$synch['names'];
	@$pricePercent	= &$synch['pricePercent'];
	
	@$avalible 	= array();
	$names		= array();

	$db	= module('doc');
	$s	= array();
	$s['prop'][':import']	= 'merlion';
	$s['type']				= 'catalog';

	$db->sql= '';
	$db->open(doc2sql($s));
	
	$parents	= array();
	while($data = $db->next())
	{
		$id		= $db->id();
		@$prop	= $data['fields']['any']['merlion'];
		@$itemID	= $prop[':merlion_itemID'];
		$names[$id]	= $data['title'];
		$avalible[$itemID] 		= $id;
		@$pricePercent[$itemID]	= $prop[':merlion_price'];

		@$parentID			= $prop[':merlion_parentID'];
		$parents[$parentID] = $parentID;

		$db->clearCache();
	}
	//	Удалить все возможные роительские каталоги, для исключения дублированных запросов
	foreach($parents as $parentID){
		if (!isset($avalible[$parentID])) continue;
		unset($avalible[$parentID]);
	}
	$synch['passProduct']	= $avalible;
	$synch['passPrice']		= $avalible;

	return false;
}?>
<? function merlionCacheProduct(&$synch)
{
	@$passImage	= &$synch['passImage'];
	@$avalible	= &$synch['avalibleProduct'];
	if (isset($avalible)) return true;
	@$avalible 	= array();

	$db	= module('doc');
	$s	= array();
	$s['prop'][':import']	= 'merlion';
	$s['type']				= 'product';
	$db->sql = '';
	$db->open(doc2sql($s));
	
	while($data = $db->next())
	{
		$id			= $db->id();
		@$prop		= $data['fields']['any']['merlion'];
		@$parentID	= $prop[':merlion_parentID'];
		@$itemID	= $prop[':merlion_itemID'];
		$article	= ":$parentID:$itemID";
		$avalible[$article] = $id;
		$passImage[$article]= $id;
		$db->clearCache();

		if (sessionTimeout() < 1) return;
	}
	return true;
}?>
<? function merlionImportProduct(&$synch)
{
	$ini		= getCacheValue('ini');
	$merlion	= $ini[':merlion'];
	if (!@$merlion['synchProduct']) return true;

	$property	= &$synch['merlionProperty'];
	$product 	= &$synch['merlionProduct'];
	$names 	= &$synch['names'];
	$pass		= &$synch['pass'];
	$passParent= &$synch['passParent'];
	$added		= &$synch['added'];
	$updated	= &$synch['updated'];
	$log		= &$synch['log'];
	$passImage	= &$synch['passImage'];
	$productCount		= &$synch['merlionProductCount'];
	$avalibleProduct	= &$synch['avalibleProduct'];
	$passProduct		= &$synch['passProduct'];

	$db		= module('doc');
	$db->sql= '';
	//	Начать ипортирование до таймаута операции или пока есть каталоги
	//	Пройти по одному через каталоги
	foreach($passProduct as $parentID => $thisID)
	{
		$synch['thisCatalog']	= $names[$thisID];
		//	Если не были получены товары в текущем каталоге, получить с сайта
		if (!isset($product))
		{
			if (sessionTimeout() < 5) return;
//				$log[]= "SOAP: getItems(cat_id: $parentID)";
			//	Выпонить запрос, получить список товаров
			$data = array();
			$data['Cat_id'] = $parentID;
			$xml = module('soap:exec:getItems', $data);
			if (!is_array($xml)){
				$log[]= "SOAP ERROR: getItems(Cat_id: $parentID)";
				return;
			}
			//	Если нет товаров, то обработать следующий каталог
			if (!$xml){
				$synch['thisCatalog']	= NULL;
				unset($passProduct[$parentID]);
				continue;
			};
			
			$product = array();
			foreach($xml as &$item){
				$itemID				= $item->No;
				$product[$itemID]	= $item;
			}
			$productCount = count($product);
		}

		foreach($product as $itemID => &$item)
		{
			if (sessionTimeout() < 2) return;
			
			$article= ":$parentID:$itemID";
			if (isset($pass[$article])){
				unset($product[$itemID]);
				continue;
			}

			$id	= $avalibleProduct[$article];
			if ($id){
				$data	= $db->openID($id);
				@$prop	= $data['fields']['any']['merlion'];
				if (!$prop) $prop = array();
			}else{
				$data	= array();
				$prop	= array();
			}

			$bUpdate	= false;
			$d			= array();
			if ($prop[':merlion_property'] != 'yes')
			{
				$pr = merlionGetProperty($synch, $parentID, $itemID);
				if (!is_array($pr)) return;
				
				foreach($pr as &$p) $d[':property'][$p->PropertyName]	= $p->Value;
				$prop[':merlion_property']	= $pr?'yes':'no';
				$bUpdate = true;
			}
			
			if (!$id)
			{
				$d['title']						= $item->Name;
				$d['visible']					= 0;	//	Скрыто новые товары, т.к. нет ни цен ни изображений
				$d[':property'][':import']		= 'merlion';
				$prop[':importParent']			= $thisID;
				$prop[':merlion_parentID']		= $parentID;
				$prop[':merlion_itemID']		= $itemID;
				$d['fields']['any']['merlion']	= $prop;
				$iid = moduleEx("doc:update:$thisID:add:product", $d);
				if ($iid) $added++;
				else module("display:message");
			}else{
				if ($bUpdate)	$d['fields']['any']['merlion'] = $prop;
				if ($d){
					$iid = moduleEx("doc:update:$id:edit", $d);
					$updated++;
				}else $iid = $id;
			}
			$pass[$article] 	= $iid;
			$passImage[$article]= $iid;
			unset($product[$itemID]);
			saveMerlionSynch($synch);
			$db->clearCache();
		}

		$synch['thisCatalog']	= NULL;
		$product	= NULL;
		$property	= NULL;
		unset($passProduct[$parentID]);
	}
	return count($passProduct) == 0;
} ?>
<? function merlionImportPrice(&$synch)
{
	$ini		= getCacheValue('ini');
	@$merlion	= $ini[':merlion'];
	if (!$merlion['synchPrice']) return true;
	/*********************************************/
	//	Цены товаров
	$ini			= getCacheValue('ini');
	$merlion		= $ini[':merlion'];
	
	$Currency		= explode(':', $merlion['Currency']);
	$Currency		= (float)$Currency[1];
	if (!$Currency) $Currency = 1;

	@$ShipmentMethod= $merlion['ShipmentMethod'];
	@$ShipmentDate	= $merlion['ShipmentDate'];
	if (!$ShipmentMethod || !$ShipmentDate) return true;

	$log				= &$synch['log'];
	$names	 			= &$synch['names'];
	$passPrice			= &$synch['passPrice'];
	$passPriceProduct	= &$synch['passPriceProduct'];
	$avalibleProduct	= &$synch['avalibleProduct'];
	$pricePercent		= &$synch['pricePercent'];
	$db					= module('doc');
	
	foreach($passPrice as $parentID => $thisID)
	{
		$synch['thisCatalog']	= $names[$thisID];
		if (!isset($passPriceProduct))
		{
			if (sessionTimeout() < 2) return;

			$data = array();
			$data['Cat_id'] 		= $parentID;
			$data['Shipment_method']= $ShipmentMethod;
			$data['Shipment_date']	= $ShipmentDate;
			$data['Only_avail']		= 1;
//			$log['shipment']= "SOAP: getItemsAvail(Shipment_method: $ShipmentMethod, Shipment_date: $ShipmentDate, Cat_id: $parentID)";
			$xml = module('soap:exec:getItemsAvail', $data);
			if (!is_array($xml)){
				$log[]= "SOAP ERROR: getItemsAvail(Cat_id: $parentID)";
				return;
			}
			//	Если нет товаров, то обработать следующий каталог
			if (!$xml){
				$synch['thisCatalog']	= NULL;
				unset($passPrice[$parentID]);
				continue;
			};

			$passPriceProduct = array();
			foreach($xml as $var){
				$passPriceProduct[$var->No] = array(
					'PriceClient'		=> $var->PriceClient,
					'AvailableClient'	=> $var->AvailableClient
					);
			}
		}

		$compilePrices = compileMerlionPercent($pricePercent[$parentID]);
		foreach($passPriceProduct as $itemID => $price)
		{
			if (sessionTimeout() < 2) return;
			$article= ":$parentID:$itemID";
			@$id	= $avalibleProduct[$article];
			if ($id){
				$p	= getMerlionPercent($compilePrices, $price['PriceClient']);
				$db->setValue($id, 'price_merlion', round($p * $Currency));
				
				$d = array();
				$d['fields']['any']['merlion']	= array(
					':PriceClient' => $price['PriceClient'],
					':AvailableClient' =>  $price['AvailableClient'],
					':PriceCurrency' =>  $Currency,
					':PriceRule' => $pricePercent[$parentID],
					':priceDate' => time()
					);
				m("doc:update:$id:edit", $d);
				$db->clearCache();
			}
			unset($passPriceProduct[$itemID]);
		}
		$synch['thisCatalog']	= NULL;
		$passPriceProduct		= NULL;
		unset($passPrice[$parentID]);
	}
	return count($passPrice) == 0;
} ?>
<? function merlionImportImage(&$synch){
	/*********************************************/
	//	Изображения товаров
	$ini		= getCacheValue('ini');
	@$merlion	= $ini[':merlion'];
	if (!@$merlion['synchImages']) return true;

	$passImage	= &$synch['passImage'];
	$copyImages	= &$synch['copyImages'];
	$sizeImages	= &$synch['sizeImages'];
	@$log		= &$synch['log'];

	$db		= module('doc');
	$db->sql= '';

	foreach($passImage as $article => $id)
	{
		if (sessionTimeout() < 5) return;
		
		$db->clearCache();
		$data	= $db->openID($id);
		if (!@$data['price']){
			unset($passImage[$article]);
			continue;
		}
		saveMerlionSynch($synch);
		list(,$parentID, $itemID) = explode(':', $article);

		$d = array();
		$d['Cat_id']	= $parentID;
		$d['Item_id']	= $itemID;
		$xml = module('soap:exec:getItemsImages', $d);

		$gallery	= array();
		$imageSize	= 0;
		$imageName	= '';
		foreach($xml as $image)
		{
			if ($image->ViewType != 'v') continue;
			if (strpos($image->FileName, 'v01')){
				if ($image->Size < $imageSize)	continue;
				$imageSize = $image->Size;
				$imageName = $image->FileName;
			}else{
				$fileName	= $image->FileName;
				$fileName	= preg_replace('#(_v\d+_).*#', '', $fileName);
				$thisData	= $gallery[$fileName];
				if ($thisData && $image->Size < $thisData[1])	continue;
				$gallery[$fileName]	= array($image->FileName, $image->Size);
			}
		}
		
		$imageFolder= $db->folder($id);
		$title		= docTitleImage($id);
		if ($imageName && basename($imageName) != basename($title))
		{
			$imageURL	= "http://img.merlion.ru/items/$imageName";
			$imageData	=  module("soap:curl:$imageURL");
			if (!$imageData){
				$log[]= "CURL ERROR: Load image from $imageURL";
				return;
			}

			delTree("$imageFolder/Title/");
			makeDir("$imageFolder/Title/");
			@file_put_contents("$imageFolder/Title/$imageName", $imageData);
			$sizeImages += $imageSize;
			++$copyImages;
			$bHasImae	= 'yes';
			if ($title) m("doc:recompile:$id");
		}else{
			$bHasImae	= $imageName?'yes':'no';
		}
		//	Удалить ошибочно добавленне, после обновления удалить код
		$title	= basename($title);
		unlink("$imageFolder/Gallery/$title");

		foreach($gallery as $data)
		{
			list($imageName, $imageSize) = $data;
			$path	= "$imageFolder/Gallery/$imageName";
			if (is_file($path)) continue;
			
			$imageURL	= "http://img.merlion.ru/items/$imageName";
			$imageData	=  module("soap:curl:$imageURL");
			if ($imageData){
				makeDir("$imageFolder/Gallery/");
				@file_put_contents($path, $imageData);
				$sizeImages += $imageSize;
				++$copyImages;
			}else{
				$log[]= "CURL ERROR: Load image from $imageURL";
				return;
			}
		}
		
		@$prop	= $data['fields']['any']['merlion'];
		$prop[':merlion_image'] = $bHasImae;
		
		$d								= array();
		$d['fields']['any']['merlion']	= $prop;
		m("doc:update:$id:edit", $d);
		
		unset($passImage[$article]);
		saveMerlionSynch($synch);
	}
	return count($passImage) == 0;
}?>
<? function merlionGetProperty(&$synch, $parentID, $itemID)
{
	@$log		= &$synch['log'];
	@$property	= &$synch['merlionProperty'];

	if (!is_array($property) && $property < 1 && synchMerlionTimeout($synch) < 120)
	{
//		$log[]= "SOAP: getItemsProperties(cat_id: $parentID)";
		saveMerlionSynch($synch);
		$data = array();
		$data['cat_id']	= $parentID;
		$xml = module('soap:exec:getItemsProperties', $data);
		if (!is_array($xml)){
			$log[]= "SOAP ERROR: getItemsProperties(cat_id: $parentID) - no XML";
			@$property = (int)$property + 1;
			return;
		}else{
			$property = array();
			foreach($xml as &$prop) $property[$prop->No][] = $prop;
		}
	}
	if (is_array($property)){
		@$prop = $property[$itemID];
	}else{
		if ($property != 100){
			$log[]= "SOAP ERROR: getItemsProperties(cat_id: $parentID), use alternative method";
		}
//		$log[]= "SOAP: getItemsProperties(cat_id: $parentID, Item_id: $itemID)";
		$property		= 100;
		saveMerlionSynch($synch);

		$data			= array();
		$data['Cat_id']	= $parentID;
		$data['Item_id']= $itemID;
		$prop 			= module('soap:exec:getItemsProperties', $data);
	}
	if (!is_array($prop)) $prop = array();
	return $prop;
}
function getMerlionPercent(&$merlionpercent, $val)
{
	$minPercent	= 1;
	$val		= (float)$val;
	foreach($merlionpercent as &$percent){
		list($p, $v1, $v2) = $percent;
		$p	= $p / 100 + 1;
		if ($v1 || $v2){
			if ($val >= $v1 && $val < $v2)
				return $val * $p;
		}else{
			$minPercent = $p;
		}
	}
	return $val * $minPercent;
}
function compileMerlionPercent($val)
{
	$percent	= array();
	$val		= explode(',', $val);
	foreach($val as $row)
	{
		$row	= trim($row);
		if (preg_match('#^([\d\.]+)\s*\((\d*)-(\d*)\)$#', $row, $val))
		{
			$p	= (float)$val[1];
			$percent[]	= array($p, $val[2], $val[3]);
		}else{
			$p	= (float)$row;
			$percent[]	= array($p, 0, 0);
		}
	}
	return $percent;
}
 ?>

