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
		$synch->setValue('comment', "<a href=\"$url\" id=\"ajax\">Настройки</a>");

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
	$db		= new importBulk();
	$f		= fopen($synch->getValue('source'), 'r');
	while(true)
	{
		if (feof($f)){
			$synch->setValue('status', 'complete');
			break;
		}
		$row	= fgets($f);
		$row	= rowParse($synch, $row);
		
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
		//	Если вдруг начнется импорт товаров, то импортировать в него
		$synch->setValue('rowRootCatalog',	$r['article']);
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
		//	Родительский элемент
		$parent	= array();
		
		if ($p = $r['parent1']) $parent[]	=	 $p;
		$article= importArticle(implode('/', $parent));
		rowAssignParent($synch, $db, $r, $p, $article, '');

		if ($p = $r['parent2'])$parent[]	=	 $p;
		$p2		= $article;
		$article= importArticle(implode('/', $parent));
		rowAssignParent($synch, $db, $r, $p, implode('/', $parent), $p2);

		if ($p = $r['parent3'])$parent[]	=	 $p;
		$p2		= $article;
		$article= importArticle(implode('/', $parent));
		rowAssignParent($synch, $db, $r, $p, implode('/', $parent), $p2);
		//	Получить артикул текущего каталога
		if (!$r['parent']){
			$p	= $synch->getValue('rowParentID');
			rowAssignParent($synch, $db, $r, $p, $p, '');
		}
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

	$article	= importArticle($row[0]);

	return array(
		'name'		=> $row[0],
		'article'	=> $article
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
	
	$article	= importArticle($paernt?"$parent/$row[0]":$row[0]);

	return array(
		'name'		=> $row[0],
		'article'	=> $article,
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
function rowParse(&$synch, $row)
{
	if ($synch->getValue('rowEncode') != 'utf-8')
		$row	= iconv($encode, 'utf-8', $row);
		
	$type	= $synch->getValue('rowType');
	if (!$type){
		$type	= explode('.', basename($synch->getValue("source")));
		$type	= strtolower(end($type));
		$synch->setValue('rowType', $type);
	}
	switch($type){
	case 'txt':
		$row	= explode("\t", $row);
		break;
	case 'csv':
		$row	= rowParseCSV($row);
		break;
	}
	

	foreach($row as &$val){
		$val	= str_replace('&nbsp;', ' ', $val);
		$val	= preg_replace('#\s+#', ' ', $val);
		$val	= trim($val);
	}
	return $row;
}
function rowParseCSV($line)
{
	$delimiter	= ';';
	$enclosure	= '"';
	$escape		= '\\';
	
	$output		= array(); 
	if (preg_match("/$escape.$enclosure/", $line))
	{ 
		while ($strlen = strlen($line))
		{ 
			$pos_delimiter       = strpos($line, $delimiter); 
			$pos_enclosure_start = strpos($line, $enclosure); 
			if (is_int($pos_delimiter) && is_int($pos_enclosure_start) 
				&& ($pos_enclosure_start < $pos_delimiter))
				{ 
				$enclosed_str = substr($line,1); 
				$pos_enclosure_end = strpos($enclosed_str,$enclosure); 
				$enclosed_str = substr($enclosed_str,0,$pos_enclosure_end); 
				$output[] = $enclosed_str; 
				$offset = $pos_enclosure_end+3; 
			} else { 
				if (empty($pos_delimiter) && empty($pos_enclosure_start)) { 
					$output[] = substr($line,0); 
					$offset = strlen($line); 
				} else { 
					$output[] = substr($line,0,$pos_delimiter); 
					$offset = ( !empty($pos_enclosure_start) 
								&& ($pos_enclosure_start < $pos_delimiter) 
								) 
								?$pos_enclosure_start 
								:$pos_delimiter+1; 
				} 
			} 
			$line = substr($line,$offset); 
		} 
	} else { 
		$line = preg_split("/$delimiter/", $line);
		/* 
		 * Validating against pesky extra line breaks creating false rows. 
		 */ 
		if (is_array($line) && !empty($line[0])) { 
			$output = $line; 
		}  
	} 
	return $output; 
}
function rowAssignParent(&$synch, &$db, &$data, $name, $article, $parentArticle)
{
	$article= importArticle($article);
	if (!$article || !$name) return;
	
	$fields	= array();
	if ($parentArticle && $article != $parentArticle) $fields['parent']	= $parentArticle;
	$iid	= $db->addItem($synch, 'catalog', $article, $name, $fields);
	
	$data['parent']	= $article;
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