<? function import_YandexXML($val, &$data)
{
	$file	= localCacheFolder.'/siteFiles/yandex.xml';
	$f		= fopen($file, 'w');
	ob_start();
	/***********************************/
	echo '<?xml version="1.0" encoding="utf-8"?><!DOCTYPE yml_catalog SYSTEM "shops.dtd">';
	
	$date	= date('Y-m-d H:i');
	echo "<yml_catalog date=\"$date\">";
	yandexMakeShop();
	echo '</yml_catalog>';
	/************************************/
	
	fwrite($f, ob_get_clean());
	fclose($f);
	
	return true;
}
/******************************/
function yandexMakeShop()
{
	$url	= getURLEx();
	$ini	= getCacheValue('ini');
	$ya		= $ini[':yandex'];
	
	echo "<shop>
    <name>$ya[shopName]</name>
    <company>$ya[shopCompany]</company>
    <url>$url</url>
    <platform>DEV CMS</platform>
    <version>0.1</version>
    <agency>$ya[shopAgency]</agency>
    <email>$ya[shopMail]</email>";
	yandexCurrncy();
	$c = yandexCategoryes();
	yandexOffers($c);
	echo "</shop>";
}
function yandexCurrncy(){
	echo '<currencies><currency id="RUR" rate="1"/></currencies>';
}
function yandexCategoryes()
{
	$db	= module('doc');
	$db->clearCache();
	
	$s			= array();
	$s['type']	= 'catalog';
	$s['prop']['!place']	= 'mainCatalog';
	$tree = module('doc:childs:4', $s);
	
	$catalogs = array();
	echo '<categories>';
	yandexCategoryShow($db, 0, $tree, &$catalogs);
	echo '</categories>';

	$db->clearCache();

	return $catalogs;
}
function yandexCategoryShow($db, $parent, &$tree, &$catalogs)
{
	foreach($tree as $id => &$childs)
	{
		if (!is_int($id)) continue;
		if ($id){
			$data	= $db->openID($id);
			$name	= htmlspecialchars($data['title']);
			if ($parent){
				echo "<category id=\"$id\" parentId=\"$parent\">$name</category>";
			}else{
				echo "<category id=\"$id\">$name</category>";
			}
		}
		yandexCategoryShow($db, $id, $childs, $catalogs);
		if (!$childs) $catalogs[$id] = $id;
	}
}
function yandexOffers(&$c)
{
	$db	= module('doc');
	
	echo '<offers>';
	foreach($c as $id){
		$s			= array();
		$s['parent']= $id;
		$s['type']	= 'product';
		$db->open(doc2sql($s));
		while($data = $db->next())
		{
			$iid	= $db->id();
			$name	= htmlspecialchars($data['title']);
			$url	= getURLEx($db->id());
			$title	= docTitleImage($iid);
			if ($title){
				$title = str_replace(localHostPath.'/', '', $title);
				$title = getURLEx('').$title;
			}
			
			echo "<offer id=\"$iid\" type=\"vendor.model\" available=\"true\">";

			echo "<url>$url</url>";
			echo "<price>$data[price]</price>";
			echo "<currencyId>RUR</currencyId>";
			echo "<categoryId>$id</categoryId>";
			echo "<name>$name</name>";
			if ($title) echo "<picture>$title</picture>";

			echo '</offer>';
			$db->clearCache();
		}
	}
	echo '</offers>';
}

?>