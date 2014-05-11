<? function import_YandexXML($val, &$data)
{
	ob_start();
	/***********************************/
	echo '<?xml version="1.0" encoding="utf-8"?><!DOCTYPE yml_catalog SYSTEM "shops.dtd">';
	
	$date	= date('Y-m-d H:i');
	echo "<yml_catalog date=\"$date\">\r\n";
	yandexMakeShop();
	echo '</yml_catalog>';
	/************************************/
	$p	= ob_get_clean();
	
	file_put_contents(cacheRootPath.'/yandex.xml', $p);
	file_put_contents(localRootPath.'/yandex.xml', $p);
	if ($val=='direct'){
		header('Content-Type: text/xml; charset=utf-8');
		setTemplate('');
		echo $p;
	}
	if ($val=='page'){
		$url	= ('yandex.xml');
		echo "<a href=\"$url\">$url</a>";
	}
	
	return true;
}
/******************************/
function yandexMakeShop()
{
	$url	= getURLEx();
	$ini	= getCacheValue('ini');
	$ya		= $ini[':yandex'];
	
	if (!is_array($ya)) $ya = array();
	foreach($ya as &$val) $val = htmlspecialchars($val);
	
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
	$s['prop']['!place']	= 'map';
	$tree		= module('doc:childs:4', $s);
	
	$pass		= array();
	$catalogs	= array();
	echo '<categories>';
	yandexCategoryShow($db, 0, $tree, $catalogs, $tree[':data'], $pass);
	echo '</categories>';

	$db->clearCache();

	return $catalogs;
}
function yandexCategoryShow($db, $parent, &$tree, &$catalogs, &$cache, &$pass)
{
	foreach($tree as $id => &$childs)
	{
		if (!is_int($id)) continue;
		
		$data	= $cache[$id];
		if ($data){
			if ($pass[$id]) continue;
			$pass[$id]	= $id;
			
			if (!$childs) $catalogs[$id] = $id;
	
			$name	= htmlspecialchars($data['title']);
			if ($parent){
				echo "<category id=\"$id\" parentId=\"$parent\">$name</category>\r\n";
			}else{
				echo "<category id=\"$id\">$name</category>\r\n";
			}
		}

		yandexCategoryShow($db, $id, $childs, $catalogs, $cache);
	}
}
function yandexOffers(&$c)
{
	$pass	= array();
	$db		= module('doc');
	
	echo '<offers>';
	foreach($c as $id){
		$s			= array();
		$s['parent']= $id;
		$s['type']	= 'product';
		$s['price']	= '1-';
		$db->open(doc2sql($s));
		while($data = $db->next())
		{
			$iid	= $db->id();
			if ($pass[$iid]) continue;
			$pass[$iid]	= $iid;
			
			if (docPriceDelivery($data)) continue;
			
			$name	= htmlspecialchars($data['title']);
			$url	= htmlspecialchars(getURLEx($db->url()));
			$title	= docTitleImage($iid);
			$price	= docPrice($data);
			
			echo "<offer id=\"$iid\" available=\"true\">\r\n";

			echo "<url>$url</url>\r\n";
			echo "<price>$price</price>\r\n";
			echo "<currencyId>RUR</currencyId>\r\n";
			echo "<categoryId>$id</categoryId>\r\n";
			echo "<name>$name</name>\r\n";
			if ($title){
				ob_start();
				$img	= trim(displayThumbImage($title, array(100, 100)), '/');
				ob_end_clean();
//				$title = str_replace(localRootPath.'/', '', $title);
				$img	= htmlspecialchars(getURLEx('').$img);
				echo "<picture>$img</picture>\r\n";
			}

			echo "</offer>\r\n";
			$db->clearCache();
		}
	}
	echo '</offers>';
}

?>