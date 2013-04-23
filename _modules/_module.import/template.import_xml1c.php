<?
//	В целом достаточно загрузить файл, определить функции и все
function import_xml1c($val, &$process){
}?>
<?
//	Category tag
//	<category id="00000010413">Печать и копирование</category>
function importFn_category(&$process, &$tag, &$prop, &$text)
{
	//	Запомнить свойства открывающего тега
	$process['tagCategoryProp']= $prop;
}
function importFn_category_close(&$process, &$tag, &$prop, &$text)
{
	//	Закрывающий тег
	$prop			= $process['tagCategoryProp'];
	$process['tagCategoryProp'] = NULL;	//	Обнулить свойства
	$prop['name']	= $text;
	importCatalog($process, $prop);
}
?>
<?
//	<offer id="00000021956" available="true">
function importFn_offer(&$process, &$tag, &$prop, &$text){
	$process['tagOfferProp'] = $prop;
}
function importFn_offer_close(&$process, &$tag, &$prop, &$text)
{
	$prop			= $process['tagOfferProp'];
	$process['tagOfferProp'] = NULL;
	importProduct($process, $prop);
}
function tagProperty(&$process, &$tag, &$prop, &$text)
{
	//	Переметится на конец архива
	end($process['tagStack']);
	//	Получить родительский тег
	$parentTag	= prev($process['tagStack']);
	
	switch($parentTag){
	case 'offer':
	//	Добавть в ствойства родителя значение текущего тега
		$process['tagOfferProp'][$tag] = $text;
		break;
//	Производитель
//	<proizvoditel>
//		<Id/>
//		<Name>SAMSUNG</Name>
//	</proizvoditel>
	case 'proizvoditel':
		if ($tag != 'Name') break;
		$process['tagOfferProp'][':property']['Производитель'] = $text;
		break;
	}
}
function importFn_name_close(&$process, &$tag, &$prop, &$text)
{
	tagProperty(&$process, &$tag, &$prop, &$text);
}

function importFn_description_close(&$process, &$tag, &$prop, &$text)
{
	tagProperty(&$process, &$tag, &$prop, &$text);
}
function importFn_categoryId_close(&$process, &$tag, &$prop, &$text)
{
	tagProperty(&$process, &$tag, &$prop, &$text);
}
function importFn_price_close(&$process,&$tag, &$prop, &$text)
{
	tagProperty(&$process, &$tag, &$prop, &$text);
}
function importFn_ostatok_close(&$process, &$tag, &$prop, &$text)
{
	tagProperty(&$process, &$tag, &$prop, &$text);
}
?>
