<?
function doc_read_catalog_before($db, &$val, &$search)
{
	$s				= getValue('search');
	$search['prop'] = $s['prop'];
	$search['page']	= getValue('page');
	m('fileLoad', 'css/readCatalog.css');
}
function doc_read_catalog_beginCache($db, &$val, &$search)
{
	if (userID()) return;
	return hashData($search);
}
function doc_read_catalog($db, &$val, &$search)
{
	if (!$db->rows()) return $search;
	
	$rows	= 3;
	$max	= $db->rows() - 1;
	$p		= dbSeek($db, 3*$rows+1, array('search' => getValue('search')));

	$cols	= array('left', 'center', 'right');
?>
<link rel="stylesheet" type="text/css" href="../../../../_templates/baseStyle.css">
<link rel="stylesheet" type="text/css" href="css/readCatalog.css">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td>
<?
	$data	= $db->next();
	$id		= $db->id();
	$link	= getURL($db->url());
	$note	= docNote($data);
	$menu	= doc_menu($id, $data);
?>
<div class="readCatalog">
	{{doc:titleImage:$id=mask:design/oldMasterMask2.png;property.href:$link;hasAdmin:top;adminMenu:$menu}}
    <div class="info">
        <h2><a href="{!$link}" title="{$data[title]}">{$data[title]}</a></h2>
        <div>{{prop:read:plain=id:$id}}</div>
<? if ($note){ ?>
        <blockquote>{!$note}</blockquote>
<? } ?>
    </div>
</div>
    </td>
	<td class="readCatalogInfo">
<?
$s1		= getValue('search');
//	Получить названия свойств для поиска
$props	= module("prop:name:productSearch");
$n		= implode(',', array_keys($props));
//	Получить названия и количество документов перечисленных свойств
$props	= $n?module("prop:count:$n", $search):array();
//	Вывести названия и кол-во с сылками на быстрый поиск
foreach($props as $n => &$prop){?>
<h3>{$n}</h3>
<div>
<?
//	Текущий параметр поиска
$thisValue	= $search['prop'][$n];
foreach($prop as $name => $count)
{
	$class	= $name == $thisValue?' class="current"':'';
	
	$s		= array();
	$s['prop']	= $s1['prop'];
	if (!$class) $s['prop'][$n] = $name;
	else  $s['prop'][$n] = '';
	
	removeEmpty($s);
	$s		= makeQueryString($s, 'search');
	$url	= getURL($db->url(currentPage()), $s);
?>
<a href="{!$url}" {!$class} title="{$name}">
    <span>{$name}</span><sup>{$count}</sup>
</a>
<? } ?>
</div>
<? } ?>
    </td>
</tr>
</table>

<? if ($db->rows() == 1) return $search; ?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="readCatalogItems">
<? while($rows-- && ($data = $db->next())){ ?>
    <tr>
<? foreach($cols as $col => $class)
{
	if ($col) $data = $db->next();
	if (!$data){
		echo "<td class=\"$class\"></td>";
		continue;
	}
	
	$id		= $db->id();
	$link	= getURL($db->url());
	$note	= docNote($data);
	$menu	= doc_menu($id, $data);
?>
        <td class="{$class}">
<div class="item">
    {{doc:titleImage:$id=mask:design/oldMasterMask.png;hasAdmin:true;adminMenu:$menu;property.href:$link}}
    <h2><a href="{!$link}" title="{$data[title]}">{$data[title]}</a></h2>
	<p>{{prop:read:plain=id:$id}}</p>
<? if ($note){ ?>
    <blockquote>{!$note}</blockquote>
<? }// if note ?>
</div>
		</td>
<? }// for ?>
    </tr>
<? } // while ?>
</table>

{!$p}

<? return $search; } ?>
