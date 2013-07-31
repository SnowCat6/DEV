<? function merlion_synch($val, &$data)
{
	if (defined('_CRON_'))
	{
		$ini		= getCacheValue('ini');
		$merlion	= $ini[':merlion'];
		$synchPeriod= $merlion['updateEveryHour'];
		if ($synchPeriod < 2) $synchPeriod = 6;
		$lastSynch	= $data['lastSynch'];
		//	Каждые $synchPeriod часов синхронизируем
		if (time() - $lastSynch > $synchPeriod*60*60)
		{
			echo 'Синхронизация началась';
			$data['lastSynch']	= time();
			clearMerlionSynch();
		}
		
		merlionSych();
		
		$synch		= readMerlionSynch();
		if ($synch['action'] == 'complete')
		{
			$lastSynch	= $data['lastSynch'];
			$nextSynch	= $synchPeriod*60 - (time() - $lastSynch)/60;
			if ($nextSynch < 60) $nextSynch = round($nextSynch) . ' минут';
			else $nextSynch = round($nextSynch / 60). ' часов';
			echo "<h2>Синхронизация завершена, следующая синхронизация через $nextSynch</h2>";
		}else{
			echo '<h2>Синхронизация не зваершена</h2>';
		}
		merlionInfo($synch);
		$log = &$synch['log'];
		if (is_array($log) && $log){
			echo '<h2>Лог:</h2>';
			echo '<div>', implode('</div><div>', $log), '</div>';
		}
		return;
	}
	
	merlionSych();
}
function merlionSych()
{
	$synch	= readMerlionSynch();
	if (is_file(merlionLock) && time() - filectime($lockFile) < $synch['importTimeout']) return;

	$val 	= time();
	file_put_contents(merlionLock, $val);

	if (!is_array($synch)){
		$synch = array();
		$synch['thisFile']		= merlionFile;
		file_put_contents(merlionFile, serialize($synch));
	}
	$synch['importStart']	= time();
	$synch['importTimeout']	= sessionTimeout();
	saveMerlionSynch($synch);

	doMerlionImport($synch);
	saveMerlionSynch($synch);
	//	Сохранить состояние
	unlink(merlionLock);
	return true;
}
function doMerlionImport(&$synch)
{
	$ini		= getCacheValue('ini');
	$merlion	= $ini[':merlion'];
	
	merlionLogin();

	if (!merlionImportInit($synch)) 	return;

	if (!merlionCacheCatalog($synch))	return;
	if (!merlionCacheProduct($synch))	return;

	if (!merlionImportPriceAndProduct($synch))	return;
//	if (!merlionImportProduct($synch))	return;
//	if (!merlionImportPrice($synch))	return;

	merlionImportComplete($synch);
	
	if ($merlion['synchYandex'] && !isset($synch['YandexXML'])){
		if (!moduleEx('import:YandexXML', $synch)) return;
		$synch['YandexXML'] = true;
	}

	if (!merlionImportImage($synch))	return;

	return true;
}
function merlionImportInit(&$synch)
{
	if (isset($synch['action'])) return true;
	$synch['action'] 	= 'init';
	
	$currency	= getCurrencyRate();
	if ($currency){
		$ini				= getCacheValue('ini');
		list($code, $val)	= each($currency);
		$ini[':merlion']['Currency'] = "$code:$val";
		setIniValues($ini);
	}

	$property	= &$synch['merlionProperty'];
	$product 	= &$synch['merlionProduct'];
	$pass		= &$synch['pass'];
	$passParent= &$synch['passParent'];
	$added		= &$synch['added'];
	$updated	= &$synch['updated'];
	$dones		= &$synch['dones'];
	$priceDones	= &$synch['priceDones'];
	$log		= &$synch['log'];
	$passImage	= &$synch['passImage'];
	$copyImages	= &$synch['copyImages'];
	$sizeImages	= &$synch['sizeImages'];
	$pricePercent		= &$synch['pricePercent'];

	//	Подготовить структуры для импорта
	$log			= array();
	$pass			= array();
	$passParent		= array();
	$added			= 0;
	$updated		= 0;
	$dones			= 0;
	$priceDones		= 0;
	$copyImages		= 0;
	$sizeImages		= 0;
	$pricePercent	= array();
	
	$db			= module('doc');
	$db->setCache(false);
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

function merlionImportComplete(&$synch)
{
	if ($synch['action'] == 'complete') return true;

	$ini		= getCacheValue('ini');
	@$merlion	= $ini[':merlion'];
	if ($merlion['synchPrice'])
	{
		$db			= module('doc');
		$db->setCache(false);
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
}
function merlionArticle($parentID, $itemID){
	return ":$itemID";
}
function merlionImportPriceAndProduct(&$synch)
{
	$ini		= getCacheValue('ini');
	$merlion	= $ini[':merlion'];
	if (!$merlion['synchPrice']) return true;

	$Currency		= explode(':', $merlion['Currency']);
	$Currency		= (float)$Currency[1];
	if (!$Currency) $Currency = 1;

	$ShipmentMethod	= $merlion['ShipmentMethod'];
	$ShipmentDate	= $merlion['ShipmentDate'];
	if (!$ShipmentMethod || !$ShipmentDate) return true;

	$names 			= &$synch['names'];
	$log			= &$synch['log'];
	$pricePercent	= &$synch['pricePercent'];
	$avalible 		= &$synch['avalible'];
	$passImage		= &$synch['passImage'];
	$passPriceProduct	= &$synch['passPriceProduct'];
	$avalibleProduct	= &$synch['avalibleProduct'];
	
	$added		= &$synch['added'];
	$updated	= &$synch['updated'];
	$dones		= &$synch['dones'];
	
	$db		= module('doc');
	$db->sql= '`deleted`=0';
	
	foreach($avalible as $parentID => $thisID)
	{
		$synch['thisCatalog']	= $names[$thisID];
		if (sessionTimeout() < 10) return;

		if (!$passPriceProduct)
		{
			$data 					= array();
			$data['Cat_id'] 		= $parentID;
			$data['Shipment_method']= $ShipmentMethod;
			$data['Shipment_date']	= $ShipmentDate;
			$data['Only_avail']		= 1;
//			$log['shipment']= "SOAP: getItemsAvail(Shipment_method: $ShipmentMethod, Shipment_date: $ShipmentDate, Cat_id: $parentID)";
			$xml = module('soap:exec:getItemsAvail', $data);
			if (!is_array($xml))
			{
				$log[]= "SOAP ERROR: getItemsAvail(Cat_id: $parentID)";
				if (sessionTimeout() > 10) continue;
				return;
			}
			//	Если нет товаров, то обработать следующий каталог
			if (!$xml){
				$synch['thisCatalog']	= NULL;
				unset($avalible[$parentID]);
				continue;
			};

			$passPriceProduct = array();
			foreach($xml as &$var){
				$passPriceProduct[$var->No] = array(
					'PriceClient'		=> $var->PriceClient,
					'AvailableClient'	=> $var->AvailableClient
					);
			}
		}
		
		$compilePrices = compileMerlionPercent($pricePercent[$parentID]);
		foreach($passPriceProduct as $itemID => &$priceItem)
		{
			if (sessionTimeout() < 5) return;

			$merlion						= array();
			$merlion[':PriceClient']		= $priceItem['PriceClient'];
			$merlion[':AvailableClient']	= $priceItem['AvailableClient'];
			$merlion[':PriceCurrency']		= $Currency;
			$merlion[':PriceRule']			= $pricePercent[$parentID];

			$d		= array();
//			$article= ":$parentID:$itemID";
			$article= merlionArticle($parentID, $itemID);
			$id		= $avalibleProduct[$article];

			if ($id){
				$data		= $db->openID($id);
				$propBase	= $data['fields']['any']['merlion'];
				dataMerge($merlion, $propBase);
				$prop		= $merlion;
				
				if ($prop[':merlion_property'] != 'yes2'){
					$property	= merlionGetProperty($synch, $parentID, $itemID);
					if ($property){
						$d[':property']				= $property;
						$prop[':merlion_property']	= 'yes2';
					}
				}
				
				if (hashData($propBase) != hashData($prop)){
					$prop[':priceDate']				= time();
					$d['fields']['any']['merlion']	= $prop;
					module("doc:update:$id:edit", $d);
					++$updated;
				}
			}else{
				$item	= getMerlionProduct($synch, $parentID, $itemID);
				if (!$item) continue;
				
				$property	= merlionGetProperty($synch, $parentID, $itemID);
				if (!is_array($property)) $property = array();
				
				$prop						= $merlion;
				$prop[':merlion_parentID']	= $parentID;
				$prop[':merlion_itemID']	= $itemID;
				$prop[':merlion_property']	= $property?'yes2':'no';
				$prop[':priceDate']			= time();
				
				$property[':import']		= 'merlion';
				
				$d['title']		= $item->Name; 
				$d[':property']	= $property;
				$d['fields']['any']['merlion']	= $prop;

				$id		= module("doc:update:$thisID:add:product", $d);

				if ($id){
					++$added;
					$avalibleProduct[$article]	= $id;
					$passImage[$article]		= $id;
				}else{
					$log[] = m('display:message');
				}
			}
			if ($id){
				$price	= getMerlionPercent($compilePrices, $priceItem['PriceClient'], $Currency);
				$db->setValue($id, 'price_merlion',	round($price));
				$db->setValue($id, 'price',			round($price));
			}
			++$dones;
			unset($passPriceProduct[$itemID]);
		}
		unset($avalible[$parentID]);
	}
	$synch['thisCatalog']	= '---';
	return count($avalible) == 0;
}
function getMerlionProduct(&$synch, $parentID, $itemID)
{
	$merlionItems	= &$synch['merlionItems'];
	if (!isset($merlionItems[$parentID]))
	{
		$log			= &$synch['log'];
		$merlionItems	= array();
		
		$data			= array();
		$data['Cat_id'] = $parentID;
		$xml			= module('soap:exec:getItems', $data);
		if (!is_array($xml)){
			$log[]	= "SOAP ERROR: getItems(Cat_id: $parentID)";
			return;
		}
		//	Если нет товаров, то обработать следующий каталог
		if (!$xml) return $xml;

		$product = &$merlionItems[$parentID];
		foreach($xml as &$item){
			$product[$item->No]	= $item;
		}
		unset($xml);
	}
	return $merlionItems[$parentID][$itemID];
}
function merlionCacheCatalog(&$synch)
{
	$avalible 		= &$synch['avalible'];
	if (isset($avalible)) return true;
	
	$names 		= &$synch['names'];
	$pricePercent	= &$synch['pricePercent'];
	
	$avalible 	= array();
	$names		= array();

	$db	= module('doc');
	$db->setCache(false);
	$s	= array();
	$s['prop'][':import']	= 'merlion';
	$s['type']				= 'catalog';

	$db->sql= '';
	$db->open(doc2sql($s));
	
	$parents	= array();
	while($data = $db->next())
	{
		$id		= $db->id();
		$prop	= $data['fields']['any']['merlion'];
		if ($prop[":merlion_synch"] != 'yes') continue;

		$itemID		= $prop[':merlion_itemID'];
		$names[$id]	= $data['title'];
		$avalible[$itemID] 		= $id;
		$pricePercent[$itemID]	= $prop[':merlion_price'];

		$parentID			= $prop[':merlion_parentID'];
		$parents[$itemID]	= $parentID;
	}
	//	Задать наследуемую наценку от родителя
	do{
		$bNew	= false;
		foreach($pricePercent as $itemID => &$price){
			if ($price) continue;
			$new	= $pricePercent[$parents[$itemID]];
			$bNew	= $new != $price;
			$price	= $new;
		}
	}while($bNew);
	//	Удалить все возможные роительские каталоги, для исключения дублированных запросов
	foreach($parents as $parentID){
		if (!isset($avalible[$parentID])) continue;
		unset($avalible[$parentID]);
	}
//	$synch['passProduct']	= $avalible;
//	$synch['passPrice']		= $avalible;
	merlionFlush($synch);

	return sessionTimeout() > 60;
}
function merlionCacheProduct(&$synch)
{
	$avalible	= &$synch['avalibleProduct'];
	if (isset($avalible)) return true;

	$passImage	= &$synch['passImage'];
	$avalible 	= array();

	$hasProps	= &$synch['hasProps'];
	$hasProps 	= array();
	
	$db	= module('doc');
	$db->setCache(false);
	$db->sql	= '`deleted`=0';
	$db->order	= '`doc_id` ASC';
	
	$s	= array();
	$s['prop'][':import']	= 'merlion';
	$s['type']				= 'product';
	$db->open(doc2sql($s));
	
	while($data = $db->next())
	{
		$id			= $db->id();
		$prop		= $data['fields']['any']['merlion'];

		$parentID	= $prop[':merlion_parentID'];
		$itemID		= $prop[':merlion_itemID'];
		if (!$itemID) continue;

//		$article	= ":$parentID:$itemID";
		$article	= merlionArticle($parentID, $itemID);
		$avalible[$article] = $id;
		$passImage[$article]= $id;
		$hasProps[$id]		= $prop[':merlion_property'];

		if (sessionTimeout() < 1) return;
	}
	merlionFlush($synch);
	return true;
}

function merlionImportImage(&$synch)
{
	/*********************************************/
	//	Изображения товаров
	$ini		= getCacheValue('ini');
	$merlion	= $ini[':merlion'];
	if (!$merlion['synchImages']) return true;

	$passImage	= &$synch['passImage'];
	$copyImages	= &$synch['copyImages'];
	$doneImages	= &$synch['doneImages'];
	$sizeImages	= &$synch['sizeImages'];
	$log		= &$synch['log'];

	$db		= module('doc');
	$db->sql= '`deleted`=0';

	foreach($passImage as $article => $id)
	{
		$db->clearCache();
		if (sessionTimeout() < 5) return;
		
		$data	= $db->openID($id);
		$prop	= $data['fields']['any']['merlion'];
		if (!$data['price'] || $prop[':merlion_image' == 'yes']){
			unset($passImage[$article]);
			continue;
		}
		list(,$parentID, $itemID) = explode(':', $article);

		$folders	= getItemsImages($parentID, $itemID);
		$folders	= $folders[$itemID];
		if (!$folders){
			++$doneImages;
			unset($passImage[$article]);
			continue;
		}

		$bHasImage	= $folders?'yes':'no';
		$imageFolder= $db->folder($id);
		$doCopy		= array();
		
		foreach($folders as $folderName => &$images)
		{
			$thisFolder	= "$imageFolder/$folderName";
			if ($folderName == 'Title')
			{
				list(, $file) = each($images);
				$thisFile	= "$thisFolder/$file[Image]";
				if (is_file($thisFile)) continue;

				$file['DeleteFolder']	= $thisFolder;
				$file['Destination']	= $thisFile;
				$doCopy[]				= $file;
				continue;
			}
			foreach($images as $file){
				$thisFile	= "$thisFolder/$file[Image]";
				if (is_file($thisFile)) continue;
				$file['Destination']= $thisFile;
				$doCopy[]			= $file;
			}
		}

		foreach($doCopy as $file)
		{
			$imageURL	= "http://img.merlion.ru/items/$file[Image]";
			$imageData	=  module("soap:curl:$imageURL");
			if (!$imageData){
				$log[]= "CURL ERROR: Load image from $imageURL";
				return;
			}
			$DeleteFolder	= $file['DeleteFolder'];
			if ($DeleteFolder) delTree($DeleteFolder);
			
			$Destination	= $file['Destination'];
			makeDir(dirname($Destination));
			file_put_contents($Destination, $imageData);

			$sizeImages += $file['Size'];
			$bHasImage	= 'yes';
			++$copyImages;
		}
		++$doneImages;

		$d								= array();
		$prop[':merlion_image'] 		= $bHasImage;
		$d['fields']['any']['merlion']	= $prop;
		m("doc:update:$id:edit", $d);
		
		unset($passImage[$article]);
		merlionFlush($synch);
	}
	return count($passImage) == 0;
}?>
<? function merlionGetProperty(&$synch, $parentID, $itemID)
{
	$log		= &$synch['log'];
	$property	= &$synch['merlionProperty'];
/*
	if (!is_array($property) && $property < 1 && synchMerlionTimeout($synch) < 120)
	{
//		$log[]= "SOAP: getItemsProperties(cat_id: $parentID)";
		$data = array();
		$data['cat_id']	= $parentID;
		$xml = module('soap:exec:getItemsProperties', $data);
		if (!is_array($xml)){
			$log[]= "SOAP ERROR: getItemsProperties(cat_id: $parentID) - no XML";
			@$property = (int)$property + 1;
			if (sessionTimeout() < 5) return;
		}else{
			$property = array();
			foreach($xml as &$prop) $property[$prop->No][$prop->PropertyName]	= $prop->Value;
		}
	}
	merlionFlush($synch);
	if (is_array($property))
		return $property[$itemID];

	if ($property != 100){
		$log[]= "SOAP ERROR: getItemsProperties(cat_id: $parentID), use alternative method";
//		saveMerlionSynch($synch);
	}
//	$log[]= "SOAP: getItemsProperties(cat_id: $parentID, Item_id: $itemID)";
	$property		= 100;
*/
	$data			= array();
	$data['Cat_id']	= $parentID;
	$data['Item_id']= $itemID;
	$xml 			= module('soap:exec:getItemsProperties', $data);

	if (!is_array($xml)) return array();
	
	$p	= array();
	foreach($xml as &$prop) $p[$prop->PropertyName]	= $prop->Value;
	return $p;
}
function getMerlionPercent(&$merlionpercent, $val, $Currency)
{
	$minPercent	= 1;
	$val		= (float)$val * $Currency;
	foreach($merlionpercent as &$percent)
	{
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

