<?
//	В целом достаточно загрузить файл, определить функции и все
function import_xml1c($val, &$process)
{
	$tags	= array();
	$tags['category']	= 'importFn_category';
	$tags['offer']		= 'importFn_offer';

	$tags['name']		= 'tagProperty';
	$tags['description']= 'tagProperty';
	$tags['categoryId']	= 'tagProperty';
	$tags['price']		= 'tagProperty';
	$tags['pricePK100']	= 'tagProperty';
	$tags['ostatok']	= 'tagProperty';
	$tags['delivery']	= 'tagProperty';
	$tags['Id']			= 'tagProperty';
	$tags['Name']		= 'tagProperty';

	importAddTagFn($tags);
}?>
<?
//	Category tag
//	<category id="00000010413">Печать и копирование</category>
function importFn_category($process, $tag, $prop, $text, $bClose)
{
	if ($bClose){
		//	Закрывающий тег
		$prop	= $process['tagCategoryProp'];
		$process['tagCategoryProp'] = NULL;	//	Обнулить свойства
		$prop['name']	= $text;
//		Не импортировать каталоги для MK
//		importCatalog($process, $prop);
	}else{
		//	Запомнить свойства открывающего тега
		$process['tagCategoryProp']= $prop;
	}
}
//	<offer id="00000021956" available="true">
function importFn_offer($process, $tag, $prop, $text, $bClose)
{
	if ($bClose){
		$prop	= $process['tagOfferProp'];
		$process['tagOfferProp'] = NULL;
		
		@$article= $prop['proizvoditel']['Id'];
		if (!$article) return;
//		importProduct($process, $prop);
	}else{
		$process['tagOfferProp'] = $prop;
	}
}
function tagProperty($process, $tag, $prop, $text, $bClose)
{
	if (!$bClose) return;
	//	Получить родительский тег
	$tagStack	= &$process['tagStack'];
	$parentTag	= $tagStack[count($tagStack)-2];
	
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
		$process['tagOfferProp'][$parentTag][$tag] = $text;
		break;
	}
}
?>
