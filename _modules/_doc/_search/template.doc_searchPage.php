<?
function doc_searchPage($db, $val, $data)
{
	//	Попробуем взять параетры из строки
	@list($type, $template) = explode(':', $val);
	//	Если типа документа нет, пробуем взять из данных
	if (!$type) $type	= $data[1];
	if (!$type) $type	= 'product';

	//	
	$thisOrder	= getValue('order');
	$thisPages	= getValue('pages');

	//	Проверить на наличие такого типа данных
	$docTypes	= getCacheValue('docTypes');
	if (!isset($docTypes["$type:"])) $type = '';

	//	Пробуем получить шаблон из данных
	if (!$template) $template	= $data[2];

	//	Сделаем ссылку
	if ($type){
		$searchURL	= "search_$type";
		if ($template) $searchURL .= "$template";
	}else{
		$searchURL	= "search";
	}
	if (!$template){
		switch($type){
		case 'product':	$template = 'catalog'; break;
		case 'article':	$template = 'news'; break;
		default: 		$template = 'catalog'; break;
		}
	}

	//	Получить данные для поиска
	$search = getValue('search');
	removeEmpty($search);
	//	Сохранить поиск по имени
	$name	= $search['name'];
	//	Если был поиск по имени, восстановить
	if ($name) $search['name'] = $name;
	//	Кешировать поиск без данных
	if (!$search && !beginCache($cache = "pageSearchCache")) return;
	
	$s			= $search;
	$s['type']	= $type;

	$ddb	= module('prop');
	$names	= array();
	//	В зависимости от поиска, исать все параметры или только часть
	$groups	= $search?"globalSearch,globalSearch2":"globalSearch";
	//	Получить свойства и кол-во товаров со свойствами
	$props	= module("prop:name:$groups");
	$n		= implode(',', array_keys($props));
	$prop	= $n?module("prop:count:$n", $s):array();

	//	Заполнить выбранные свойства
	$selected	= array();
	$sProp		= $search['prop'];
	if (!is_array($sProp)) $sProp = array();

	foreach($sProp as $name => $val)
	{
		if (!isset($prop[$name])) continue;
		$selected[$name]	= $val;
	}
	//	Заполнить свойства для выбора
	$select = array();
	foreach($prop as $name => &$property){
		if (isset($search['prop'][$name])) continue;
		$select[$name] = $property;
	}

	m('page:title', 'Поиск по сайту');
?>
<form action="{{getURL:$searchURL}}" method="post" class="form searchForm">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="100%"><input name="search[name]" type="text" class="input w100" value="{$search[name]}" /></td>
    <th><input type="submit" name="button" class="button" value="Искать" /></th>
</tr>
</table>
<? if ($selected || $select){ ?>
<table class="search property" width="100%" cellpadding="0" cellspacing="0">
<tr>
    <td colspan="2" class="title">
<big>Ваш выбор: </big>
<? foreach($selected as $name => $val){
	$s1	= array();
	$s1['search']	= $search;
	$s1['search']['prop'][$name]	= '';
	$s1['pages']	= $thisPages;
	$s1['order']	= $thisOrder;
	removeEmpty($s1);
	$url	= getURL($searchURL, makeQueryString($s1));
?>
<span><a href="{!$url}">{$val}</a></span>
<? } ?>
<? if ($selected){ ?><a href="{{getURL:$searchURL}}" class="clear">очистить</a><? } ?>
    </td>
</tr>
<? foreach($select as $name => &$property){
	$note = $props[$name]['note'];
?>
<tr>
	<th title="{$note}">{$name}</th>
    <td width="100%">
<? 
$ix = 0;
foreach($property as $pName => $count)
{
	$s1	= array();
	$s1['search']	= $search;
	$s1['search']['prop'][$name]	= $pName;
	$s1['pages']	= $thisPages;
	$s1['order']	= $thisOrder;
	removeEmpty($s1);
	$url	= getURL($searchURL, makeQueryString($s1));

	$nameFormat	= propFormat($pName, $props[$name]);
	if ($ix++ == 50) echo '<div class="expand"><div class="expandContent">';
?>
    <span><a href="{!$url}">{!$nameFormat}</a><sup>{$count}</sup></span>
<? } ?>
<?	if ($ix >= 50) echo '</div></div>'; ?>
    </td>
</tr>
<? } ?>
</table>
<? } ?>
</form>
<?
$sql = array();
doc_sql($sql, $search);

if ($sql){ $p = m("doc:read:$template", $s); ?>
    <h2>Результат поиска:</h2>
<? if (!$p){ ?>
    <h3>По вашему запросу ничего не найдено</h3>
<? }else{ ?>
{!$p}
<? } ?>
<? } ?>
<? if (!$search) endCache($cache); ?>
<? } ?>
