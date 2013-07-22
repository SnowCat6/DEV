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

	if (!merlionImportProduct($synch))	return;
	if (!merlionImportPrice($synch))	return;

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
		$itemID	= $prop[':merlion_itemID'];
		$names[$id]	= $data['title'];
		$avalible[$itemID] 		= $id;
		$pricePercent[$itemID]	= $prop[':merlion_price'];

		$parentID			= $prop[':merlion_parentID'];
		$parents[$parentID] = $parentID;
	}
	//	Удалить все возможные роительские каталоги, для исключения дублированных запросов
	foreach($parents as $parentID){
		if (!isset($avalible[$parentID])) continue;
		unset($avalible[$parentID]);
	}
	$synch['passProduct']	= $avalible;
	$synch['passPrice']		= $avalible;
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
	
	$synch['duplicate']	=	array();
	
	while($data = $db->next())
	{
		$id			= $db->id();
		$prop		= $data['fields']['any']['merlion'];
		$parentID	= $prop[':merlion_parentID'];
		$itemID		= $prop[':merlion_itemID'];
		if (!$itemID) continue;

		$article	= ":$parentID:$itemID";
		$avalible[$article] = $id;
		$passImage[$article]= $id;
		$hasProps[$id]		= $prop[':merlion_property'];
		
//		$iid	= $synch['duplicate'][$data['title']];
//		if (!$iid) $iid = $id;
		$synch['duplicate'][$data['title']]	= $id;
//		$synch['duplicate'][$id]			= $iid;

		if (sessionTimeout() < 1) return;
	}

	merlionFlush($synch);
	return true;
}
function merlionImportProduct(&$synch)
{
	$ini		= getCacheValue('ini');
	$merlion	= $ini[':merlion'];
	if (!$merlion['synchProduct']) return true;

	$property	= &$synch['merlionProperty'];
	$product 	= &$synch['merlionProduct'];
	$names 		= &$synch['names'];
	$pass		= &$synch['pass'];
	$passParent	= &$synch['passParent'];
	$added		= &$synch['added'];
	$updated	= &$synch['updated'];
	$dones		= &$synch['dones'];
	$log		= &$synch['log'];
	$passImage	= &$synch['passImage'];
	$hasProps	= &$synch['hasProps'];
	$avalibleProduct	= &$synch['avalibleProduct'];
	$passProduct		= &$synch['passProduct'];

	$db		= module('doc');
	$db->sql= '';
	//	Начать ипортирование до таймаута операции или пока есть каталоги
	//	Пройти по одному через каталоги
	foreach($passProduct as $parentID => $thisID)
	{
		merlionFlush($synch);
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
				continue;
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
			unset($xml);
		}

		foreach($product as $itemID => &$item)
		{
			$db->clearCache();
			merlionFlush($synch);
			if (sessionTimeout() < 2) return;
			
			$article= ":$parentID:$itemID";
			if (isset($pass[$article])){
				unset($product[$itemID]);
				continue;
			}

			$bUpdate	= false;
			$d			= array();
			$prop		= array();
			
			$id		= $avalibleProduct[$article];
//			if ($id) $id = $synch['duplicate'][$id];
			if (!$id){
				$id		= $synch['duplicate'][$item->Name];
				if ($id){
					$data	= $db->openID($id);
					$prop	= $data['fields']['any']['merlion'];
					$prop[':merlion_parentID']		= $parentID;
					$prop[':merlion_itemID']		= $itemID;
					$d['fields']['any']['merlion']	= $prop;
				}
			}

			if ($hasProps[$id] != 'yes')
			{
				$pr = merlionGetProperty($synch, $parentID, $itemID);
				if (!is_array($pr)){
					if (sessionTimeout() > 10) continue;
					return;
				}
				
				if ($pr){
					$d[':property']	= $pr;
					
					$data	= $db->openID($id);
					$prop	= $data['fields']['any']['merlion'];
					$prop[':merlion_parentID']		= $parentID;
					$prop[':merlion_itemID']		= $itemID;
					$prop[':merlion_property']		= $pr?'yes':'no';
					$d['fields']['any']['merlion']	= $prop;
				}
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
				if ($d){
//					print_r($d); die;
					$iid = moduleEx("doc:update:$id:edit", $d);
					$updated++;
				}else $iid = $id;
			}
			$dones++;
			$avalibleProduct[$article] = $iid;
			$pass[$article] 	= $iid;
			$passImage[$article]= $iid;
			unset($product[$itemID]);
		}

		$synch['thisCatalog']	= NULL;
		$product	= NULL;
		$property	= NULL;
		unset($passProduct[$parentID]);
	}
	return count($passProduct) == 0;
}

function merlionImportPrice(&$synch)
{
	$ini		= getCacheValue('ini');
	$merlion	= $ini[':merlion'];
	if (!$merlion['synchPrice']) return true;
	/*********************************************/
	//	Цены товаров
	$ini			= getCacheValue('ini');
	$merlion		= $ini[':merlion'];
	
	$Currency		= explode(':', $merlion['Currency']);
	$Currency		= (float)$Currency[1];
	if (!$Currency) $Currency = 1;

	$ShipmentMethod= $merlion['ShipmentMethod'];
	$ShipmentDate	= $merlion['ShipmentDate'];
	if (!$ShipmentMethod || !$ShipmentDate) return true;

	$log				= &$synch['log'];
	$names	 			= &$synch['names'];
	$passPrice			= &$synch['passPrice'];
	$passPriceProduct	= &$synch['passPriceProduct'];
	$avalibleProduct	= &$synch['avalibleProduct'];
	$pricePercent		= &$synch['pricePercent'];
	$priceDones			= &$synch['priceDones'];

	$db					= module('doc');
	$db->sql			= '';
	
	foreach($passPrice as $parentID => $thisID)
	{
		merlionFlush($synch);
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
				if (sessionTimeout() > 10) continue;
				return;
			}
			//	Если нет товаров, то обработать следующий каталог
			if (!$xml){
				$synch['thisCatalog']	= NULL;
				unset($passPrice[$parentID]);
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
		foreach($passPriceProduct as $itemID => $price)
		{
			$db->clearCache();
			merlionFlush($synch);
			if (sessionTimeout() < 2) return;
			$article= ":$parentID:$itemID";
			$id		= $avalibleProduct[$article];
			if ($id) $id = $synch['duplicate'][$id];
			if ($id){
				$data	= $db->openID($id);
				$prop	= $data['fields']['any']['merlion'];
				$p		= getMerlionPercent($compilePrices, $price['PriceClient']);
				$db->setValue($id, 'price_merlion', round($p * $Currency));
				
				$d = array();
				$prop[':PriceClient']		= $price['PriceClient'];
				$prop[':AvailableClient']	= $price['AvailableClient'];
				$prop[':PriceCurrency']		= $Currency;
				$prop[':PriceRule']			= $pricePercent[$parentID];
				$prop[':priceDate']			= time();
				$d['fields']['any']['merlion']	= $prop;
				m("doc:update:$id:edit", $d);
				++$priceDones;
			}else{
//				echo $article; die;
			}
			unset($passPriceProduct[$itemID]);
		}
		$synch['thisCatalog']	= NULL;
		$passPriceProduct		= NULL;
		unset($passPrice[$parentID]);
	}
	return count($passPrice) == 0;
}
function merlionImportImage(&$synch){
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
	$db->setCache(false);

	foreach($passImage as $article => $id)
	{
		$db->clearCache();
		if (sessionTimeout() < 5) return;
		
		if ($id) $id = $synch['duplicate'][$id];
		$data	= $db->openID($id);
		if (!$data['price']){
			unset($passImage[$article]);
			continue;
		}
//		saveMerlionSynch($synch);
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
				continue;
			}

			delTree("$imageFolder/Title/");
			makeDir("$imageFolder/Title/");
			file_put_contents("$imageFolder/Title/$imageName", $imageData);
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
				file_put_contents($path, $imageData);
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
	foreach($xml as &$prop) $p[$prop->PropertyName]	= $p->Value;
	return $p;
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

