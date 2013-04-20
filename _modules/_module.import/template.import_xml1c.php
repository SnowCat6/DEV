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
	$prop		= $process['tagCategoryProp'];
	@$article	= ":$prop[id]";			//	Артикул
	@$parent	= ":$prop[parentId]";	//	Родительский объект
	@$name		= $text;				//	Межтеговый текст как имя категории
	$process['tagCategoryProp'] = NULL;	//	Обнулить свойства
	
	$cache		= &$process['cacheGroup'];
	@$id		= $cache[$article];
	@$parentId	= $cache[$parent];
	@$cacheProp	= &$process['cacheProperty'][$id];
	$statistic	= &$process['statistic']['category'];

	$d	= array();
	if ($id){
		if ($parentId){
			@$bHasParent = $process['cacheParents'][$id];
			@$bHasParent = $bHasParent[$parentId];
			if (!$bHasParent){
				$d[':property'][':importParent'] = $parentId;
				$process['cacheParents'][$id][$parentId] = true;
			}
		}
		if ($name != $cacheProp[':title']){
			$d['title'] = $name;
			$cacheProp[':title'] = $name;
		}
		if ($d){
			@$statistic['update'] += 1;
			$id = module("doc:update:$id:edit", $d);
		}else{
			@$statistic['pass'] += 1;
		}
	}else{
		$d['title']		= $name;
		$d[':property'][':import']			= 'price';
		$d[':property'][':importArticle']	= $article;
		$d[':property'][':importParent']	= $parentId;
		$id = module("doc:update:$parentId:add:catalog", $d);
		if ($id){
			@$statistic['add'] += 1;
			$cache[$article] = $id;
			$process['cacheParents'][$id][$parentId] = true;
		}else{
			@$statistic['error'] += 1;
		}
	}
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
	if (!$prop['id'])	return;
	if (!$prop['name'])	return;
	
	$article		= ":$prop[id]";
	$parentArticle	= ":$prop[categoryId]";
	@$name			= $prop['name'];
	$process['tagOfferProp'] = NULL;
	
	$cacheParent= &$process['cacheGroup'];
	$cache		= &$process['cacheProduct'];
	$statistic	= &$process['statistic']['product'];

	@$id		= $cache[$article];
	@$parentId	= $cacheParent[$parentArticle];

	$d			= array();
	@$price		= parseInt($prop['price']);
	@$thisProp	= $prop[':property'];
	if (!is_array($thisProp)) $thisProp = array();
	
	if ($id){
		@$cacheProp	= &$process['cacheProperty'][$id];
		if ($parentId){
			@$bHasParent = $process['cacheParents'][$id];
			@$bHasParent = $bHasParent[$parentId];
			if (!$bHasParent){
//				print_r($process['cacheParents'][$id]);
//				echo $id, ' ', $parentArticle; die;
				$d[':property'][':importParent'] = $parentId;
				$process['cacheParents'][$id][$parentId] = true;
			}
		}
		if ($name != $cacheProp[':title']){
			$d['title'] = $name;
			$cacheProp[':title'] = $name;
		}
		if ((float)$price != (float)$cacheProp[':price']){
			echo (float)$price, ' ', (float)$cacheProp[':price'];
			$d['price'] = $price;
		}

		foreach($thisProp as $name => &$property){
			$diff = array_diff(explode(', ', $property), explode(', ', $cacheProp[$name]));
			if (!$diff) continue;
			$d[':property'][$name] = $property;
		}

		if ($d){
			print_r($d);
			$iid = module("doc:update:$id:edit", $d);
			if ($iid){
				@$statistic['update'] += 1;
			}else{
				@$statistic['error'] += 1;
				logData("import: Error update product $id", 'import');
			}
		}else{
			@$statistic['pass'] += 1;
		}
		$process['imported'][] = $id;
	}else{
		$d['title']		= $name;
		$d['price'] 	= $price;
		$d[':property']	= $thisProp;
		$d[':property'][':import']			= 'price';
		$d[':property'][':importArticle']	= $article;
		$d[':property'][':importParent']	= $parentId;
		$id = module("doc:update:$parentId:add:product", $d);
		if ($id){
			$process['imported'][] = $id;
			@$statistic['add'] += 1;
			$cache[$article] = $id;
			$process['cacheParents'][$id][$parentId] = true;
		}else{
			@$statistic['error'] += 1;
			logData("import: Error add product", 'import');
		}
	}
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
