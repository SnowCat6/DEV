<?
function doc_searchPage($db, $val, $data)
{
	$type		= $data[1];
	if (!$type) $type = 'article,product';
	$template	= $data[2];

	//	Пробуем получить шаблон из данных
	if (!$template){
		switch($type){
		case 'product':	$template = 'catalog';	break;
		case 'article':	$template = 'news';		break;
		default: 		$template = 'catalog';	break;
		}
	}

	//	Получить данные для поиска
	$s			= array();
	$s['type']	= $type;
	$s['options']	= array(
		'hasChoose'	=> true
	);
	
	if ($data['options']['url']) $s['options']['url'] = $data['options']['url'];
	else $s['options']['url'] = 'search';
	$url	= getURL($s['options']['url']);

	m('page:title', 'Поиск по сайту');
	//	
	$search		= getValue('search');
	removeEmpty($search);
?>
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">
<link rel="stylesheet" type="text/css" href="css/search.css">

<form action="{$url}" method="post" class="searhForm">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="100%">
    <input name="search[name]" type="text" class="input w100" value="{$search[name]}" />
    </td>
    <th><input type="submit" name="button" class="button" value="Искать" /></th>
</tr>
</table>
</form>

<? $s	= module('doc:searchPanel:default', $s); ?>

<? if ($search){ ?>
    <? if ($p = m("doc:read:$template", $s)){?>
        <h2>Результат поиска:</h2>
		{!$p}
	<? }else{ ?>
        <h2>По вашему запросу ничего не найдено</h2>
        {{read:searchPageNotFound}}
    <? } ?>
<? }else{ ?>
	<? if (!$data['options']['hideNotFound']){ ?>
        {{read:searchPage}}
	<? } ?>
<? } ?>

<? } ?>
