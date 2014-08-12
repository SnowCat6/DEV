<?
//	+function import_txtSource
function import_txtSource(&$val, &$sources)
{
	$files	= getFiles(importFolder, '\.(txt|csv)$');
	foreach($files as $name => $path)
	{
		$synch	= module("baseSynch:$path.synch/synch.txt");
		$synch->setValue('source', $path);
		$url	= getURL('import_txtSettings', "source=".urlencode($name));
		$synch->setValue('comment', "<a href=\"$url\">Настройки</a>");

		//	Encode
		$ini	= getCacheValue('ini');
		$encode	= $ini[':txtSettings']['encode'];
		if (!$encode) $encode = 'windows-1251';
		$synch->setValue('rowEncode', $encode);
		//	Header detect
		$names	= $ini[':txtImportFields'];
		if (!is_array($names)) $names = array();
		$fields	= array();
		foreach($names as $field=>$name2){
			$name2	= explode(';', $name2);
			foreach($name2 as $n){
				$n = trim($n);
				if ($n) $fields[$n]	= $field;
			}
		}
		$synch->setValue('rowNameFormat', $fields);

		$sources[$name]	= $synch;
	}
}

//	+function import_txtCancel
function import_txtCancel(&$val, &$names)
{
	$sources	= array();
	import_txtSource($sources, $sources);
	
	foreach($names as $name)
	{
		$synch	= $sources[$name];
		if (!$synch) continue;
		$synch->delete();
	}
}
//	+function import_txtDelete
function import_txtDelete(&$val, &$names)
{
	$sources	= array();
	import_txtSource($sources, $sources);
	
	foreach($names as $name)
	{
		$synch	= $sources[$name];
		if (!$synch) continue;
		unlink($synch->getValue('source'));
		$synch->delete();
		delTree(importFolder . "/$name.synch");
	}
}
//	+function import_txtSynch
function import_txtSynch(&$val, &$names)
{
	$sources	= array();
	import_txtSource($sources, $sources);
	
	foreach($names as $name)
	{
		$synch	= $sources[$name];
		if (!$synch) continue;
	
		$synch->unlock();
		if ($synch->lockTimeout()) return;

		$synch->lock();
		if (!$synch->getValue('status'))
		{
			$synch->setValue('status', 'import');
			rowCacheBrands($synch);
			//	Save settings
			$synch->write();
		}
		
		doTxtImport($synch);
	
		if ($synch->write()){
			$synch->unlock();
		}
	}
}
?>
<? function doTxtImport(&$synch)
{
	$encode	= $synch->getValue('rowEncode');
	
	$db		= new importBulk();
	$f		= fopen($synch->getValue('source'), 'r');
	while(true)
	{
		if (feof($f)){
			$synch->setValue('status', 'complete');
			break;
		}
		$row	= fgets($f);
		if ($encode != 'utf-8'){
			$row	= iconv($encode, 'utf-8', $row);
		}
		
		$row	= explode("\t", $row);
		foreach($row as &$val) $val	= trim($val);
		
		doTxtImport2($synch, $db, $row);
		
		$synch->flush();
	}
	fclose($f);
}
?>
<? function doTxtImport2(&$synch, &$db, &$row)
{
	if ($r = rowIsFormat($synch, $row)){
	}else
	if ($r = rowIsRootCatalog($synch, $row)){
		//	Импортировать основной каталог первого уровня
		$prop			= array();
		$prop['id']		= $r['article'];
		$prop['name']	= $r['name'];
		//	Указать свойсво, что этот каталог отображаеться на карте сайта
		$db->addItem($synch, 'catalog', $r['article'], $r['name'], $prop);
		//	Задать каталог как основной каталог
		$synch->setValue('rowParentName',	'');
		$synch->setValue('rowParentID', 	$r['article']);
	}else
	if ($r = rowIsCatalog($synch, $row)){
		//	Если основной каталог есть, то это второстепенный каталог
		$db->addItem($synch, 'catalog', $r['article'], $r['name'], $r);
		//	Запомнить название родительского каталога для товаров
		$synch->setValue('rowParentName',	$r['name']);
		$synch->setValue('rowParentID', 	$r['article']);
	}else
	if ($r = rowIsProduct($synch, $row)){
		//	Получить артикул текущего каталога
		if (!$r['parent'])	$r['parent']	= $synch->getValue('rowParentID');
		$db->addItem($synch, 'product', $r['article'], $r['name'], $r);
	}else{
		$line	= trim($line);
		if ($line) $synch->log("Not imported: $line");
		else{
			//	Пустая строка начинает новый родительский каталог, все параметры сбрасываются
			rowResetScan($synch);
		}
	}
}
function rowResetScan(&$synch, $bResetFormat = false){
	$synch->setValue('rowParentName',	'');
	$synch->setValue('rowRootCatalog',	'');
	$synch->setValue('rowParentID', 	'');
	if ($bResetFormat) $synch->setValue('rowFormat', 		'');
}
function makeFloatPrice($price){
	$price= str_replace(',', '.', $price);
	return (float)preg_replace('#([^\d\.]+)#', '', $price);
}
function rowIsRootCatalog(&$synch, &$row)
{
	return;
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

	//	Если вдруг начнется импорт товаров, то импортировать в него
	$synch->setValue('rowRootCatalog',	$row[0]);

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
		'article'	=> $paernt?"$parent/$row[0]":$row[0],
		'parent'	=> $parent
	);
}
function rowIsFormat(&$synch, &$row)
{
	$names	= $synch->getValue('rowNameFormat');
	if (!$names) return;
	
	//	Если хоть одно значение в колонке содержит название, то это строка формата данных
	reset($row);
	$format	= array();
	foreach($row as $ix => &$val){
		if (!isset($names[$val])) continue;
		$format[$ix]	= $names[$val];
	}

	if ($format){
		$synch->setValue('rowFormat', $format);
	}
	return $format;
}
function rowIsProduct(&$synch, &$row)
{
	$format	= $synch->getValue('rowFormat');
	if (!$format || !count($format) || count($row) < count($format)) return;
	
	reset($row);
	$data	= array();
	foreach($row as $ix => &$val)
	{
		$name	= $format[$ix];
		$val	= trim($val);
		if (!$name || !$val) continue;
		
		$v	= &$data;
		$ex	= explode('.', $name);
		foreach($ex as $n){
			if ($n) $v = &$v[$n];
			else $v = &$v[];
		}
		$v	= $val;
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