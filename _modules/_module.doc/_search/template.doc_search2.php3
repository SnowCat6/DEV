<?
function doc_search2($db, $val, $search)
{
	@list($id, $group) = explode(':', $val);
	
	//	Откроем документ
	$data	= $db->openID($id);
	if (!$data) return;
	
	//	Проверим параметры поиска
	if (!is_array($search)) $search = array();
	
	if (!$group) $group = 'productSearch';

	//	
	$thisOrder	= getValue('order');
	$thisPages	= getValue('pages');

	$sql= array();
	//	Подготовим базовый SQL запрос
	$s	= $search;
	$s['parent*'] 	= "$id:catalog";
	$s['type']		= 'product';
	$s['url'] 		= array(
		'search'=> $search,
		'pages'	=> $thisPages,
		'order'	=> $thisOrder);
	removeEmpty($s);


	//	Вычислим хеш значение, посмотрим кеш, если есть совпаления, то выведем результат и выйдем
	if (!beginCompile($data, "search2_".hashData($s)))
		return $s;

	doc_sql($sql, $s);
	//	Получить свойства и кол-во товаров со свойствами
	
	$n		= $data['fields']['any']['searchProps'];
	if ($n && is_array($n)) $n = implode(',' , $n);
	else{
		$props	= module("prop:name:productSearch");
		$n		= implode(',', array_keys($props));
	}
	$prop	= $n?module("prop:count:$n", $s):array();
	//////////////////
	//	Созание поиска
	if (!$prop){
		endCompile($data);
		return $s;
	}
	
	///////////////////
	//	Табличка поиска
?>
<div class="search search2 property">
<div class="title">
<big>Ваш выбор:</big>
<?
//	Выведем уже имеющиеся в поиске варианты
$s1		= NULL;
$sProp	= $search['prop'];
if (!is_array($sProp)) $sProp= array();
foreach($sProp as $name => $val){
	//	Если в свойствах базы данных нет имени свойства,пропускаем
	if (!isset($prop[$name])) unset($sProp[$name]);
}
if ($sProp){ ?><a href="{{getURL:page$id}}" class="clear">очистить</a><? }

foreach($sProp as $name => $val){
	//	Сделаем ссылку поиска но без текущего элемента
	//	Сделаем ссылку поиска но без текущего элемента
	$s1		= array();
	$s1['search']				= $search;
	$s1['search']['prop'][$name]= '';
	$s1['pages']				= $thisPages;
	$s1['order']				= $thisOrder;
	removeEmpty($s1);
	$url	= getURL("page$id", makeQueryString($s1));

//	$s1		= $search;
//	unset($s1['prop'][$name]);
//	$url	= getURL("page$id", makeQueryString($s1['prop'], 'search'));
	$val	= propFormat($val, $props[$name]);
	//	Покажем значение
?><div><a href="{!$url}">{!$val}</a></div> <? } ?>
</div>
<?
//	Выведем основные характеристики
$totalCount = 0;
foreach($prop as $name => &$property){
	$totalCount += count($property) + 2;
}

foreach($prop as $name => &$property)
{
	@$thisVal = $search['prop'][$name];
	if ($thisVal) continue;
	$note	= $props[$name]['note'];
?>
<div class="panel">
<h3 title="{$note}">{$name}:</h3>
<div>
<?
$chars	= 0;
foreach($property as $pName => $count){
	$chars	+= strlen($pName) + 5;
}
$nColumns	= floor($chars?$chars/30:1);
$nColumns	= max(1, $nColumns);
$rowLimit	= 0;
$rowLimit	= 20;

$ix			= 1;
$close		= '';
foreach($property as $pName => $count)
{
	if ($ix++ == $rowLimit && count($property) - $ix > 5){
		echo '<div class="expand"><div class="expandContent">';
		$close	= '</div></div>';
	}

	$s1		= array();
	$s1['search']				= $search;
	$s1['search']['prop'][$name]= $pName;
	$s1['pages']				= $thisPages;
	$s1['order']				= $thisOrder;
	removeEmpty($s1);
	$url	= getURL("page$id", makeQueryString($s1));
	
//	$s1					= $search;
//	$s1['prop'][$name]	= $pName;
//	$url		= getURL("page$id", makeQueryString($s1['prop'], 'search'));

	$nameFormat	= propFormat($pName, $props[$name]);
?>
<span><a href="{!$url}">{!$nameFormat}</a><sup>{$count}</sup></span>
<? }//	each prperty ?>
{!$close}
</div>
</div>
<? }// each prop ?>
</div>
<?
	endCompile($data);
	return $s;
} ?>

