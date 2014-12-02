<?
function doc_read_oldMaster_before($db, &$val, &$search)
{
	$s				= getValue('search');
	$search['prop'] = $s['prop'];
	$search['page']	= getValue('page');
	m('fileLoad', 'css/oldMaster.css');
}
function doc_read_oldMaster_beginCache($db, &$val, &$search)
{
	if (userID()) return;
	return hashData($search);
}
function doc_read_oldMaster($db, &$val, &$search)
{
	if (!$db->rows()) return $search;
	
	$rows	= 3;
	$row	= 0;
	$max	= $db->rows() - 1;
	$p		= dbSeek($db, 3*$rows+1, array('search' => getValue('search')));
	$classes= array('left', 'center', 'right');
?>
<link rel="stylesheet" type="text/css" href="css/oldMaster.css">
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
<div class="oldMasterSlot">
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
	<td class="oldMasterInfo">
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

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="oldMaster">
<? while($data = $db->next()){ ?>
    <tr>
<? for($col=0; $col < 3; ++$col)
{
	if ($col) $data = $db->next();
	if (!$data){
		echo "<td class=\"$class\"></td>";
		continue;
	}
	
	$class	= $classes[$col];
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
