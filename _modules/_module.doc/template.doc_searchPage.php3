<?
function doc_searchPage($db, $val, $data)
{
	module('script:ajaxLayout');
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
	if (!is_array($search['prop'])) $search['prop'] = array();
	$search['type'] = '';
	
	$bSecondSearch = $search['prop'] != false;
	
//	if (!$bSecondSearch && !beginCache($cache = "docPageSearch")) return;
	
	$selected	= array();
	$select		= array();

	$ddb	= module('prop');
	$names	= array();
	$groups	= explode(',', $bSecondSearch?"globalSearch,globalSearch2":"globalSearch");

	foreach($groups as $group){
		makeSQLValue($group);
		$sql[] = "FIND_IN_SET($group, `group`) > 0";
	}
	$ddb->order = 'sort';
	$ddb->open(implode(' OR ', $sql));
	while($data = $ddb->next()){
		$names[] = $data['name'];
	}

	foreach($search['prop'] as $propName => $val)
	{
		if (!is_int(array_search($propName, $names))) continue;

		$s						= $search;
		$s['prop'][$propName]	= '';
		unset($s['prop'][$propName]);

		$selected[$val]	= array(getURL($searchURL, makeQueryString($s, 'search')), $propName);
	}

	foreach($names as $ix => &$name) makeSQLValue($name);
	$names	= implode(', ', $names);
	
	$db->fields = 'count(*) as cnt';

	$ddb->seek(0);
	while($data = $ddb->next())
	{
		$groups		= explode(',', $data['group']);
		if ($bSecondSearch && !is_int(array_search('globalSearch2', $groups))) continue;
		
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
<form action="{{getURL:$searchURL}}" method="post" class="form searchForm ajaxLayout" id="searchPage">
<? if ($type){ ?><input type="hidden" name="search[type]" value="{$type}" /><? } ?>
<h2>Поиск по сайту:</h2>
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
<input type="hidden" name="search[prop][{$name}]" value="{$val}" />
<? } ?>
<? if ($selected){ ?><a href="{{getURL:$searchURL}}" class="clear">очистить</a><? } ?>
    </td>
</tr>
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
<? } ?>
</form>
<? $p = m("doc:read:$template", $search); ?>
<? $style = $p || !$bSecondSearch?'':' style="display:block"'; ?>
<div class="{$documentType} list" id="searchPage" template="{$template}">
    <h2 class="layoutTitle"{!$style}>Результат поиска:</h2>
    <h3 class="layoutError"{!$style}>По вашему запросу ничего не найдено</h3>
    <div class="layoutContent">{!$p}</div>
</div>
<? //if (!$bSecondSearch) endCache($cache); ?>
<? } ?>
