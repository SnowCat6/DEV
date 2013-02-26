<?
function doc_searchPage($db, $val, $data)
{
	@list($type, $template) = explode(':', $val);

	if (!$type) @$type = $data[1];
	$docTypes	= getCacheValue('docTypes');
	if (!isset($docTypes[$type])) $type = '';
	
	if (!$template) @$template	= $data[2];

	$searchURL	= $type?"search_$type":'search';
	if ($template) $searchURL .= "_$template";

	$names		= explode(',', 'Бренд,Цена,Цвет');

	$search = getValue('search');
	if (!is_array($search)) $search = array();
	if (!is_array($search['prop'])) $search['prop'] = array();
	
	$selected	= array();
	$select		= array();

	foreach($search['prop'] as $propName => $val)
	{
		if (!is_int(array_search($propName, $names))) continue;

		$s						= $search;
		$s['prop'][$propName]	= '';
		unset($s['prop'][$propName]);

		$selected[$val]	= getURL($searchURL, makeQueryString($s, 'search'));
	}

	$ddb	= module('prop');
	foreach($names as $ix => &$name) makeSQLValue($name);
	$names	= implode(', ', $names);
	
	$db->fields = 'count(*) as cnt';

	$ddb->order = 'sort';
	$ddb->open("`name` IN ($names)");
	while($data = $ddb->next())
	{
		$iid		= $ddb->id();
		$propName	= $data['name'];
		if (isset($search['prop'][$propName])) continue;
		
		$valueType	= $data['valueType'];
		$typeField	= makeField($valueType);
	
		$ddb->dbValue->fields	= $typeField;
		$ddb->dbValue->group	= $typeField;
		$ddb->dbValue->order	= $typeField;
		$ddb->dbValue->open("`prop_id` = $iid");
		if (!$ddb->dbValue->rows()) continue;
		
		while($d = $ddb->dbValue->next())
		{
			$propValue	= $d[$valueType];
			
			$sql		= array();
			$s			= $search;
			$s['type']	= $type;
			$s['prop'][$propName]	= $propValue;
			
			doc_sql($sql, $s);
			$db->open($sql);
			$d		= $db->next();
			@$count	= $d['cnt'];
			if (!$count) continue;
			
			unset($s['type']);
			$url	= getURL($searchURL, makeQueryString($s, 'search'));
			$select[$propName][$propValue] = array($url, $count);
		}
	}
?>
<form action="{{getURL:$searchURL}}" method="post" class="searchForm">
<h2>Поиск по сайту:</h2>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="100%"><input name="search[name]" type="text" class="input w100" value="{$search[name]}" /></td>
    <th><input type="submit" name="button" class="button" value="Искать" /></th>
</tr>
</table>
<table class="search property" width="100%" cellpadding="0" cellspacing="0">
<? if ($selected){ ?>
<tr>
    <td colspan="2" class="title">
<big>Ваш выбор: </big>
<? foreach($selected as $name => $url){ ?>
<span><a href="{!$url}">{$name}</a></span>
<? } ?>
<a href="{{getURL:$searchURL}}" class="clear">очистить</a>
    </td>
</tr>
<? } ?>
<? foreach($select as $name => $props){ ?>
<tr>
	<th>{$name}</th>
    <td width="100%">
	<? foreach($props as $name => $url){?>
    <span><a href="{!$url[0]}">{$name}</a> ({$url[1]})</span>
    <? } ?>
    </td>
</tr>
<? } ?>
</table>
</form>
<div class="product list">
<? module("doc:read:$template", $search);?>
</div>
<? } ?>
