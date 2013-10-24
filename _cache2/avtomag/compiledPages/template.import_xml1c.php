<?
//	В целом достаточно загрузить файл, определить функции и все
function import_xml1c($val, &$synch)
{
	$tags2	= 'Ид|Наименование';
	$tags3	= 'Ид|Штрихкод|Наименование';
	$tags4	= 'Ид|Штрихкод|Наименование|Количество';

	$tags					= array();
	$tags["Группы Группа"]			= 'importFn_Group';
	$tags["Группы Группа ($tags2)"]	= 'importFn_Group_prop';
	
	$tags["Товары Товар"]			= 'importFn_Offer';
	$tags["Товары Товар Группы Ид"]	= 'importFn_Offer_parent';
	$tags["Товары Товар ($tags3)"]	= 'importFn_Offer_prop';
	$tags["Товары Товар .* ЗначениеРеквизита"]		= 'importFn_Offer_property';
	$tags["Товары Товар .* ЗначениеРеквизита [^\s]+"]= 'importFn_Offer_property_values';

	$tags["Предложения Предложение"]			= 'importFn_Offer2';
	$tags["Предложения Предложение ($tags4)"]	= 'importFn_Offer_prop';
	$tags["Предложения Предложение Цены Цена"]	= 'importFn_Offer2_price';
	$tags["Предложения Предложение Цены Цена [^\s]+"]	= 'importFn_Offer_prop';
	
	$synch->addRules($tags);
}
function importFn_Group(&$synch, &$tagTree)
{
	$parentId	= '';
	foreach($tagTree as &$tag)
	{
		if ($tag['tagName'] != 'Группа') continue;
		if ($tag[':imported']) continue;
		$tag[':imported'] = true;
		$attrs			= &$tag['attrs'];
		
		$p				= array();		
		$p['id']		= $attrs['Ид'];
		$p['name']		= $attrs['Наименование'];
		$p['parentId']	= $parentId;
		
		importCatalog($synch, $p);
		
		$parentId		= $p['id'];
	}
}
function importFn_Group_prop(&$synch, &$tagTree)
{
	//	Получить индекс родительского тега
	$tag	= end($tagTree);
	prev($tagTree);
	$ix		= key($tagTree);
	//	Добавить ссвойства родительского тега
	$tagTree[$ix]['attrs'][$tag['tagName']]	= $tag['contents'];
}
function importFn_Offer(&$synch, &$tagTree)
{
	$tag			= end($tagTree);
	$attrs			= &$tag['attrs'];
	
	$p				= array();
	$p['id']		= $attrs['Ид'];
	$p['name']		= $attrs['Наименование'];
	$p['categoryId']= $attrs['categoryId'];
	$p[':property']	= $attrs[':property'];
	importProduct($synch, $p);
}
function importFn_Offer_prop(&$synch, &$tagTree)
{
	//	Получить индекс родительского тега
	$tag	= end($tagTree);
	prev($tagTree);
	$ix		= key($tagTree);
	//	Добавить ссвойства родительского тега
	$tagTree[$ix]['attrs'][$tag['tagName']]	= $tag['contents'];
}
function importFn_Offer_parent(&$synch, &$tagTree)
{
	//	Получить индекс родительского тега
	$tag	= end($tagTree);
	$text	= $tag['contents'];
	if (!$text) return;

	prev($tagTree);
	prev($tagTree);
	$ix	= key($tagTree);
	$tagTree[$ix]['attrs']['categoryId']	= $text;
}
function importFn_Offer_property(&$synch, &$tagTree)
{
	$p	= $synch->getValue('offerProperty');
	$synch->setValue('offerProperty', array());
	if (!$p) return;

	end($tagTree);
	prev($tagTree);
	prev($tagTree);
	$ix	= key($tagTree);
	
	$n	= $p['Наименование'];
	$v	= $p['Значение'];
	if ($n) $tagTree[$ix]['attrs'][':property'][$n]	= $v;
}
function importFn_Offer_property_values(&$synch, &$tagTree)
{
	//	Получить индекс родительского тега
	$tag	= end($tagTree);
	$text	= $tag['contents'];
	if (!$text) return;

	$p		= $synch->getValue('offerProperty');
	$p[$tag['tagName']]= $text;
	$synch->setValue('offerProperty', $p);
}
function importFn_Offer2_price(&$synch, &$tagTree)
{
	$tag	= end($tagTree);
	if ($tag['attrs']['Валюта'] != 'RUB') return;
	
	prev($tagTree);
	prev($tagTree);
	$ix	= key($tagTree);
	$tagTree[$ix]['attrs']['Цена']	= $tag['attrs']['ЦенаЗаЕдиницу'];
}
function importFn_Offer2(&$synch, &$tagTree)
{
	$tag			= end($tagTree);
	$attrs			= &$tag['attrs'];
	
	$p				= array();
	$p['id']		= $attrs['Ид'];
	$p['name']		= $attrs['Наименование'];
	$p['price']		= $attrs['Цена'];
	importProduct($synch, $p);
}
?>
