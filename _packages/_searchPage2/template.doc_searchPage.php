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

	m('page:title', 'Поиск по сайту');
?>
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">
<link rel="stylesheet" type="text/css" href="css/search.css">

<h1>Поиск по сайту</h1>
<table width="100%" cellpadding="0" cellspacing="0">
<tr>

<td valign="top" width="200">
    <? $s	= module('doc:searchPanel:default2', $s); ?>
</td>

<td valign="top" style="padding-left:20px">
	<? if (getValue('search')){ ?>
    {{doc:read:$template=$s}}
    <? }else{ ?>
    {{read:searchPage}}
    <? } ?>
</td>

</tr>
</table>

<? } ?>
