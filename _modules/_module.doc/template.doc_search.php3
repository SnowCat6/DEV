<?
function doc_search($db, $val, $search)
{
	@list($id, $group) = explode(':', $val);
	
	//	Откроем документ
	$data	= $db->openID($id);
	if (!$data) return;
	
	//	Проверим параметры поиска
	if (!is_array($search)) $search = array();
	$search = array('prop' => $search);
	
	if (!$group)
		$group = 'productSearch';

	$sql= array();
	//	Подготовим базовый SQL запрос
	$s	= array();
	$s['parent*'] 	= "$id:catalog";
	$s['type']		= 'product';
	dataMerge($s, $search);
	doc_sql(&$sql, $s);

	//	Вычислим хеш значение, посмотрим кеш, если есть совпаления, то выведем результат и выйдем
	if (!beginCompile($data, $searchHash = "search_".hashData($sql)))
		return $search;

	//	Получить свойства и кол-во товаров со свойствами
	$props	= module("prop:name:productSearch");
	$n		= implode(',', array_keys($props));
	$prop	= $n?module("prop:count:$n", $s):array();
	//////////////////
	//	Созание поиска
	if (!$prop){
		endCompile($data, $searchHash);
		return $search;
	}
	
	///////////////////
	//	Табличка поиска
?>
<table width="100%" cellpadding="0" cellspacing="0" class="search property">
<tr><td colspan="2" class="title">
<big>Ваш выбор:</big>
<?
//	Выведем уже имеющиеся в поиске варианты
$s = NULL;
foreach($search['prop'] as $name => $val){
	//	Если в свойствах базы данных нет имени свойства,пропускаем
	if (!isset($prop[$name])) continue;
	
	//	Сделаем ссылку поиска но без текущего элемента
	$s		= $search;
	unset($s['prop'][$name]);
	$url	= getURL("page$id", makeQueryString($s['prop'], 'search'));
	$val	= propFormat($val, $prop[$name]);
	//	Покажем значение
?><span><a href="{!$url}">{!$val}</a></span> <? } ?>
<? if ($s){ ?><a href="{{getURL:page$id}}" class="clear">очистить</a><? } ?>
</td></tr>
<?
//	Выведем основные характеристики
foreach($prop as $name => &$property)
{
	@$thisVal = $search['prop'][$name];
	if ($thisVal) continue;
?>
<tr>
    <th title="{$val[note]}">{$name}:</th>
    <td width="100%">
<?
foreach($property as $pName => $count)
{
	$s					= $search;
	$s['prop'][$name]	= $pName;

	$nameFormat	= propFormat($pName, $props[$name]);
	$url		= getURL("page$id", makeQueryString($s['prop'], 'search'));
?>
<span><a href="{!$url}">{!$nameFormat}</a> ({$count})</span>
<? }//	each prperty ?>
	</td>
</tr>
<? }// each prop ?>
</table>
<?
	endCompile($data, $searchHash);
	
	$sql	= array();
	doc_sql($sql, $search);
	return $sql?$search:array();
} ?>

