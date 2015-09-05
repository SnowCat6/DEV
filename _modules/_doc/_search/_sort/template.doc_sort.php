<?
//	Прменяет значения сортировки и заполняет визуальные элементы
function doc_sort($db, $val, &$search)
{
	$pageSearch	= getValue('search');
	if (!is_array($pageSearch)) $pageSearch = array();

	$order	= getCacheValue('docOrderPages');
	list(,$orderDefault)	= each($order);

	$pages	= getCacheValue('docPages');
	list($pagesDefault,)	= each($pages);
	
	/***************************************/
	$docSort	= getCacheValue('docSort');
	$orderValue	= getValue('order');
	if (!isset($docSort[$orderValue])) $orderValue = $orderDefault;
	$search[':order']	= $orderValue;
	$thisOrder	= $orderDefault == $orderValue?'':$orderValue;
	/*******************************************/
	$pagesValue		= getValue('pages');
	if (!isset($pages[$pagesValue])) $pagesValue = $pagesDefault;
	$search[':max'] = $pages[$pagesValue];
	$thisPages		 = $pagesDefault == $pagesValue?'':$pagesValue;
?>

{push}
<?
$first = ' id="first"';
foreach($order as $name => $val)
{
	if (trim($orderValue, '-') == $val){
		$class = "$val current";

		if ($orderValue != $val) $class .= ' reverse';
		else $val = "-$val";

		$class = " class=\"$class\"";
	}else{
		$class = "class=\"$val\"";
	}

	$s				= array();
	$s['search']	= $pageSearch;
	$s['pages']		= $thisPages;
	if ($val != $orderDefault) $s['order'] = $val;

	removeEmpty($s);
	$s	= makeQueryString($s);
	$url= getURL('#', $s);
?>
<a href="{!$url}"{!$class}{!$first}><span>{$name}</span></a>
<? $first = ''; } ?>
{pop:sortNames}

{push}
<?
$first = ' id="first"';
foreach($pages as $name => $val)
{
	$class			= $pagesValue	== $name?' class="current"':'';
	
	$s				= array();
	$s['search']	= $pageSearch;
	$s['order']		= $thisOrder;
	if ($val != $pagesDefault) $s['pages'] = $name;
	
	removeEmpty($s);
	$s	= makeQueryString($s);
	$url= getURL('#', $s);
?>
<a href="{!$url}"{!$class}{!$first}><span>{$name}</span></a>
<? $first = ''; } ?>
{pop:sortPages}

<? return $search; } ?>