<?
//	+function import_txtSource
function import_txtSource(&$val, &$sources)
{
	$files	= getFiles(importFolder, '\.(txt|csv)$');
	foreach($files as $name => $path){
		$synch			= module("baseSynch:$path.synch/synch.txt");
		$synch->setValue('source', $path);
		$sources[$name]	= $synch;
	}
}

//	+function import_txtSynch
function import_txtSynch(&$val, &$names)
{
	$sources	= array();
	import_txtSource($sources, $sources);
	
	reset($names);
	list(, $name)	= each($names);
	$synch	= $sources[$name];
	if (!$synch) return;

	$synch->unlock();
	if ($synch->lockTimeout()) return;
	$synch->lock();
	$synch->read();
	
	doTxtImport($synch);

	if ($synch->write()){
		$synch->unlock();
	}
}
?>
<? function doTxtImport(&$synch)
{
	$db	= new importBulk();
	$f	= fopen($synch->getValue('source'), 'r');
	while(!feof($f))
	{
		$row	= fgets($f);
		$row	= iconv('windows-1251', 'utf-8', $row);
		
		$row	= explode("\t", $row);
		foreach($row as &$val) $val	= trim($val);
		
		doTxtImport2($synch, $db, $row);
	}
	fclose($f);
}
?>
<? function doTxtImport2(&$synch, &$db, &$row)
{
	if ($r = rowIsRootCatalog($synch, $row)){
		//	Импортировать основной каталог первого уровня
		$prop			= array();
		$prop['id']		= $r['article'];
		$prop['name']	= $r['name'];
		//	Указать свойсво, что этот каталог отображаеться на карте сайта
		$db->addItem('catalog', $r['article'], $r['name'], $prop);
		//	Задать каталог как основной каталог
		$synch->setValue('rowParentName',	'');
		$synch->setValue('rowParentID', 	$r['article']);
		//	Если вдруг начнется импорт товаров, то импортировать в него
		$synch->setValue('rowRootCatalog',	$r['article']);
	}else
	if ($r = rowIsFormat($synch, $row)){
		$synch->setValue('rowFormat', $r);
	}else
	if ($r = rowIsCatalog($synch, $row)){
		//	Если основной каталог есть, то это второстепенный каталог
		$prop				= array();
		$prop['id']			= $r['article'];
		$prop['name']		= $r['name'];
		$db->addItem('catalog', $r['article'], $r['name'], $prop);
		//	Запомнить название родительского каталога для товаров
		$synch->setValue('rowParentName',	$r['name']);
		$synch->setValue('rowParentID', 	$r['article']);
	}else
	if ($r = rowIsProduct($synch, $row)){
		//	Получить артикул текущего каталога
		$parent	= $synch->getValue('rowParentID');
		//	Импортируем товар
		$prop			= $r;
		$prop['id']		= $r['article'];
		$prop['name']	= $r['name'];
		$prop['price']	= makeFloatPrice($r['price']);
		$prop['price2']	= makeFloatPrice($r['price2']);
		$prop['categoryId']	= $parent;
		$prop[':property']	= $r[':property'];
		$db->addItem('product', $r['article'], $r['name'], $prop);
	}else{
		$line	= trim($line);
		if ($line) $synch->log("Not imported: $line");
		else{
			//	Пустая строка начинает новый родительский каталог, все параметры сбрасываются
			$synch->setValue('rowParentName',	'');
			$synch->setValue('rowRootCatalog',	'');
			$synch->setValue('rowParentID', 	'');
			$synch->setValue('rowFormat', 		'');
		}
	}
}
function makeFloatPrice($price){
	$price= str_replace(',', '.', $price);
	return (float)preg_replace('#([^\d\.]+)#', '', $price);
}
function rowIsRootCatalog(&$synch, &$row)
{
	//	Родительский каталог не должен быть определен
	if ($synch->getValue('rowRootCatalog')) return;
	//	Первая колонка должна содержать имя
	if ($row[0] == '') return;
	
	//	Болше ни одна колонка не должна содержать значение
	reset($row);
	foreach($row as $ix => &$val){
		if (!$val) continue;
		if ($ix) return;
	}

	return array(
		'name'		=> $row[0],
		'article'	=> $row[0]
		);
}
function rowIsCatalog(&$synch, &$row)
{
	//	Родительский каталог должен быть определен
	$parent	= $synch->getValue('rowRootCatalog');
	//	Первая колонка должна быть заполнена
	if ($row[0] == '') return;

	//	Болше ни одна колонка не должна содержать значение
	reset($row);
	foreach($row as $ix => &$val){
		if (!$val) continue;
		if ($ix) return;
	}
	
	return array(
		'name'		=> $row[0],
		'article'	=> "$parent/$row[0]",
		'parent'	=> $parent
	);
}
function rowIsFormat(&$synch, &$row)
{
	$names	= array(
		'РРЦ'		=> 'rrc',
		'Код товара' => 'article',
		'Артикул' 	=> 'code',
		'Ед. изм.'	=> 'ed',
		'Цена , у.е.'	=> 'price',
		'Цена, у.е.'	=> 'price',
		'Цена, руб.'	=> 'price2',
		'Условия поставки'		=> 'delivery',
		'Наименование товара'	=> 'name'
		);
	
	//	Если хоть одно значение в колонке содержит название, то это строка формата данных
	reset($row);
	$format	= array();
	foreach($row as $ix => &$val){
		if (!isset($names[$val])) continue;
		$format[$ix]	= $names[$val];
	}

	return $format;
}
function rowIsProduct(&$synch, &$row)
{
	$format	= $synch->getValue('rowFormat');
	if (!$format || !count($format) || count($row) < count($format)) return;
	
	reset($row);
	$data	= array();
	foreach($row as $ix => &$val){
		$name	= $format[$ix];
		if (!$name) continue;
		$data[$name]	= $val;
	}
	if (!$data) return;
	
	$title	= $data['name'];
	$brands	= $synch->getValue('rowBrands');
	foreach($brands as &$brand){
		if (is_bool(strpos($title, $brand))) continue;
		$data[':property']['Бренд']	= $brand;
		break;
	}

	return $data;
}
function rowCacheBrands(&$synch)
{
	$brands	= array();
	$props	= module("prop:count:Бренд", array('type'=>'product'));
	foreach($props as $names){
		foreach($names as $name => $count){
			$brands[$name]	= $name;
		}
	}
	$synch->setValue('rowBrands', $brands);
}
?>