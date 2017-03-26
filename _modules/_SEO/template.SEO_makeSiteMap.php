<?
//	+function SEO_makeSIteMap
function SEO_makeSIteMap($val, $data)
{
	$autoMake	= getIniValue(':');
	$autoMake	= $autoMake['sitemapAutoMake']=='yes';
	if (!$autoMake) return;
	
	$s	= array();
	$s['prop']['!place']= 'map';

	$tree	= module('doc:childs:5', $s);
	$d		= &$tree[':data'];
	
	ob_start();
	makeSiteMapTree($d, $tree, 0);
	$xml	= ob_get_clean();
	$xml	= '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . $xml . '</urlset>';
	
	$ex = getSiteFile('sitemap.xml');
	if (file_get_contents($ex) != $xml){
		writeSiteFile('sitemap.xml', $xml);
	}
}
function makeSiteMapTree($d, $childs, $deep)
{
	if (!$childs) return;

	foreach($childs as $id => $childs)
	{
		if (!is_int($id)) continue;
		$data	= $d[$id];
		if ($data)
		{
			$db	= module("doc", $data);
			$url= getURLEx($db->url());
			$absDeep	= round(1/$deep,1);
			//	<lastmod>2014-04-15T14:25:02+01:00</lastmod>
		}else{
			$url	= getURLEx('');
			$absDeep= 1;
		}
		echo "
<url>
	<loc>$url</loc>
	<priority>$absDeep</priority>
</url>
";
		makeSiteMapTree($d, $childs, $deep+1);
	}
}
?>