<?
//	В целом достаточно загрузить файл, определить функции и все
function import_xml1c($val, &$process)
{
	$tags	= array();
	$tags['Запись']		= 'inportFn_Record';
	$tags['ЭтоГруппа']	= 'inportFn_RecordProp';
	$tags['Код']		= 'inportFn_RecordProp';
	$tags['КодГруппы']	= 'inportFn_RecordProp';
	$tags['Вес']		= 'inportFn_RecordProp2';
	$tags['Объем']		= 'inportFn_RecordProp2';
	$tags['Цена']		= 'inportFn_RecordProp';
	$tags['Количество']	= 'inportFn_RecordProp2';
	$tags['Метка']		= 'inportFn_RecordProp';
	$tags['Размер']		= 'inportFn_RecordProp2';
	$tags['Производитель']		= 'inportFn_RecordProp2';
	$tags['Производительность']	= 'inportFn_RecordProp2';
	$tags['ТоварнаяГруппа']		= 'inportFn_RecordProp';
	$tags['СопутствующиеТовары']= 'inportFn_RecordProp';
	$tags['ПохожиеТовары']		= 'inportFn_RecordProp';
	$tags['НаименованиеПолное']	= 'inportFn_RecordProp';
	$tags['НаименованиеКраткое']= 'inportFn_RecordProp';
	$tags['ДополнительноеОписание']	= 'inportFn_RecordProp';
	
	$tags['Group']	= 'importFn_Group';
	$tags['Element']= 'importFn_Element';
	
	importAddTagFn($tags);
}?>
<?
function inportFn_Record($process, $tag, $prop, $text, $bClose)
{
	if (!$bClose)
		return $process['tagCategoryProp']= array();

	//	Закрывающий тег
	$prop			= $process['tagCategoryProp'];
	$process['tagCategoryProp'] = NULL;	//	Обнулить свойства
	
	@$bGroup = $prop['ЭтоГруппа']=='true';
	if ($bGroup){
		$prop['name']		= $prop['НаименованиеКраткое'];
		$prop['id']			= $prop['Код'];
		$prop['parentId']	= $prop['КодГруппы'];
		if (!$prop['parentId']) $prop[':property']['!place'] = 'map';
		importCatalog($process, $prop);
	}else{
		$prop['name']		= $prop['НаименованиеКраткое'];
		$prop['id']			= $prop['Код'];
		$prop['categoryId']	= $prop['КодГруппы'];
		$prop['price']		= $prop['Цена'];
		importProduct($process, $prop);
	}

	//	commit
}
//	Системные ствойства докусментов
function inportFn_RecordProp($process, $tag, $prop, $text, $bClose)
{
	if (!$bClose) return;

	//	Получить родительский тег
	$tagStack	= &$process['tagStack'];
	$parentTag	= $tagStack[count($tagStack)-2];
	
	switch($parentTag){
		default: 		return;
		case 'Запись':	break;
	}
	$process['tagCategoryProp'][$tag] = $text;
}
//	Свойства документов
function inportFn_RecordProp2($process, $tag, $prop, $text, $bClose)
{
	if (!$bClose || !$text) return;

	//	Получить родительский тег
	$tagStack	= &$process['tagStack'];
	$parentTag	= $tagStack[count($tagStack)-2];
	if ($parentTag != 'Запись') return;

	$process['tagCategoryProp'][':property'][$tag] = $text;
}
?>
<?
//	Category tag
//		<Group Model="СПЛИТ-СИСТЕМЫ" ElementCode="СПЛИТ-СИСТЕМЫ" ParentCode="Electrolux" Source="RVS"/>
function importFn_Group($process, $tag, $prop, $text, $bClose)
{
	//	Запомнить свойства открывающего тега
	if (!$bClose)
		return $process['tagCategoryProp']= $prop;

	//	Закрывающий тег
	$prop			= $process['tagCategoryProp'];
	$process['tagCategoryProp'] = NULL;	//	Обнулить свойства

	$prop['name']		= $prop['Model'];
	$prop['id']			= $prop['ElementCode'];
	$prop['parentId']	= $prop['ParentCode'];
	importCatalog($process, $prop);
}
?>
<?
//		<Element Model="Термостат  защиты от замерзания TF-60/HY  6м; -10...+12 ;" ElementCode="00000008725" ParentCode="Термостаты защиты от замерзания" Code="" Price="2 739,00" Trademark="Федеративная Республика Германия" Count="14,000" OrderedQuantity="0,000" Description="Короткое наименование: TF-60/HY; Размер: ; Производительность по воздуху: 6 м; Производитель: SHUFT" Fulldescription="" Source="RVS"/>

function importFn_Element($process, $tag, $prop, $text, $bClose)
{
	if (!$bClose)
		return $process['tagOfferProp'] = $prop;

	$prop			= $process['tagOfferProp'];
	$process['tagOfferProp'] = NULL;

	$prop['name']		= $prop['Model'];
	$prop['id']			= $prop['ElementCode'];
	$prop['categoryId']	= $prop['ParentCode'];
	$prop['price']		= $prop['Price'];
	//	Property
	@$p	= explode('; ', $prop['Description']);
	foreach($p as $val){
		list($name, $value) = explode(': ', $val);
		if ($name) $prop[':property'][$name] = $value;
	}
	
	importProduct($process, $prop);
}
?>
