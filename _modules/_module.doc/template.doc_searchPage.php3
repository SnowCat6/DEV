<?
function doc_searchPage($db, $val, $data)
{
	@list($type, $template) = explode(':', $val);

	if (!$type) @$type = $data[1];
	$docTypes	= getCacheValue('docTypes');
	if (!isset($docTypes[$type])) $type = '';
	
	if ($type) $documentType = $type;
	else $documentType = 'news';
	
	if (!$template) @$template	= $data[2];

	$searchURL	= $type?"search_$type":'search';
	if ($template) $searchURL .= "_$template";

	$search = getValue('search');
	if (!is_array($search)) $search = array();
	$search['type'] = '';
	unset($search['type']);
	
	$bSecondSearch = $search['prop'] != false;

	$ddb	= module('prop');
	$names	= array();
	$groups	= $bSecondSearch?"globalSearch,globalSearch2":"globalSearch";
	//	Получить свойства и кол-во товаров со свойствами
	$props	= module("prop:name:$groups");
	$n		= implode(',', array_keys($props));
	$prop	= $n?module("prop:count:$n", $search):array();

	if (!is_array($search['prop'])) $search['prop'] = array();
	
	$selected	= array();
	foreach($search['prop'] as $name => $val)
	{
		if (!isset($prop[$name])) continue;
		$s = $search;
		unset($s['prop'][$name]);
		$selected[$val]	= array(getURL($searchURL, makeQueryString($s, 'search')), $name);
	}
	
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
<? foreach($selected as $val => $url){ list($url, $name) = $url;?>
<span><a href="{!$url}">{$val}</a></span>
<? } ?>
<? if ($selected){ ?><a href="{{getURL:$searchURL}}" class="clear">очистить</a><? } ?>
    </td>
</tr>
<? foreach($select as $name => &$property){ ?>
<tr>
	<th>{$name}</th>
    <td width="100%">
<? foreach($property as $pName => $count)
{
	$s					= $search;
	$s['prop'][$name]	= $pName;

	$nameFormat	= propFormat($pName, $props[$name]);
	$url		= getURL($searchURL, makeQueryString($s, 'search'));
?>
    <span><a href="{!$url}">{!$nameFormat}</a> ({$count})</span>
<? } ?>
    </td>
</tr>
<? } ?>
</table>
<? } ?>
</form>
<?
$sql = array();
doc_sql($sql, $search);

if ($sql){ $p = m("doc:read:$template", $search); ?>
    <h2>Результат поиска:</h2>
<? if (!$p){ ?>
    <h3>По вашему запросу ничего не найдено</h3>
<? }else echo $p; ?>
<? } ?>
<? //if (!$bSecondSearch) endCache($cache); ?>
<? } ?>
