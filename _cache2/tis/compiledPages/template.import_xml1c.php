<?
//	В целом достаточно загрузить файл, определить функции и все
function import_xml1c($val, &$synch)
{
	$tags2	= 'ЭтоГруппа|КодГруппы|Код|Цена|Метка|ТоварнаяГруппа|СопутствующиеТовары|ПохожиеТовары|НаименованиеПолное|НаименованиеКраткое|ДополнительноеОписание';
	//	Задать свои правила
	$tags['Данные Запись']			= 'inportFn_Record';
	$tags["Данные Запись ($tags2)"]	= 'inportFn_RecordProp';
	$tags['Данные Запись [^\s]+']	= 'inportFn_RecordProp2';

	//	Сохранить правила
	$synch->addRules($tags);
}

function inportFn_Record(&$synch, &$tagTree)
{
	$tag	= end($tagTree);
	$attrs	= &$tag['attrs'];
	
	if ($attrs['ЭтоГруппа']=='true')
	{
		$attrs['name']		= $attrs['НаименованиеКраткое'];
		$attrs['id']		= $attrs['Код'];
		$attrs['parentId']	= $attrs['КодГруппы'];
		//	Если нет родителя, это корневой каталог, поместим его на карту сайта
		if (!$attrs['parentId']) $attrs[':property']['!place'] = 'map';
		importCatalog($synch, $attrs);
	}else{
		$attrs['name']		= $attrs['НаименованиеКраткое'];
		$attrs['id']		= $attrs['Код'];
		$attrs['categoryId']= $attrs['КодГруппы'];
		$attrs['price']		= $attrs['Цена'];
		importProduct($synch, $attrs);
	}
}
//	Системные ствойства докусментов
function inportFn_RecordProp(&$synch, &$tagTree)
{
	//	Получить индекс родительского тега
	$tag	= end($tagTree);
	prev($tagTree);
	$ix		= key($tagTree);
	//	Добавить ссвойства родительского тега
	$tagTree[$ix]['attrs'][$tag['tagName']]	= $tag['contents'];
}
//	Свойства документов
function inportFn_RecordProp2(&$synch, &$tagTree)
{
	//	Получить индекс родительского тега
	$tag	= end($tagTree);
	prev($tagTree);
	$ix		= key($tagTree);
	//	Добавить ссвойства родительского тега
	$tagTree[$ix]['attrs'][':property'][$tag['tagName']]	= $tag['contents'];
}
?>