<?
function doc_search($db, $val, $search)
{
	@list($id, $group) = explode(':', $val);
	
	//	Откроем документ
	$data	= $db->openID($id);
	if (!$data) return;
	
	//	Проверим параметры поиска
	if (!is_array($search)) $search = array();
	if ($search) $search = array('prop' => $search);
	
	if (!$group)
		$group = 'productSearch';

	$sql= array();
	//	Подготовим базовый SQL запрос
	$s	= array();
	$s['parent'] 	= $id;
	$s['type']		= 'product';
	dataMerge($s, $search);
	doc_sql(&$sql, $s);

	//	Вычислим хеш значение, посмотрим кеш, если есть совпаления, то выведем результат и выйдем
	if (!beginCompile($data, $searchHash = "search_".hashData($sql)))
		return $search;

	//////////////////
	//	Созание поиска
	$ids	= $db->selectKeys($db->key(), $sql);
	$prop	= $ids?module("prop:get:$ids:$group"):NULL;
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
$bHasProp	= false;
@$s1		= $search['prop'];
while(@list($name, $val) = each($s1)){
	//	Если в свойствах базы данных нет имени свойства,пропускаем
	if (!isset($prop[$name])) continue;
	$bHasProp = true;
	
	//	Сделаем ссылку поиска но без текущего элемента
	$s		= $search;
	unset($s['prop'][$name]);
	$url	= getURL("page$id", makeQueryString($s['prop'], 'search'));
	$val	= propFormat($val, $prop[$name]);
	//	Покажем значение
?><span><a href="{!$url}">{!$val}</a></span> <? } ?>
<? if ($bHasProp){ ?>
<a href="{{getURL:page$id}}" class="clear">очистить</a>
<? } ?>
</td></tr>
<?
//	Выведем основные характеристики
foreach($prop as $name => $val)
{
	if ($name[0] == ':') continue;
	if (isset($search['prop'][$name])) continue;
	
	$property = $val['property'];
	if (!$property) continue;
	
	$property = explode(', ', $property);
	@$thisVal = $search['prop'][$name];
?>
<tr>
    <th title="{$val[note]}">{$name}:</th>
    <td width="100%">
<?
foreach($property as $p)
{
	$class	= $thisVal == $p?' selected="selected"':'';

	$s					= $search;
	$s['parent']		= $id;
	$s['type']			= 'product';
	$s['prop'][$name]	= $p;

	$sql= array();
	doc_sql($sql, $s);
	$db->fields = 'count(*) as cnt';
	$db->open($sql);
	$d		= $db->next();
	@$count	= $d['cnt'];

	$nameFormat	= propFormat($p, $val);
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
	return $search;
} ?>

