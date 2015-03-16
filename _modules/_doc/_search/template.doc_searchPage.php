<?
function doc_searchPage($db, $val, $data)
{
	$type		= $data[1];
	if (!$type) $type = 'article,product';
	$template	= $data[2];

	//	
	$search		= getValue('search');
	$thisOrder	= getValue('order');
	$thisPages	= getValue('pages');

	//	Пробуем получить шаблон из данных
	if (!$template){
		switch($type){
		case 'product':	$template = 'catalog';	break;
		case 'article':	$template = 'news';		break;
		default: 		$template = 'catalog';	break;
		}
	}

	//	Получить данные для поиска
	$s			= $search;
	$s['type']	= $type;
	$s['options']	= array(
		'hasChoose'	=> true
	);

	m('page:title', 'Поиск по сайту');
?>
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">
<link rel="stylesheet" type="text/css" href="css/search.css">

<form action="{{urk:#}}" method="post" class="searhForm">
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
{{doc:read:$template=$s}}
<? } ?>

<? } ?>
