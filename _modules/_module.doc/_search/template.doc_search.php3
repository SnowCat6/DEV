<?
function doc_search($db, $val, $search)
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

//	$s['price']		= '1-';
	
//	$s[':order']	= $search['prop'][':order'];
//	$s['prop'][':order'] = '';
//	unset($s['prop'][':order']);

//	$s[':pages']	= $search['prop'][':pages'];
//	$s['prop'][':pages'] = '';
//	unset($s['prop'][':pages']);
	
	doc_sql($sql, $s);

	//	Вычислим хеш значение, посмотрим кеш, если есть совпаления, то выведем результат и выйдем
	if (!beginCompile($data, $searchHash = "search_".hashData($sql)))
		return $s;

	//	Получить свойства и кол-во товаров со свойствами
	$n		= $data['fields']['any']['searchProps'];
	if ($n && is_array($n)){
		$n		= implode(',' , $n);
		$prop	= module("prop:count:!$n", $s);
	}else{
		$props	= module("prop:name:productSearch");
		$n		= implode(',', array_keys($props));
		$prop	= $n?module("prop:count:$n", $s):array();
	}
	//////////////////
	//	Созание поиска
	if (!$prop){
		endCompile($data, $searchHash);
		return $s;
	}
	///////////////////
	//	Табличка поиска
?>
<table width="100%" cellpadding="0" cellspacing="0" class="search property">
<tr><td colspan="2" class="title">
<big>Ваш выбор:</big>
<?
//	Выведем уже имеющиеся в поиске варианты
$s1		= NULL;
$sProp	= $search['prop'];
if (!is_array($sProp)) $sProp= array();
foreach($sProp as $name => $val){
	//	Если в свойствах базы данных нет имени свойства,пропускаем
	if (!isset($prop[$name])) continue;
	
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
?><span><a href="{!$url}">{!$val}</a></span> <? } ?>
<? if ($s1){ ?><a href="{{getURL:page$id}}" class="clear">очистить</a><? } ?>
</td></tr>
<?
//	Выведем основные характеристики
foreach($prop as $name => &$property)
{
	@$thisVal = $search['prop'][$name];
	if ($thisVal) continue;
	$note	= $props[$name]['note'];
?>
<tr>
    <th title="{$note}">{$name}:</th>
    <td width="100%">
<?
foreach($property as $pName => $count)
{
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
	</td>
</tr>
<? }// each prop ?>
</table>
<?
	endCompile($data, $searchHash);
	return $s;
} ?>

